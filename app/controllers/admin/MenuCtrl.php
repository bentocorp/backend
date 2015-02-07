<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Menu;
use View;



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
    
    
    
}
