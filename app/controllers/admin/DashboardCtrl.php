<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Menu;
use Bento\Admin\Model\Orders;
use Bento\Admin\Model\Driver;
use Bento\Model\LiveInventory;
use Bento\Admin\Model\Status;
use DB;
use View;



class DashboardCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
        
        // Get today's menu
        $date = Menu::getDateForTodaysMenu();
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
        $currentDrivers = Driver::getCurrentDrivers();
        $driversDropdown = Driver::getCurrentDriversForDropdown();
        $data['currentDrivers'] = $currentDrivers;
        $data['driversDropdown'] = $driversDropdown;
        
        // Get LiveInventory compared with DriverInventory
        $liveInventory = LiveInventory::getLiveAndDriver();
        $data['liveInventory'] = $liveInventory;
        
        // Get Status
        $statusClass = Status::getClass();
        $statusMsg = Status::getMsg();
        $data['statusClass'] = $statusClass;
        $data['statusMsg'] = $statusMsg;
        
        // Get some copy to edit on the dashboard
        $in = "'closed-text', 'closed-text-latenight', 'sold-out-text', 'sale_price', 'price'";
        $iosCopy = DB::select("SELECT * FROM admin_ios_copy WHERE `key` IN ($in) order by `key` asc", array());
        $data['iosCopy'] = $iosCopy;
           
        return View::make('admin.index', $data);
    }
    
    
    
}
