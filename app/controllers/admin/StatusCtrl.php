<?php

namespace Bento\Admin\Ctrl;

use Bento\core\Status;
use Bento\Admin\Model\Settings;
use Bento\Model\Menu as OdMenu;
use Redirect;
use DB;
use Input;



class StatusCtrl extends \BaseController {

        
    public function getOpen($override = false) {
        #die('here');
        $hasMenuForCurrentMealType = OdMenu::hasMenuForCurrentMealType();
        
        $overrideBtn = '<a href="/admin/status/open/true" onclick="return confirm(\'Override?\')" class="btn btn-warning">Override</a>';
        
        // Don't open if there's no menu for this meal
        if (!$hasMenuForCurrentMealType && !$override) {
            return Redirect::back()
                ->with('msg', 
                array('type' => 'danger', 'txt' => "<b>Can't Open!</b> There is no on-demand menu for the current meal time. &nbsp; $overrideBtn"));
        }
        
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
    
    
    /**
     * ONLY FOR ON DEMAND!!!
     * 
     * @return Back to admin
     */
    public function getReset() {
        
        ####
        # Only for On Demand!
        ####
        
        // Clear LiveInventory
        DB::delete('delete from LiveInventory');
        
        // Clear DriverInventory
        DB::delete('delete from DriverInventory');
        
        // Take all drivers off shift
         DB::update('update Driver set on_shift = 0, order_queue = NULL WHERE on_shift = 1', array());

         
        // Close any open Orders
        DB::update('
            update OrderStatus os 
            left join `Order` o on (os.fk_Order = o.pk_Order) 
            set os.`status` = "Delivered" 
            where 
                os.`status` IN (?,?,?) AND (os.fk_Driver IS NOT NULL AND os.fk_Driver > 0) 
                AND o.order_type = 1 
            ', 
            array('Open', 'En Route', 'Assigned'));
        
        DB::update('
            update OrderStatus os 
            left join `Order` o on (os.fk_Order = o.pk_Order) 
            set os.`status` = "Cancelled" 
            where 
                os.`status` IN (?,?,?) AND (os.fk_Driver IS NULL OR os.fk_Driver <= 0) 
                AND o.order_type = 1 
            ', 
            array('Open', 'En Route', 'Assigned'));

        
        // Close any open generic_Orders
        DB::update('update generic_Order set `status` = "Delivered" where `status` IN (?,?,?) AND (fk_Driver IS NOT NULL AND fk_Driver > 0)', array('Open', 'En Route', 'Assigned'));
        DB::update('update generic_Order set `status` = "Cancelled" where `status` IN (?,?,?) AND (fk_Driver IS NULL OR fk_Driver <= 0)', array('Open', 'En Route', 'Assigned'));
        
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
