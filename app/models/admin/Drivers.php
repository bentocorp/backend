<?php

namespace Bento\Admin\Model;

use DB;

class Drivers {


    public static function getCurrentDrivers() {
        
        // Get from db           
        $sql = "
            SELECT d.* 
            FROM DriverInventory di
            left join Driver d on (di.fk_Driver = d.pk_Driver)
            group by fk_Driver
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function getDriverInventory($pk_Driver) {
        
        // Get from db           
        $sql = "
            select 
                di.fk_Driver,
                di.fk_item, di.item_type,
                di.qty,
                d.`type`
                d.`name`, d.short_name
            from DriverInventory di 
            left join Dish d on (di.fk_item = d.pk_Dish)
            where di.fk_Driver = $pk_Driver
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
                
}
