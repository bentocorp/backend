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
                        
            $menu = DB::select($sql, array($date2));
            
            // Return if empty
            if (count($menu) == 0)
                return NULL;
            else 
                $menu = $menu[0];
            
            // Hide the pk
            $pk_Menu = $menu->pk_Menu;
            unset($menu->pk_Menu);
            
            // Get Menu_Items            
            $sql2 = "
                SELECT d.pk_Dish itemId, d.name, d.description, d.type, d.image1, d.max_per_order 
                FROM Menu_Item mi
                LEFT JOIN Dish d on (mi.fk_item = d.pk_Dish)
                WHERE mi.fk_Menu = ?
                order by d.type ASC, d.name ASC 
            ";
            $menuItems = DB::select($sql2, array($pk_Menu));
            
            // Setup the return
            $return['Menu'] = $menu;
            $return['MenuItems'] = $menuItems;
             
            // Create some friendly date text
            $carbon = new Carbon($menu->for_date);
            $dayText = $carbon->format('l F jS');
            $return['Menu']->day_text = $dayText;
            
            // Now add to cache
            $return['source'] = 'cache';
            Cache::put($cacheKey, $return, 5);
             
            // Return
            $return['source'] = 'db';             
            return $return;
        }
        
        
        public static function get($date) {
            
            // Get the Menu            
            $sql = 'SELECT pk_Menu, name, for_date, bgimg 
                    FROM Menu 
                    WHERE for_date = ? AND published AND fk_MealType = 3';
            
            return self::getMenu($sql, $date, 'menu');
        }
        
        
        public static function getNext($date) {
            
            // Get the NEXT Menu            
            $sql = 'SELECT pk_Menu, name, for_date, bgimg 
                    FROM Menu 
                    WHERE for_date > ? AND published
                    ORDER BY for_date ASC LIMIT 1';
            
            return self::getMenu($sql, $date, 'next');
        }
        
}
