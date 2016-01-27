<?php namespace Bento\Admin\Ctrl;


use Bento\Admin\Model\Orders;
use Bento\Admin\Model\Driver;
use Bento\core\Status;
use Bento\Model\LiveInventory;
use Bento\Model\Status as ApiStatus;
use DB;
use View;



class DashboardCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
                
        // Nav
        $data['nav1'] = true;
        
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
        $data['status'] = ApiStatus::overall()->value;
        
        // Get some copy to edit on the dashboard
        $in = "'closed-text', 'closed-text-latenight', 'sold-out-text'";
        $iosCopy = DB::select("SELECT * FROM admin_ios_copy WHERE `key` IN ($in) order by `key` asc", array());
        $data['iosCopy'] = $iosCopy;
        
        // Get the meal modes for the dropdown
        $mealModes = Status::getMealModesForDropdown();
        $data['mealModesAr'] = $mealModes;
        
        // Get the meal mode
        $mealMode = Status::getMealMode();
        $mealModeId = $mealMode->pk_MealType;
        $data['mealModeId'] = $mealModeId;
           
        return View::make('admin.index', $data);
    }
    
    
    
}
