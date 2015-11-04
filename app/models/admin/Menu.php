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
        $return['menus'] = array();

        // Get the Menu            
        $sql = "SELECT m.*,
                    mt.name meal_name,
                    mt.order meal_order,
                    mt.startTime meal_start
                FROM Menu m 
                left join MealType mt on (m.fk_MealType = mt.pk_MealType)
                WHERE m.for_date = ? AND m.published
                ORDER BY mt.`order` ASC";
        
        $menus = DB::select($sql, array($date));

        // Return if empty
        if (count($menus) == 0)
            return NULL;

        // Otherwise, return the menu(s)!
        foreach ($menus as $menu) {
            
            // Get Menu_Items
            $sql2 = "
                SELECT 
                        d.*,
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

            // Setup the return
            
            $builtMenu = array();
            
            $builtMenu['Menu'] = $menu;
            $builtMenu['MenuItems'] = $menuItems;
            
            // Add to return
            $return['menus'][$menu->meal_name] = $builtMenu;
        }

        // Return
        $return['source'] = 'db';             
        return $return;
    }


    public static function getRelative($date, $dateComparator = '=', $sort = 'ASC') {

        // Otherwise, query the DB...

        $return = array();

        // Get the Menu            
        $sql = "SELECT m.*,
                    mt.name meal_name,
                    mt.order meal_order,
                    mt.startTime meal_start
                FROM Menu m
                left join MealType mt on (m.fk_MealType = mt.pk_MealType)
                WHERE m.for_date $dateComparator ? AND m.published
                ORDER BY m.for_date $sort
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
        
        // Set type to fixed for lunch
        // VJC 2015-06-03: Commenting out so Joseph can test the different flows for lunch/dinner
        #if ($data['fk_MealType'] == 2)
        #    $data['menu_type'] = 'fixed';
        
        // Insert into Menu
        $menu = Menu::create($data);
                
        // Insert into MenuItems
        Menu_Item::setDishes($menu->pk_Menu, $menuItems);
        
        return $menu;
    }
    
    
    public function scopeSaveChanges($query, $id, $data) {
        
        // Collect MenuItems
        $menuItems = $this->collectMenuItems($data);
        
        // Set type to fixed for lunch
        #if ($data['fk_MealType'] == 2)
        #    $data['menu_type'] = 'fixed';
        #else
        #    $data['menu_type'] = 'custom';
        
        // Update Menu
        unset($data['_token']);
        
        DB::table('Menu')
                    ->where('pk_Menu', $id)
                    ->update($data);
        
        // Insert into MenuItems
        Menu_Item::setDishes($id, $menuItems);
    }
    
    
    private function collectMenuItems(& $data) {
        
        $menuItems = isset($data['dish']) ? $data['dish'] : array();
        unset($data['dish']);
        
        return $menuItems;
    }
        
        
        
}
