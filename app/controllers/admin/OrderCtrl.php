<?php

namespace Bento\Admin\Ctrl;

use Bento\Model\OrderStatus;
use Bento\Admin\Model\Orders;
use Bento\Admin\Model\Driver;
use Redirect;
use View;


class OrderCtrl extends \BaseController {
    
    
    public function getIndex() {
        
        // Get open orders
        $openOrders = Orders::getOpenOrders();
        $data['openOrders'] = $openOrders;
        
        // Get current drivers dropdown
        $driversDropdown = Driver::getCurrentDriversForDropdown();
        $data['driversDropdown'] = $driversDropdown;
        
        // Get possible order statuses
        $orderStatusDropdown = Orders::getStatusesForDropdown();
        $data['orderStatusDropdown'] = $orderStatusDropdown;
        
        return View::make('admin.order.index', $data);
    }
    
    
    public function postSaveStatus($pk_Order) {
        
        #if ($data === NULL)
        $data = $_POST;
        
        OrderStatus::setStatus($pk_Order, $data);
        
        return Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => 'Order status updated.'));
    }
    
    
    
}
