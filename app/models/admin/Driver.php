<?php

namespace Bento\Admin\Model;

use Bento\Model\LiveInventory;
use Bento\Model\PendingOrder;
use Bento\Admin\Model\Orders;
use DB;

class Driver extends \Eloquent {
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Driver';
    protected $primaryKey = 'pk_Driver';

    private $id;
    
    public function __construct($id = NULL) {
        $this->id = $id;
    }
    

    public static function getCurrentDrivers() {
        
        // Get from db           
        $sql = "select * from Driver where on_shift
            #SELECT d.* 
            #FROM DriverInventory di
            #left join Driver d on (di.fk_Driver = d.pk_Driver)
            #group by fk_Driver
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
    
    
    public static function getAggregateInventory() {
        
        // Get from db           
        $sql = "
        # roll into items for recalculation
        select fk_item, sum(qty) as dqty
        from DriverInventory
        group by fk_item
        ";
        
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    /*
    public static function updateShifts($data) {
        
        // Clear all
        #self::clearAllShifts();
        
        # if drivers are set, add those, and try to see who we can remove. Compile a list of those we can't.
        # if drivers  are not set, try to remove all. Compile a list of those we can't.
        
        // Update
        if (isset($data['drivers'])) 
        {            
            $in = implode(',', $data['drivers']);
            
            // 1. Update who is on shift
             DB::update("update Driver set on_shift = 1 where pk_Driver in ($in)", array());
             
             
            // 2. Clear inventories of those who are NOT on shift
             
            // First, figure out if some drivers can't be taken off shift, because they still
            // have outstanding orders assigned to them.
             
            $desiredOffShiftDrivers = DB::select("select * from Driver where pk_Driver NOT in ($in) AND on_shift");
            
            $in2 = $in;
            $notRemovable = array();
            
            foreach ($desiredOffShiftDrivers as $row) {
                $driver = new Driver($row->pk_Driver);
                $driverOpenOrdersCount = $driver->getOpenOrdersCount();
                echo $driverOpenOrdersCount; die();
            }
             
            // Remove drivers from shift who are safe to remove
            DB::delete("delete from DriverInventory where fk_Driver NOT in ($in2)");
        }
        // Clear everything
        else {
            // Clear all driver inventories 
            DB::delete("delete from DriverInventory");
            
            // Recalculate LiveInventory
            LiveInventory::recalculate();
        }
    }
     * 
     */
    
       
    
    public static function updateInventoryByAssignment($pk_Order, $data) {
        
        $fk_Driver = $data['fk_Driver'];
        
        // Base case that represents no driver
        if ($fk_Driver == 0)
            return;
        
        $orderJson = json_decode(PendingOrder::withTrashed()->where('fk_Order', $pk_Order)->get()[0]->order_json);
        #var_dump($data); die();
        
        $totals = Orders::calculateTotalsFromJson($orderJson);
        
        DB::transaction(function() use ($totals, $fk_Driver)
        {
            foreach ($totals as $itemId => $itemQty) {
              DB::update("update DriverInventory set qty = qty - ?, change_reason='order_assignment' 
                          WHERE fk_item = ? AND fk_Driver = ?", 
                      array($itemQty, $itemId, $fk_Driver));
            }
        });
        
        // Recalculate LiveInventory
        // VJC:2-16-2015: DONT do this. Otherwise you are overwriting the live inventory!
        #LiveInventory::recalculate();  
    }
    
    
    public function getOpenOrdersCount() {
    
        // Get from db           
        $sql = "
        select count(*) count 
        from OrderStatus 
        where fk_Driver = ? and status in ('Open', 'En Route')
        ";
        
        $row = DB::select($sql, array($this->id));
        
        return $row[0]->count;
    }
    
    
    public function removeFromShift() {
        
        $status = $this->safeToRemoveFromShift();
        
        if ($status['ok']) {
            
            DB::delete("delete from DriverInventory where fk_Driver = ?", array($this->id));
            
            DB::update("update Driver set on_shift = 0 where pk_Driver = ?", array($this->id));
            
            return $status;
        }
        else
            return $status;
    }
    
    
    private function safeToRemoveFromShift() {
        
        // Open orders?
        if ($this->getOpenOrdersCount() > 0)
            return array('ok' => false, 'reason' => 'hasOpenOrders', 'desc' => 'Drivers with assigned orders');
        
        // Will removing this driver make it impossible to complete an open order?
        // This should be done last, since after this, LiveInventory is deducted
        if (!$this->removeInventoryFromLiveInventory())
            return array('ok' => false, 'reason' => 'cantCompleteOrders', 'desc' => 'Removing results in incompletable orders');
        
        // If we're here, all good
        return array('ok' => true, 'reason' => '');
    }
    
    
    private function removeInventoryFromLiveInventory() {
        
        // Try to deduct it. If it throws an exception, it means that the LiveInventory
        // is trying to go to negative, which means that it if we take this driver off shift,
        // some order cannot be completed.
        try {
            
            $invRows = $this->getInventory();
                        
            DB::transaction(function() use ($invRows)
            {
                foreach ($invRows as $inv) {
                  DB::update("update LiveInventory set qty = qty - ? WHERE fk_item = ?", array($inv->qty, $inv->fk_item));
                }
            });
            
            return true;
        } 
        catch (\Exception $ex) {
            return false;
        }
    }
    
    
    private function getInventory() {
        
        // Get from db           
        $sql = "select * from DriverInventory where fk_Driver = ?";
        
        $rows = DB::select($sql, array($this->id));
        
        return $rows;
    }
    
}
