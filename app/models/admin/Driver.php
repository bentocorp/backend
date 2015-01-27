<?php

namespace Bento\Admin\Model;

use Bento\Model\LiveInventory;
use DB;

class Driver extends \Eloquent {
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Driver';
    protected $primaryKey = 'pk_Driver';


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
    
    
    public static function getCurrentDriversForDropdown() {
        
        $drivers = self::getCurrentDrivers();
        $dropdown = array();
        
        $dropdown[0] = '';
        
        foreach($drivers as $driver) {
            $dropdown[$driver->pk_Driver] = "$driver->firstname $driver->lastname";
        }
        
        return $dropdown;
    }
    
    
    public static function getDriverInventory($pk_Driver) {
        
        // Get from db           
        $sql = "
        select 
            di.fk_Driver,
            di.fk_item, di.item_type,
            di.qty,
            d.`type`,
            d.`name`, d.short_name
        from DriverInventory di 
        left join Dish d on (di.fk_item = d.pk_Dish)
        where di.fk_Driver = $pk_Driver
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function overwriteInventory($pk_Driver, $data) {
        
        // Update driver inventory
        
        DB::transaction(function() use ($pk_Driver, $data)
        {
            // First copy inventory to the Log

            $sql = "
            insert into DriverInventoryLog 
            select NULL, t.* from DriverInventory t where t.fk_Driver = ?
            ";

            DB::insert($sql, array($pk_Driver));

            // Now delete

            $sql2 = "delete from DriverInventory where fk_Driver = ?";

            DB::delete($sql2, array($pk_Driver));

            // Now insert new

            foreach($data as $key => $val) {
                $sql3 = "
                insert into DriverInventory (fk_Driver, fk_item, qty, change_reason)
                values (?,?,?,?)
                ";

                DB::insert($sql3, array($pk_Driver, $key, $val, 'admin_update'));
            }
        });
        
        // Recalculate the LiveInventory
        
        LiveInventory::recalculate();
    }
}
