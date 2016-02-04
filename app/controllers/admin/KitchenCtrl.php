<?php namespace Bento\Admin\Ctrl;


use Bento\Timestamp\Clock;
use Bento\core\OrderAhead\Orders;
use Bento\Order\Cashier;
use View;


class KitchenCtrl extends \BaseController {

    private $data = array();
    
    
    public function __construct() {
        // Nav
        $this->data['nav12'] = true;
    }
    
    
    public function getOascreen() {
        
        $data = $this->data;
                
        // Today's Date
        $today = Clock::getLocalTimestamp();
        $data['today'] = $today;
        
        // Lunch
        $orders = Orders::getLunchOrders($today);
        $data['lunchOrders'] = $orders;
        $data['lunchQtys'] = $this->getQtys($orders);
        
        // Dinner
        $orders2 = Orders::getDinnerOrders($today);
        $data['dinnerOrders'] = $orders2;
        $data['dinnerQtys'] = $this->getQtys($orders2);
           
        return View::make('admin.kitchen.oascreen', $data);
    }
    
    
    private function getQtys($orders)
    {
        $masterCount = array();
        
        foreach ($orders as $order)
        {
            $cashier = new Cashier(json_decode($order->order_json));
            $qtyHash = $cashier->getTotalsHash();
            
            foreach ($qtyHash as $id => $qty) {
                if (isset($masterCount[$id]))
                    $masterCount[$id] += $qty;
                else
                    $masterCount[$id] = $qty;
            }
        }
        
        return $masterCount;
    }
    
   
}
