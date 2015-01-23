<?php

namespace Bento\Admin\Model;

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
                        
            // Otherwise, query the DB...
            
            $return = array();
            
            // Get the Menu            
            $sql = "SELECT *  
                    FROM Menu 
                    WHERE for_date = ? AND published";
            $menu = DB::select($sql, array($date));
            
            // Return if empty
            if (count($menu) == 0)
                return NULL;
            else 
                $menu = $menu[0];
            
            // Get Menu_Items
            $sql2 = "
                SELECT * 
                FROM Menu_Item mi
                LEFT JOIN Dish d on (mi.fk_item = d.pk_Dish)
                WHERE mi.fk_Menu = ?
                order by type
            ";
            $menuItems = DB::select($sql2, array($menu->pk_Menu));
             
            $return['Menu'] = $menu;
            $return['MenuItems'] = $menuItems;
                          
            // Return
            $return['source'] = 'db';             
            return $return;
        }
        
        
        
}
