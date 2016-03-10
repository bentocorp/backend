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
        $qtys1 = $this->getQtys($orders);
        $data['lunchOrders'] = $orders;
        $data['lunchQtys'] = $qtys1->itemTotals;
        $data['lunchBQty'] = $qtys1->bentoCount;
        
        // Dinner
        $orders2 = Orders::getDinnerOrders($today);
        $qtys2 = $this->getQtys($orders2);
        $data['dinnerOrders'] = $orders2;
        $data['dinnerQtys'] = $qtys2->itemTotals;
        $data['dinnerBQty'] = $qtys2->bentoCount;
        
        // Special sushi bucket
        $data['sushiBucket'] = array();
           
        return View::make('admin.kitchen.oascreen', $data);
    }
    
    
    private function getQtys($orders)
    {
        $qtys = new \stdClass;
        $masterCount = array();
        $bentoCount = 0;
        
        foreach ($orders as $order)
        {
            $cashier = new Cashier(json_decode($order->order_json), NULL, $order->pk_Order);
            $qtyHash = $cashier->getTotalsHash();
            $bentoCount += $cashier->getTotalBentos();
            
            foreach ($qtyHash as $id => $qty) {
                if (isset($masterCount[$id]))
                    $masterCount[$id] += $qty;
                else
                    $masterCount[$id] = $qty;
            }
        }
        
        $qtys->itemTotals = $masterCount;
        $qtys->bentoCount = $bentoCount;
        
        return $qtys;
    }
    
   
}
