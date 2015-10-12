<?php

namespace Bento\Admin\Ctrl;

use Bento\core\OrderStatus;
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
    
    
    public function postSetDriver($pk_Order) {
        
        $data = $_POST;
        
        $orderStatus = new OrderStatus($pk_Order);
        $orderStatus->setDriver($data);
        
        $response = Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => 'Driver assigned to order.'));
        
        return $response;
    }
    
    
    public function getCancel($pk_Order){
        
        $orderStatus = new OrderStatus($pk_Order);
        $orderStatus->cancel();
        
        $response = Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => 'Order cancelled.'));
        
        return $response;
    }
    
    
    
}
