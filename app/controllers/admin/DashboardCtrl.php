<?php

namespace Bento\Admin\Ctrl;

use \Bento\Admin\Model\Menu;
use \Bento\Admin\Model\Orders;
use \Bento\Admin\Model\Drivers;
use View;



class DashboardCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
        
        // Get today's menu
        $date = date('Ymd');
        $menu = Menu::get($date);
        $data['menu'] = $menu;
        
        // Get open orders
        $openOrders = Orders::getOpenOrders();
        $data['openOrders'] = $openOrders;
        
        // Get current drivers
        #$currentDrivers = Drivers::getCurrentDrivers();
        #$data['currentDrivers'] = $currentDrivers;
        
        // Get recent not-open orders
        #$recentOpenOrders = Dashboard::getRecentOpenOrders();
        
        
        
        return View::make('admin.index', $data);
    }
    
    
    
}
