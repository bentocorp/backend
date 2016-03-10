<?php namespace Bento\Admin\Ctrl\OA;


use Bento\Timestamp\Clock;
use Bento\core\OrderAhead\Orders;
use View;


class OrdersCtrl extends \BaseController {

    private $data = array();
    
    
    public function __construct() {
        // Nav
        $this->data['nav13'] = true;
    }
    
    
    public function getIndex() {
        
        $data = $this->data;
                
        // Today's Date
        $today = Clock::getLocalTimestamp();
        $data['today'] = $today;
        
        // Today and greater with orders
        $orders = Orders::getFutureGroupList($today);
        $data['list'] = $orders;
           
        return View::make('admin.oa.orders.index', $data);
    }
    
    
    public function getFor($date)
    {
        $data = $this->data;
        $data['date'] = $date;
        
        // Get orders by day
        $monetizedOrders = Orders::getMonetizedOrdersByDay($date);
        $data['monetizedOrders'] = $monetizedOrders;
        
        $cancelledOrders = Orders::getCancelledOrdersByDay($date);
        $data['cancelledOrders'] = $cancelledOrders;
        
        return View::make('admin.oa.orders.day', $data);
    }
    
   
}
