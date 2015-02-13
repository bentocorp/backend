<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Menu;
use Bento\Admin\Model\Dish;
use View;
use Redirect;



class MenuCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
        
        // Get today's menu
        $date = Menu::getDateForTodaysMenu();
        $menu = Menu::get($date);
        $data['menu'] = $menu;
        
        // Get upcoming
        $menuUpcoming = Menu::getRelative($date, '>');
        $data['menuUpcoming'] = $menuUpcoming;
        
        // Get past
        $menuPast = Menu::getRelative($date, '<', 'DESC');
        $data['menuPast'] = $menuPast;
           
        return View::make('admin.menu.index', $data);
    }
    
    
    public function getCreate() {
        
        $dishesAll = Dish::orderby('type', 'asc')->orderBy('name', 'asc')->get();
        $data['dishesAll'] = $dishesAll;
        
        $data['dishesInMenu'] = array();
        
        $data['mode'] = 'Create New Menu';
        $data['title'] = $data['mode'];
        
        return View::make('admin.menu.crud', $data);
    }
    
    
    public function postCreate() {
              
        $menu = Menu::createNew($_POST);
        
        return Redirect::to("admin/menu/edit/$menu->pk_Menu")->with('msg', 
            array('type' => 'success', 'txt' => "New menu for <b>$menu->for_date</b> created."));
    }
    
    
    public function getEdit($id) {
        
        $menu = Menu::find($id);
        $data['menu'] = $menu;
        
        $dishesInMenu = Dish::getDishesByMenuId($id);
        $data['dishesInMenu'] = $dishesInMenu;
        
        $dishesAll = Dish::orderby('type', 'asc')->orderBy('name', 'asc')->get();
        $data['dishesAll'] = $dishesAll;
        
        $data['mode'] = 'Editing';
        $data['title'] = $data['mode'].': '. "$menu->for_date $menu->name";
        
        return View::make('admin.menu.crud', $data);
    }
    
    
    public function postEdit($id) {
        
        $data = $_POST;
        
        Menu::saveChanges($id, $data);
        
        return Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => "Menu for <b>{$data['for_date']}</b> saved."));
    }
    
    
    
}
