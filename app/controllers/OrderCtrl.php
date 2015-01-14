<?php

namespace Bento\Ctrl;

use Bento\Model\Order;
use Bento\Model\OrderEvent;
use Response;
use Input;

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
        $data = Input::get('data');
        print_r($data); die();
        
        // Make sure this user doesn't already have a pending order
        # how?
                
        // Update LiveInventory and store into PendingOrder
        // (Phase 2 makes it final)
        
        
        return Response::json($status);
    }
    
    
    /**
     * Phase 2:
     * Commit a new order for real. Dispatch the driver, etc.
     * 
     * @return json 
     */
    public function phase2() {
        
        // Get data
        $data = Input::get('data');
        
        // Perform payment verification with gateway
        #$this->veryifyOrder();
        
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
