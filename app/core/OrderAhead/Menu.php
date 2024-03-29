<?php

namespace Bento\core\OrderAhead;


use Bento\core\Logic\MaitreD;
use Bento\Timestamp\Clock;
use Bento\Admin\Model\Settings;
use DB;
#use Cache;
use Carbon\Carbon;


class Menu {


    private static function getMenu($sql) {

        /*
        // Create normalized cache token
        $date2 = str_replace('-', '', $date);
        $cacheKey = "Menu-SF-OA-$date2-$type";

        // Check the cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
         * 
         */

        // Otherwise, query the DB...

        $return = array();
        $return['menus'] = array();

        #echo $sql; die(); #0
        $menus = DB::select($sql);

        // Return if empty
        if (count($menus) == 0)
            return NULL;

        // Otherwise, return the menu(s)!
        foreach ($menus as $menu) 
        {
            // Hide the pk
            $pk_Menu = $menu->menu_id;
            unset($menu->pk_Menu); # Just in case, but not really needed
            
            // Hide the oa_times string (we're parsing it latah)
            $oa_times = $menu->oa_times;
            unset($menu->oa_times);

            // Get Menu_Items            
            $sql2 = "
                SELECT d.pk_Dish itemId, d.name, d.description, d.type, d.image1, d.max_per_order, d.price,
                    (select qty from MenuInventory minv where minv.fk_Menu = ? AND minv.fk_item = d.pk_Dish) qty,
                    99 as max_per_bento
                    #IF(d.type != 'side', d.price, NULL) as price
                FROM Menu_Item mi
                LEFT JOIN Dish d on (mi.fk_item = d.pk_Dish)
                WHERE mi.fk_Menu = ? AND d.oa_avail
                order by d.type ASC, d.name ASC 
            ";
            $menuItems = DB::select($sql2, array($pk_Menu, $pk_Menu));

            // Setup the return
            
            $builtMenu = array();
            
            $builtMenu['Menu'] = $menu;
            $builtMenu['MenuItems'] = $menuItems;
            $builtMenu['Times'] = self::parseTimes($oa_times);
            // random | useDefault | first
            $builtMenu['DefaultTimeMode'] = 'random';

            // Create some friendly date text
            $carbon = new Carbon($menu->for_date);
            $dayText = $carbon->format('l F jS');
            $builtMenu['Menu']->day_text = $dayText;
            
            // Add to return
            $return['menus'][] = $builtMenu;
        }

        // Now add to cache
        #$return['source'] = 'cache';
        #Cache::put($cacheKey, $return, 5);

        // Return
        $return['source'] = 'db';             
        return $return;
    }


    /**
     * Get Order Ahead menus based on today's date
     * @return type
     */
    public static function getMenus($fk_Kitchen) {
        
        $md = MaitreD::get();
        $today = Clock::getLocalTimestamp();
        $futureDate = $md->getMaxOaDate();
        $availNow = $md->getAvailableMealsLeftToday();
        $todayQ = '';
        
        if (count($availNow) > 0) 
        {
            $list = array();
            
            foreach ($availNow as $meal) {
                $list[] = $meal->pk_MealType;
            }
            
            $in = implode(',' , $list);
            $todayQ = " (m.for_date = '$today' && fk_MealType IN ($in)) ";
        }
        
        $or = $todayQ != '' ? 'OR' : '';
        $futureQ = " $or (m.for_date > '$today' && m.for_date <= '$futureDate') ";

        // Get the Menu            
        $sql = "SELECT m.pk_Menu menu_id, m.name, m.for_date, m.bgimg, m.menu_type, m.fk_MealType meal_type, m.oa_times,
                    mt.name meal_name, mt.`order` meal_order
                FROM Menu m
                LEFT JOIN MealType mt ON (mt.pk_MealType = m.fk_MealType)
                WHERE 
                    ($todayQ $futureQ)  
                    AND m.published AND m.oa_avail
                    AND m.fk_Kitchen = $fk_Kitchen
                ORDER BY m.for_date ASC, mt.`order` ASC";

        return self::getMenu($sql);
    }
    
    
    /*
     * What we're building:
     * 
     * this:
     * 17:00-18:00,18:00-19:00,19:00-20:00,20:00-21:00
     * 
     * becomes:
     * array(
     *      {start:"17:00", end:"18:00", available:true/false},
     * )
     * 
     */
    public static function parseTimes($timesStr) 
    {
        // Return array
        $retAr = array();
        
        // Get each range
        $ranges = explode(',' , $timesStr);
        
        // Get OA deliv price. Later we can do some custom stuff per delivery window
        $delivPrice = Settings::find('oa_delivery_price')->value;
        
        foreach ($ranges as $range)
        {
            // Get each time
            $times = explode('-' , $range);
            $start = $times[0];
            $end = $times[1];
            
            // Build final object
            $obj = new \stdClass();
            
            $obj->start = $start;
            $obj->end = $end;
            $obj->available = true; # For showing that we have this time, but it's sold out; For demand shaping.
            $obj->delivery_price = $delivPrice; # For different deliv prices per window; For demand shaping.
            $obj->isDefault = false; # For forcing the app to default to a particular window; For demand shaping.
            
            // Push onto return array
            $retAr[] = $obj;
        }
        
        return $retAr;
    }

        
}
