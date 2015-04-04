<?php

namespace Bento\Admin\Model;

use Bento\Model\LiveInventory;
use Bento\Model\CustomerBentoBox;
use DB;

class Driver extends \Eloquent {
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Driver';
    protected $primaryKey = 'pk_Driver';
    protected $guarded = array('pk_Driver');

    #private $pk_Driver = NULL;
    
    public function __construct($attributes = array(), $pk_Driver = NULL) 
    {
        // So I can pass a less verbose NULL into the constructor
        if (!is_array($attributes))
            $attributes = array();
        
        // Make sure the parent is called
        parent::__construct($attributes);
        
        // Set the pk if the parent constructor hasn't yet
        if (!isset($this->pk_Driver))
            $this->pk_Driver = $pk_Driver;
    }
    
    
    private function id() {
        return $this->pk_Driver;
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
        
        #$dropdown[0] = '';
        
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
            /* This is already done with a DB Trigger
            // First copy inventory to the Log

            $sql = "
            insert into DriverInventoryLog 
            select NULL, t.* from DriverInventory t where t.fk_Driver = ?
            ";

            DB::insert($sql, array($pk_Driver));
             * 
             */

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
    
    
    public static function saveChanges($id, $data) {
        
        unset($data['_token']);
        
        DB::table('Driver')
                    ->where('pk_Driver', $id)
                    ->update($data);
    }
    
    
    /*
     * Member Functions:
     */
    
        
    public function getOpenOrdersCount() {
    
        // Get from db           
        $sql = "
        select count(*) count 
        from OrderStatus 
        where fk_Driver = ? and status in ('Open', 'En Route')
        ";
        
        $row = DB::select($sql, array($this->id()));
        
        #var_dump($this->id()); die(); #0
        return $row[0]->count;
    }
    
    
    public function removeFromShift() {
        
        $status = $this->safeToRemoveFromShift();
        #var_dump($status); die(); #0
        
        if ($status['ok']) {
            
            DB::delete("delete from DriverInventory where fk_Driver = ?", array($this->id()));
            
            DB::update("update Driver set on_shift = 0 where pk_Driver = ?", array($this->id()));
            
            return $status;
        }
        else
            return $status;
    }
    
    
    private function safeToRemoveFromShift() {
        
        // Open orders?
        // VJC 3/13/2015: Removing for Asana 26535038097950/29248148753131
        #if ($this->getOpenOrdersCount() > 0)
            #return array('ok' => false, 'reason' => 'hasOpenOrders', 'desc' => 'Drivers with assigned orders');
        
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
        
        $rows = DB::select($sql, array($this->id()));
        
        return $rows;
    }
    
    
    /**
     * Add an order back into a Driver's inventory
     * 
     * @param int $pk_Order
     */
    public function addOrderToInventory($pk_Order)
    {
        $order = new \Bento\Model\Order(null, $pk_Order);
        
        $orderJsonObj = $order->getOrderJsonObj();
        
        $totals = CustomerBentoBox::calculateTotalsFromJson($orderJsonObj);
        
        $id = $this->id();
        
        // Subtract
        DB::transaction(function() use ($totals, $id)
        {
            foreach ($totals as $itemId => $itemQty) {
              DB::update("update DriverInventory set qty = qty + ?, change_reason='order_assignment' 
                          WHERE fk_item = ? AND fk_Driver = ?", 
                      array($itemQty, $itemId, $id));
            }
        });
    }
    
    
    /**
     * Subtract an order from a Driver's inventory
     * 
     * @param int $pk_Order
     */
    public function subtractOrderFromInventory($pk_Order)
    {
        $order = new \Bento\Model\Order(null, $pk_Order);
        
        $orderJsonObj = $order->getOrderJsonObj();
        
        $totals = CustomerBentoBox::calculateTotalsFromJson($orderJsonObj);
        
        $id = $this->id();
        
        // Subtract
        DB::transaction(function() use ($totals, $id)
        {
            foreach ($totals as $itemId => $itemQty) {
              DB::update("update DriverInventory set qty = qty - ?, change_reason='order_assignment' 
                          WHERE fk_item = ? AND fk_Driver = ?", 
                      array($itemQty, $itemId, $id));
            }
        });
    }
    
}
