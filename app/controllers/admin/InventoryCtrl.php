<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Menu;
use Bento\Admin\Model\Driver;
use Bento\Model\LiveInventory;
use View;



class InventoryCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
        
        // Get today's menu
        $date = Menu::getDateForTodaysMenu();
        $menu = Menu::get($date);
        $data['menu'] = $menu;
                
        // Get current drivers
        $currentDrivers = Driver::getCurrentDrivers();
        $data['currentDrivers'] = $currentDrivers;
        
        // Get LiveInventory compared with DriverInventory
        $liveInventory = LiveInventory::getLiveAndDriver();
        $data['liveInventory'] = $liveInventory;
           
        return View::make('admin.inventory.index', $data);
    }
    
    
    
}
