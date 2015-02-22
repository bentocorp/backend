<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Driver;
use Bento\Drivers\DriverMgr;
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
        $errors = DriverMgr::updateShifts($_POST);
        
        // If some drivers still have outstanding orders, they can't be taken off shift yet
        if (count($errors) !== 0) {
            
            $str = '<b>Errors:</b><br>';
            
            foreach ($errors as $error) {
                $str .= "<b>{$error['msg']}:</b> ";
                $str .= implode($error['rows'], ', ');
                $str .= '<br>';
            }
            
            return Redirect::back()->with('msg', array(
                'type' => 'danger', 
                'txt' => $str));
        }
        else {
            return Redirect::back()->with('msg', array(
                'type' => 'success', 
                'txt' => 'Drivers on shift updated.'));
        }
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
