<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Menu;
use Bento\Admin\Model\Dish;
use Bento\core\Status;
use Bento\Model\MealType;
use View;
use Redirect;



class MenuCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
        
        // Menu date
        $date = Menu::getDateForTodaysMenu();
        
        // Get upcoming
        $menuUpcoming = Menu::getRelative($date, '>');
        $data['menuUpcoming'] = $menuUpcoming;
        
        // Get past
        $menuPast = Menu::getRelative($date, '<', 'DESC');
        $data['menuPast'] = $menuPast;
           
        return View::make('admin.menu.index', $data);
    }
    
    
    public function getCreate() {
        
        // Possible menu items
        
        $dishesAll = Dish::orderby('type', 'asc')->orderBy('name', 'asc')->get();
        $data['dishesAll'] = $dishesAll;
        
        $data['dishesInMenu'] = array();
                
        $addonsAll = Dish::orderby('type', 'asc')->orderBy('name', 'asc')->whereIn('type', array('addon'))->get();
        $data['addonsAll'] = $addonsAll;
        
        $data['addonsInMenu'] = array();
        
        // Other stuff
        
        $data['mode'] = 'Create New Menu';
        $data['title'] = $data['mode'];
        
        // Get the meal modes for the dropdown
        $mealModes = Status::getMealModesForDropdown();
        $data['mealModesAr'] = $mealModes;
        
        return View::make('admin.menu.crud', $data);
    }
    
    
    public function postCreate() {
        
        // If you try to save two dinner menus for the same day, for instance, we'll get an exception
        try{
            $menu = Menu::createNew($_POST);
        } 
        catch (\Illuminate\Database\QueryException $e) {
            $mealType = MealType::find($_POST['fk_MealType']);
            $mealName = $mealType->name;
            
            return Redirect::back()->with('msg', 
                array('type' => 'danger', 'txt' => "Menu for <b>{$_POST['for_date']}</b> already has a <b>$mealName</b> menu defined! Menu NOT created.")); 
        }
        
        return Redirect::to("admin/menu/edit/$menu->pk_Menu")->with('msg', 
            array('type' => 'success', 'txt' => "New menu for <b>$menu->for_date</b> created."));
    }
    
    
    public function getEdit($id) {
        
        $menu = Menu::find($id);
        $data['menu'] = $menu;
        
        // Possible menu items
                
        $dishesAll = Dish::orderby('type', 'asc')->orderBy('name', 'asc')->whereIn('type', array('main','side'))->get();
        $data['dishesAll'] = $dishesAll;
        
        $dishesInMenu = Dish::getDishesByMenuId($id);
        $data['dishesInMenu'] = $dishesInMenu;
                
        $addonsAll = Dish::orderby('type', 'asc')->orderBy('name', 'asc')->whereIn('type', array('addon'))->get();
        $data['addonsAll'] = $addonsAll;
        
        $addonsInMenu = Dish::getAddonsByMenuId($id);
        $data['addonsInMenu'] = $addonsInMenu;
        
        // Other stuff
        
        $data['mode'] = 'Editing';
        $data['title'] = $data['mode'].': '. "$menu->for_date $menu->name";
        
        // Get the meal modes for the dropdown
        $mealModes = Status::getMealModesForDropdown();
        $data['mealModesAr'] = $mealModes;
        
        return View::make('admin.menu.crud', $data);
    }
    
    
    public function postEdit($id) {
        
        $data = $_POST;
        
        // If you try to save two dinner menus for the same day, for instance, we'll get an exception
        try {
            Menu::saveChanges($id, $data);
        } 
        catch (\Illuminate\Database\QueryException $e) {
            $mealType = MealType::find($data['fk_MealType']);
            $mealName = $mealType->name;
            
            return Redirect::back()->with('msg', 
                array('type' => 'danger', 'txt' => "Menu for <b>{$data['for_date']}</b> already has a <b>$mealName</b> menu defined! Menu not saved.")); 
        }
        
        return Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => "Menu for <b>{$data['for_date']}</b> saved."));
    }
    
    
    
}
