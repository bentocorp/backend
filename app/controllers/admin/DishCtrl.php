<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Dish;
use View;



class DishCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
                
        // Get dishes
        $dishes = Dish::orderBy('name', 'asc')->get();
        #$dishes = Dish::all()->orderBy("name");
        $data['dishes'] = $dishes;
           
        return View::make('admin.dish.index', $data);
    }
    
    
    public function getEdit($id) {
        
        $dish = Dish::find($id);
        $data['dish'] = $dish;
        $data['mode'] = 'Editing';
        
        return View::make('admin.dish.crud', $data);
    }
    
    
}
