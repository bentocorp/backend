<?php

namespace Bento\Admin\Model;

use Bento\Admin\Model\Menu_Item;
use DB;
use Carbon\Carbon;


class Menu extends \Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Menu';
    protected $primaryKey = 'pk_Menu';
    protected $guarded = array('pk_Menu');

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
            SELECT 
                    d.pk_Dish, d.name, d.description, d.type, d.short_name,
                (
                            # summate
                            select sum(qty) as total
                            from DriverInventory di
                            where fk_item = d.pk_Dish
                            group by fk_item
                ) as DriverInventoryTotal
            FROM Menu_Item mi
            LEFT JOIN Dish d on (mi.fk_item = d.pk_Dish)
            WHERE mi.fk_Menu = ?
            order by d.type ASC, d.name ASC
        ";
        $menuItems = DB::select($sql2, array($menu->pk_Menu));

        $return['Menu'] = $menu;
        $return['MenuItems'] = $menuItems;

        // Return
        $return['source'] = 'db';             
        return $return;
    }


    public static function getRelative($date, $dateComparator = '=', $sort = 'ASC') {

        // Otherwise, query the DB...

        $return = array();

        // Get the Menu            
        $sql = "SELECT *  
                FROM Menu 
                WHERE for_date $dateComparator ? AND published
                ORDER BY for_date $sort
         ";
        $menus = DB::select($sql, array($date));

        // Return if empty
        if (count($menus) == 0)
            return NULL;
        else {

            $compoundMenu = array();

            foreach($menus as $menu) {
                $compoundMenu['Menu'] = $menu;

                // Get Menu_Items
                $sql2 = "
                    SELECT 
                            d.pk_Dish, d.name, d.description, d.type, d.short_name,
                        (
                                    # summate
                                    select sum(qty) as total
                                    from DriverInventory di
                                    where fk_item = d.pk_Dish
                                    group by fk_item
                        ) as DriverInventoryTotal
                    FROM Menu_Item mi
                    LEFT JOIN Dish d on (mi.fk_item = d.pk_Dish)
                    WHERE mi.fk_Menu = ?
                    order by d.type ASC, d.name ASC
                ";

                $menuItems = DB::select($sql2, array($menu->pk_Menu));

                $compoundMenu['MenuItems'] = $menuItems;

                $return[] = $compoundMenu;
            }
        }

        // Return
        return $return;
    }
    
    
    public static function getDateForTodaysMenu($format = 'Ymd') 
    {    
        $date = Carbon::now('America/Los_Angeles')->format($format);
 
        return $date;
    }
    
    
    public function scopeCreateNew($query, $data) {
        
        // Collect MenuItems
        $menuItems = $this->collectMenuItems($data);
        
        // Insert into Menu
        $menu = Menu::create($data);
                
        // Insert into MenuItems
        Menu_Item::setDishes($menu->pk_Menu, $menuItems);
        
        return $menu;
    }
    
    
    public function scopeSaveChanges($query, $id, $data) {
        
        // Collect MenuItems
        $menuItems = $this->collectMenuItems($data);
        
        // Update Menu
        unset($data['_token']);
        
        DB::table('Menu')
                    ->where('pk_Menu', $id)
                    ->update($data);
        
        // Insert into MenuItems
        Menu_Item::setDishes($id, $menuItems);
    }
    
    
    private function collectMenuItems(&$data) {
        
        $menuItems = isset($data['dish']) ? $data['dish'] : array();
        unset($data['dish']);
        
        return $menuItems;
    }
        
        
        
}
