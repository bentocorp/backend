<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Driver;
use Bento\Model\LiveInventory;
use View;
use Redirect;



class InventoryCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
        
        // Get today's menu
        // (performed in the view-composer)

        // Get current drivers
        $currentDrivers = Driver::getCurrentDrivers();
        $data['currentDrivers'] = $currentDrivers;
        
        // Get LiveInventory compared with DriverInventory
        $liveInventory = LiveInventory::getLiveAndDriver();
        $data['liveInventory'] = $liveInventory;
           
        return View::make('admin.inventory.index', $data);
    }
    
    
    public function getSoldout($mode, $fk_item) {
        
        LiveInventory::sellOut($mode, $fk_item);
        
        return Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => '<b>Item sold out toggled.</b>'));
    }
    
    
    
}
