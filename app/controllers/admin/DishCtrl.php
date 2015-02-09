<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Dish;
use View;
use Redirect;



class DishCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
                
        // Get dishes
        $dishes = Dish::orderBy('name', 'asc')->get();
        #$dishes = Dish::all()->orderBy("name");
        $data['dishes'] = $dishes;
           
        return View::make('admin.dish.index', $data);
    }
    
    
    public function getCreate() {
        
        $data['mode'] = 'Create New Dish';
        $data['title'] = $data['mode'];
        
        return View::make('admin.dish.crud', $data);
    }
    
    
    public function postCreate() {
        
        $dish = Dish::create($_POST);
        $id = $dish->pk_Dish;
        
        return Redirect::to("admin/dish/edit/$id")->with('msg', 
            array('type' => 'success', 'txt' => "New dish <b>$dish->name</b> created."));
    }
    
    
    public function getEdit($id) {
        
        $dish = Dish::find($id);
        $data['dish'] = $dish;
        $data['mode'] = 'Editing';
        $data['title'] = $data['mode'].': '.$dish->name;
        
        return View::make('admin.dish.crud', $data);
    }
    
    
    public function postEdit($id) {
        
        $data = $_POST;
        
        Dish::saveChanges($id, $data);
        
        return Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => 'Dish Saved.'));
    }
    
    
}
