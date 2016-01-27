<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Driver;
use Bento\Drivers\DriverMgr;
use Redirect;
use View;
use Input;
use Route;


class DriverCtrl extends AdminBaseController {
    
    private $data = array();
    
    
    public function __construct() {
        // Nav
        $this->data['nav6'] = true;
    }
    
    
    public function getIndex() {
        
        $data = $this->data;
        
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
        
        #$data = $_POST;
        $data = Input::all();
        
        // This is NOT a merge
        if ($data['zeroArray'] == '') {
            $driver = new Driver(null, $pk_Driver);
            $driver->updateInventory($data);
        }
        // This IS a merge
        else {
            DriverMgr::mergeDrivers($data, $pk_Driver);
        }
                        
        // Save the new data
        #Driver::overwriteInventory($pk_Driver, $data);
                
        return Redirect::back()->with('msg', array(
            'type' => 'success', 
            'txt' => 'Driver Inventory <b>AND</b> Live Inventory updated via diff.'));
    }
    
    
    public function getCreate() {
        
        $data['mode'] = 'create';
        $data['title'] = 'Add New Driver';
        
        return View::make('admin.driver.crud', $data);
    }
    
    
    public function postCreate() {
        
        $record = Driver::create($_POST);
        
        $id = $record->pk_Driver;
        
        return Redirect::to("admin/driver/edit/$id")->with('msg', 
            array('type' => 'success', 'txt' => "New driver <b>$record->firstname $record->lastname</b> created."));
    }
    
    
    public function getEdit($id) {
        
        $record = Driver::find($id);
        $data['record'] = $record;
        $data['mode'] = 'Editing';
        $data['title'] = $data['mode'].': '. "$record->firstname $record->lastname";
        
        return View::make('admin.driver.crud', $data);
    }
    
    
    public function postEdit($id) {
        
        $data = $_POST;
        
        Driver::saveChanges($id, $data);
        
        return Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => 'Driver Saved.'));
    }
    
    
    public function getArchive($id) {
        
        $driver = Driver::find($id);
        
        // Check to make sure that they aren't on shift
        if (!$driver->on_shift) {
            
            $driver->delete();
            
            return Redirect::back()->with('msg', 
                array('type' => 'success', 'txt' => "<b>Driver <u>#$id</u></b> has been archived."));
        }
        else
            return Redirect::to('/admin/driver')->with('msg', 
                array('type' => 'danger', 'txt' => "<b>Driver <u>#$id</u> cannot be archived while on shift.</b>"));
    }
    
}
