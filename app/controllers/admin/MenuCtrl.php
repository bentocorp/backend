<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Menu;
use View;
use Carbon\Carbon;



class MenuCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
        
        $date = Carbon::now('America/Los_Angeles')->format('Ymd');
        
        // Get today's menu
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
