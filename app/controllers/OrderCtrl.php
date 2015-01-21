<?php

namespace Bento\Ctrl;

use Bento\Model\Order;
use Bento\Model\OrderEvent;
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
        
        if ($reserved !== false)
            return Response::json(array('reserveId' => $reserved));
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
        
        // Get the user
        $user = User::get();
        
        // Return 404 if PendingOrder not found
        if ($pendingOrder === NULL)
            return Response::json('', 404);
        
        // Perform payment verification with gateway
        try {
            Stripe::setApiKey($_ENV['Stripe_sk_test']);
            $stripeChargeId = $data->stripe_chargeId;
            $ch = Stripe_Charge::retrieve($stripeChargeId);
        }
        catch (\Exception $e) {
            return Response::json(array('Error' => 'Invalid stripe_chargeId.'), 400);
        }
        
        // Insert into Order
        $order = new Order();
        $order->fk_User = $user->pk_User;
        $order->amount = $ch->amount / 100;
        $order->stripe_charge_id = $stripeChargeId;
        $order->stripe_charge_obj = $ch;
        $order->save();
        
        // Insert into OrderEvent
        #$orderEvent = new OrderEvent();
        # do stuff
        #$orderEvent->save();
        
        // Soft-delete pending order
        $pendingOrder->delete();
        
        // Dispatch the driver
        # do stuff
        
        return Response::json('', 200);
    }
    
}
