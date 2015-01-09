<?php

namespace Bento\Ctrl;

use Bento\Model\Menu;
use Response;

class MenuCtrl extends \BaseController {

    /**
     * Get a menu for a given day
     * 
     * @param date $date In the format of date('Y-m-d') or date('Ymd'). Meaning YYYY-mm-dd or YYYYmmdd both seem to work with MySQL
     * @return json Menu
     */
    public function show($date) {
                
        $menu = Menu::get($date);
        
        if ($menu === NULL)
            return Response::make(null, 404);
        else
            return Response::json($menu);
    }

}
