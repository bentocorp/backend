<?php

namespace Bento\Ctrl;

use Bento\Model\Order;
use Bento\Model\OrderEvent;
use Bento\Model\PendingOrder;
use Bento\Model\LiveInventory;
use Bento\Model\Status;
use Response;
use Input;
use Request;
use Route;

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
        
        // Get the pending order
        $pendingOrder = PendingOrder::getUserPendingOrder();
        
        if ($pendingOrder === NULL)
            return Response::json('', 404);
        
        // Perform payment verification with gateway
        #$stripe->veryifyOrder($data);
        
        // Insert into Order
        $order = new Order();
        # do stuff
        $order->save();
        
        // Insert into OrderEvent
        $orderEvent = new OrderEvent();
        # do stuff
        $orderEvent->save();
        
        // Dispatch the driver
        # do stuff
    }
    
}
