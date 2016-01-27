<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Dish;
use View;
use Redirect;



class DishCtrl extends \BaseController {

    private $data = array();
    
    
    public function __construct() {
        // Nav
        $this->data['nav11'] = true;
    }
    
    
    public function getIndex() {
        
        $data = $this->data;
                
        // Get dishes
        $dishes = Dish::orderby('type', 'asc')->orderBy('name', 'asc')->whereIn('type', array('main','side'))->get();
        $addons = Dish::orderby('type', 'asc')->orderBy('name', 'asc')->whereIn('type', array('addon'))->get();
        #$dishes = Dish::all()->orderBy("name");
        $data['dishes'] = $dishes;
        $data['addons'] = $addons;
           
        return View::make('admin.dish.index', $data);
    }
    
    
    public function getCreate() {
        
        $data['mode'] = 'create';
        $data['title'] = 'Create New Dish';
        
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
