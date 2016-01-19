<?php

namespace Bento\Model;

use Bento\Timestamp\Clock;
use Bento\core\Logic\MaitreD;
use Bento\Admin\Model\Settings;
use DB;
use Cache;
use Carbon\Carbon;

/**
 * On-demand Menu
 */
class Menu extends \Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Menu';
    protected $primaryKey = 'pk_Menu';

    private static function getMenu($sql, $date, $type) {

        // Create normalized cache token
        $date2 = str_replace('-', '', $date);
        $cacheKey = "Menu-SF-$date2-$type";

        // Check the cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Otherwise, query the DB...

        $return = array();
        $return['menus'] = array();

        $menus = DB::select($sql, array($date2));

        // Return if empty
        if (count($menus) == 0)
            return NULL;

        // Otherwise, return the menu(s)!
        foreach ($menus as $menu) {
            // Hide the pk
            $pk_Menu = $menu->pk_Menu;
            unset($menu->pk_Menu);

            // Get Menu_Items            
            $sql2 = "
                SELECT d.pk_Dish itemId, d.name, d.description, d.type, d.image1, d.max_per_order, d.price
                    #IF(d.type != 'side', d.price, NULL) as price
                FROM Menu_Item mi
                LEFT JOIN Dish d on (mi.fk_item = d.pk_Dish)
                WHERE mi.fk_Menu = ? AND d.od_avail
                order by d.type ASC, d.name ASC 
            ";
            $menuItems = DB::select($sql2, array($pk_Menu));

            // Setup the return
            
            $builtMenu = array();
            
            $builtMenu['Menu'] = $menu;
            $builtMenu['MenuItems'] = $menuItems;

            // Create some friendly date text
            $carbon = new Carbon($menu->for_date);
            $builtMenu['Menu']->day_text = $carbon->format('l F jS');
            $builtMenu['Menu']->day_text2 = $carbon->format('l M jS');
            
            // Add to return
            $return['menus'][$menu->meal_name] = $builtMenu;
        }

        // Now add to cache
        $return['source'] = 'cache';
        Cache::put($cacheKey, $return, 5);

        // Return
        $return['source'] = 'db';             
        return $return;
    }


    public static function get($date) {

        // Get the Menu            
        $sql = 'SELECT m.pk_Menu, m.name, m.for_date, m.bgimg, m.menu_type, m.fk_MealType meal_type, 
                    mt.name meal_name, mt.order meal_order, mt.displayStartTime
                FROM Menu m
                LEFT JOIN MealType mt ON (mt.pk_MealType = m.fk_MealType)
                WHERE m.for_date = ? AND m.published AND m.od_avail
                ORDER BY mt.`order` ASC';

        return self::getMenu($sql, $date, 'menu');
    }


    public static function getNext($date) {

        // Get the NEXT Menu
        $sql = <<<SQL
        SELECT m.pk_Menu, m.name, m.for_date, m.bgimg, m.menu_type, m.fk_MealType meal_type,
                mt.name meal_name, mt.order meal_order, mt.displayStartTime
        FROM Menu m
        LEFT JOIN MealType mt ON (mt.pk_MealType = m.fk_MealType)
        WHERE m.for_date = (
                select for_date from Menu m2 where m2.for_date > ? AND m2.published ORDER BY m2.for_date ASC LIMIT 1
            ) 
            AND m.od_avail
SQL;
        
        return self::getMenu($sql, $date, 'next');
    }
    
    
    public static function getCountToday()
    {
        $today = Clock::getLocalTimestamp();
        
        $sql = "select count(*) cnt from Menu where for_date = '$today' AND od_avail";
        
        return DB::select($sql)[0]->cnt;
    }
    
    
    public static function hasMenuForCurrentMealType()
    {
        $today = Clock::getLocalTimestamp(); # Y-m-d
        
        // Determine current MealType
        $md = MaitreD::get();
        $mealType = $md->determineCurrentMealType();
        
        $sql = "select * from Menu where for_date = '$today' AND fk_MealType = $mealType AND od_avail";
        $result = DB::select($sql);
        #var_dump($result); die(); #0;
        
        if (count($result) == 0)
            return false;
        else
            return true;
    }
    
    
    /*
     * Upcoming Late Menu Definition:
     * OD Menus for today, where the menu's startTime+bufferMins is greater than the current time, order by startTime ASC
     * Get the first one
     */
    public static function getUpcomingLateMenu()
    {
        $today = Clock::getLocalTimestamp(); # Y-m-d
        $curTime = Clock::getLocalCarbon()->toTimeString();
        $bufferMins = Settings::find('buffer_minutes')->value;
        #echo count($bufferMins); #0
        #var_dump($bufferMins); #0
        
        $sql = '
            select *, mt.name as mealName, date_add(mt.StartTime, INTERVAL ? MINUTE) as bufferTime
            from Menu m
            left join MealType mt on (m.fk_MealType = mt.pk_MealType)
            where 
                date_add(mt.StartTime, INTERVAL ? MINUTE) >= ? AND 
                for_date = ? AND od_avail
            ORDER BY startTime ASC
        ';
        $result = DB::select($sql, array($bufferMins, $bufferMins, $curTime, $today));
        #var_dump($result); #0
        
        return $result;
    }
        
}
