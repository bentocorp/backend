<?php

class MenuCtrl extends BaseController {

    public function getShow($date) {
        
        #$todayDate = date('Y-m-d');
        #echo $todayDate;
        $menuToday = Menu::where('for_date', $date)->first();
        
        echo "$date";
        
        Cache::put('laraTest', 'laraValue', 5);
    }

}
