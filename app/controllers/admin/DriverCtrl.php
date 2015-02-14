<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Driver;
use Redirect;
use View;


class DriverCtrl extends AdminBaseController {
    
    
    public function getIndex() {
        
        // Get drivers
        $drivers = Driver::all();
        $data['drivers'] = $drivers;
        
        return View::make('admin.driver.index', $data);
    }
    
    
    public function postIndex() {
        
        // Save shift status
        Driver::updateShifts($_POST);
        
        return Redirect::back()->with('msg', array(
            'type' => 'success', 
            'txt' => 'Drivers on shift updated.'));
    }
    

    public function postSaveInventory($pk_Driver) {
        
        $data = $_POST;
                
        // Save the new data
        Driver::overwriteInventory($pk_Driver, $data);
        
        return Redirect::back()->with('msg', array(
            'type' => 'success', 
            'txt' => 'Driver inventory <b>AND</b> Live Inventory updated.'));
    }
    
}
