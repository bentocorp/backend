<?php

namespace Bento\Admin\Ctrl;

use Bento\Order\OrderStatus;
use Bento\Admin\Model\Orders;
use Bento\Admin\Model\Driver;
use Bento\app\Bento;
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
    
    
    public function postSetDriver($pk_Order, $insertAt = 0) {
        
        $data = $_POST;
        
        $orderStatus = new OrderStatus($pk_Order);
        $orderStatus->setDriver($data, $insertAt);
        
        if (Bento::isAdminApiRequest()) {
            return true;
        }
        else {
            $response = Redirect::back()->with('msg', 
                array('type' => 'success', 'txt' => 'Driver assigned to order.'));

            return $response;
        }
    }
    
    
    public function getCancel($pk_Order){
        
        $orderStatus = new OrderStatus($pk_Order);
        $internalResponse = $orderStatus->cancel();
        
        if (Bento::isAdminApiRequest()) {
            return $internalResponse->formatForRest();
        }
        else {
            $response = Redirect::back()->with('msg', 
                array('type' => $internalResponse->getDerivedStatusClass(), 'txt' => $internalResponse->getPubMsg() ));
            
            return $response;
        }
    }
    
    
    
}
