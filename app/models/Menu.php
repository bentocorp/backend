<?php

namespace Bento\Model;

use DB;
use Cache;

class Menu extends \Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Menu';
        protected $primaryKey = 'pk_Menu';
        
        public static function get($date) {
            
            // Create normalized cache token
            $date = str_replace('-', '', $date);
            $cacheKey = "Menu-SF-$date";

            // Check the cache first
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            // Otherwise, query the DB...
            
            $return = array();
            
            // Get the Menu
            $sql = 'SELECT pk_Menu, name, for_date FROM Menu WHERE for_date = ? AND published';
            $menu = DB::select($sql, array($date));
            
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
                SELECT name, description, type, temp, image1, image2, max_per_order 
                FROM Menu_Item
                LEFT JOIN Dish on (fk_item = pk_Dish)
                WHERE fk_Menu = ?
                order by type
            ";
             $menuItems = DB::select($sql2, array($pk_Menu));
             
             $return['Menu'] = $menu;
             $return['MenuItems'] = $menuItems;
             
             // Now add to cache
             $return['source'] = 'cache';
             Cache::put($cacheKey, $return, 5);
             
             // Return
            $return['source'] = 'db';             
             return $return;
        }
}
