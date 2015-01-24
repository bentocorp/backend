<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Menu;
use Bento\Admin\Model\Orders;
use Bento\Admin\Model\Drivers;
use View;
use Carbon\Carbon;



class DashboardCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
        
        // Get today's menu
        $date = Carbon::now('America/Los_Angeles')->format('Ymd');
        $menu = Menu::get($date);
        $data['menu'] = $menu;
        
        // Get open orders
        $openOrders = Orders::getOpenOrders();
        $data['openOrders'] = $openOrders;
        
        // Get recent not-open orders
        #$recentOpenOrders = Dashboard::getRecentOpenOrders();
        
        // Get possible order statuses
        $orderStatusDropdown = Orders::getStatusesForDropdown();
        $data['orderStatusDropdown'] = $orderStatusDropdown;
        
        // Get current drivers
        $currentDrivers = Drivers::getCurrentDrivers();
        $driversDropdown = Drivers::getCurrentDriversForDropdown();
        $data['currentDrivers'] = $currentDrivers;
        $data['driversDropdown'] = $driversDropdown;
           
        return View::make('admin.index', $data);
    }
    
    
    
}
