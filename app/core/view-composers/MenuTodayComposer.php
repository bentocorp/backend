<?php namespace Bento\ViewComposer;

use Bento\core\Status;
use Bento\Admin\Model\Menu;


class MenuTodayComposer {

    public function compose($view)
    {
        // Get today's menu
        $date = Menu::getDateForTodaysMenu();
        $menusApi = Menu::get($date);
        $view->with('menusApi', $menusApi);
                
        // Meal Mode
        $mealMode = Status::getMealMode();
        $mealModeName = $mealMode->name;
        $view->with('mealMode', $mealMode);
        
        // And extract the composed menu based on the current meal mode
        $mealMenu = isset($menusApi['menus'][$mealModeName]) ? $menusApi['menus'][$mealModeName] : NULL;
        $view->with('mealMenu', $mealMenu);
    }

}