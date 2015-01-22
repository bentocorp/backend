<?php

namespace Bento\Ctrl;

use Bento\Model\Order;
use Bento\Model\OrderStatus;
use Bento\Model\CustomerBentoBox;
use Bento\Model\PendingOrder;
use Bento\Model\LiveInventory;
use Bento\Model\Status;
use User;
use Response;
use Input;
use Stripe; use Stripe_Charge;

class OrderCtrl extends \BaseController {

    /**
     * Phase 1:
     * Store a new pending order.
     * Hold the inventory in good faith by updating LiveInventory
     * 
     * 
     * @return json 
     */
    public function phase1() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Make sure this user doesn't already have a pending order
        $pendingOrder = PendingOrder::checkUser();
        
        if ($pendingOrder)
            return Response::json(array('Error' => 'A pending order already exists for you.'), 400);
                
        // Update LiveInventory and store into PendingOrder
        // (Phase 2 makes it final)
        $reserved = LiveInventory::reserve($data);
        
        // Inventory reservation went ok.
        if ($reserved !== false)
            return Response::json(array('reserveId' => $reserved));
        // Inventory reservation failed. We are out of something.
        else {
            // Since the inventory is incorrect in the client, conveniently send it back to them
            $menuStatus = Status::menu();
            
            $response = array(
                'Error' => 'Some of our inventory in your order just sold out!',
                'MenuStatus' => $menuStatus,
                );
            
            return Response::json($response, 410);
        }
    }
    
    
    /**
     * Phase 2:
     * Commit a new order for real. Dispatch the driver, etc.
     * 
     * @return json 
     */
    public function phase2() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Get the PendingOrder
        $pendingOrder = PendingOrder::getUserPendingOrder();
                
        // Return 404 if PendingOrder not found
        if ($pendingOrder === NULL)
            return Response::json('', 404);
        
        // Perform payment verification with Stripe
        try {
            Stripe::setApiKey($_ENV['Stripe_sk_test']);
            $stripeChargeId = $data->stripe_chargeId;
            $ch = Stripe_Charge::retrieve($stripeChargeId);
        }
        catch (\Exception $e) {
            return Response::json(array('Error' => 'Invalid stripe_chargeId.'), 400);
        }
        
        // Everything's good at this point.
        $this->processOrder($pendingOrder, $ch);
    }
    
    
    private function processOrder($pendingOrder, $ch) {
        
        // Get the user
        $user = User::get();

        // Insert into Order
        $order = new Order;
        
        $orderJson = json_decode($pendingOrder->order_json);
        
        $order->street = $orderJson->OrderDetails->address->street;
        $order->city = $orderJson->OrderDetails->address->city;
        $order->state = $orderJson->OrderDetails->address->state;
        $order->zip = $orderJson->OrderDetails->address->zip;
        $order->lat = $orderJson->OrderDetails->coords->lat;
        $order->long = $orderJson->OrderDetails->coords->long;
        
        $order->fk_User = $user->pk_User;
        $order->amount = $ch->amount / 100;
        $order->stripe_charge_id = $ch->id;
        $order->fk_PendingOrder = $pendingOrder->pk_PendingOrder;
        
        $order->save();
        
        // Insert into OrderStatus
        $orderStatus = new OrderStatus;
        $orderStatus->fk_Order = $order->pk_Order;
        $orderStatus->save();
        
        // Insert into CustomerBentoBox
        $this->insertCustomerBentoBoxes($orderJson, $order->pk_Order);
        
        // Soft-delete pending order
        $pendingOrder->delete();
        
        // Dispatch the driver
        # do stuff
        
        return Response::json('', 200);
    }
    
    
    private function insertCustomerBentoBoxes($orderJson, $pk_Order) {
        
        // For each CustomerBentoBox
        foreach ($orderJson->OrderItems as $orderItem) {
            
            // Make a CustomerBentoBox
            $box = new CustomerBentoBox;
            $box->fk_Order = $pk_Order;
            
            // Now for each thing in the box
            foreach($orderItem->items as $item) {
                $fk = "fk_$item->type";
                $box->{$fk} = $item->id;
            }
            
            // Save the box
            $box->save();
        }
    }
    
}
