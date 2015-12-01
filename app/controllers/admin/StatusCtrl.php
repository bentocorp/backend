<?php

namespace Bento\Admin\Ctrl;

use Bento\core\Status;
use Bento\Admin\Model\Settings;
use Redirect;
use DB;
use Input;



class StatusCtrl extends \BaseController {

        
    public function getOpen() {
        
        Status::open();
        
        return Redirect::back();
            #->with('msg', 
            #array('type' => 'success', 'txt' => '<b>Restaurant Open!</b>'));
    }
    
    
    public function getClosed() {
        
        Status::closed();
        
        return Redirect::back();
            #->with('msg', 
            #array('type' => 'danger', 'txt' => '<b>Restaurant Closed!</b>'));
    }
    
    
    public function getSoldout() {
        
        Status::soldout();
        
        return Redirect::back();
            #->with('msg', 
            #array('type' => 'warning', 'txt' => '<b>Restaurant Sold Out!</b>'));
    }
    
    
    public function getReset() {
        
        // Clear LiveInventory
        DB::delete('delete from LiveInventory');
        
        // Clear DriverInventory
        DB::delete('delete from DriverInventory');
        
        // Take all drivers off shift
         DB::update('update Driver set on_shift = 0, order_queue = NULL', array());

        // Close any open Orders
        DB::update('update OrderStatus set `status` = "Delivered" where `status` IN (?,?,?) AND fk_Driver IS NOT NULL', array('Open', 'En Route', 'Assigned'));
        DB::update('update OrderStatus set `status` = "Cancelled" where `status` IN (?,?,?) AND fk_Driver IS NULL', array('Open', 'En Route', 'Assigned'));

        // Close any open generic_Orders
        DB::update('update generic_Order set `status` = "Delivered" where `status` IN (?,?,?) AND fk_Driver IS NOT NULL', array('Open', 'En Route', 'Assigned'));
        DB::update('update generic_Order set `status` = "Cancelled" where `status` IN (?,?,?) AND fk_Driver IS NULL', array('Open', 'En Route', 'Assigned'));
        
        return Redirect::back()
            ->with('msg', 
            array('type' => 'success', 'txt' => "<b>I've cleared everything out.</b>"));
    }
    
    
    public function postMealmode() {
        
        $mode = Input::get('meal_mode');
        
        $row = Settings::find('fk_MealType_mode');
        $row->value = $mode;
        $row->save();
        
        return Redirect::back()
            ->with('msg', 
            array('type' => 'success', 'txt' => "<b>Meal mode updated.</b>"));
    }
    
    
    
}
