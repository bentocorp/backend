<?php

namespace Bento\Model;

use DB;
use Cache;
use Carbon\Carbon;

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
                WHERE mi.fk_Menu = ?
                order by d.type ASC, d.name ASC 
            ";
            $menuItems = DB::select($sql2, array($pk_Menu));

            // Setup the return
            
            $builtMenu = array();
            
            $builtMenu['Menu'] = $menu;
            $builtMenu['MenuItems'] = $menuItems;

            // Create some friendly date text
            $carbon = new Carbon($menu->for_date);
            $dayText = $carbon->format('l F jS');
            $builtMenu['Menu']->day_text = $dayText;
            
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
                    mt.name meal_name, mt.order meal_order
                FROM Menu m
                LEFT JOIN MealType mt ON (mt.pk_MealType = m.fk_MealType)
                WHERE m.for_date = ? AND m.published 
                ORDER BY mt.`order` ASC';

        return self::getMenu($sql, $date, 'menu');
    }


    public static function getNext($date) {

        // Get the NEXT Menu
        $sql = <<<SQL
        SELECT m.pk_Menu, m.name, m.for_date, m.bgimg, m.menu_type, m.fk_MealType meal_type,
                mt.name meal_name
        FROM Menu m
        LEFT JOIN MealType mt ON (mt.pk_MealType = m.fk_MealType)
        WHERE m.for_date = (
                select for_date from Menu m2 where m2.for_date > ? AND m2.published ORDER BY m2.for_date ASC LIMIT 1
            )               
SQL;
        
        return self::getMenu($sql, $date, 'next');
    }
        
}
