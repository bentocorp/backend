<?php

namespace Bento\Admin\Model;

use Bento\Model\LiveInventory;
use Bento\Model\CustomerBentoBox;
use Bento\core\Util\DbUtil;
use DB;

class Driver extends \Eloquent {
    
    use \SoftDeletingTrait;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Driver';
    protected $primaryKey = 'pk_Driver';
    protected $guarded = array('pk_Driver');
    protected $dates = ['deleted_at'];

    
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
    
    
    /**
     * WARNING: This method is deprecated in favor of updateInventory() instead.
     * @deprecated
     */
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

            // Now, delete

            $sql2 = "delete from DriverInventory where fk_Driver = ?";

            DB::delete($sql2, array($pk_Driver));

            // Now insert new
            
            foreach($data['newqty'] as $key => $val) {
                
                // Ignore hidden fields
                #$keyParts = explode('-', $key);
                #if ($keyParts[0] != 'newqty')
                    #continue;
                
                $sql3 = "
                insert into DriverInventory (fk_Driver, fk_item, qty, change_reason)
                values (?,?,?,?)
                ";

                DB::insert($sql3, array($pk_Driver, $key, $val, 'admin_update'));
            }
            
            // Recalculate (overwrite) the LiveInventory with the DriverInventory
            LiveInventory::recalculate();
        });
        
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
        
        // Take the driver off-shift immediately, so no other processes will assign him
        DB::update("update Driver set on_shift = 0 where pk_Driver = ?", array($this->id()));
        
        try {
            $status = $this->safeToRemoveFromShift();
            #var_dump($status); die(); #0

            if ($status['ok']) {

                DB::delete("delete from DriverInventory where fk_Driver = ?", array($this->id()));
                
                // And since we already took him off shift above, we're done.

                return $status;
            }
            else {
                // Something's gone wrong, we can't pull them off shift yet
                DB::update("update Driver set on_shift = 1 where pk_Driver = ?", array($this->id()));

                return $status;
            }
        } 
        catch (\Exception $e) {
        // Catch for anything else, so as not to leave this record in a dirty state
            DB::update("update Driver set on_shift = 1 where pk_Driver = ?", array($this->id()));
        }
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
    
    /**
     * 
     * @param boolean $forupdate Do a locking read via FOR UPDATE?
     * @return array
     */
    private function getInventory($forupdate = false) {
        
        $forupdateSql = '';
        if ($forupdate)
            $forupdateSql = 'FOR UPDATE';
        
        // Get from db           
        $sql = "SELECT * FROM DriverInventory WHERE fk_Driver = ? $forupdateSql";
        #echo $sql; die(); #0
        
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
    
    
    /**
     * Set everything for this driver's DriverInventory to zero
     * 
     * @param bool $transaction Should this be a transaction? Useful to avoid 
     *      transaction nesting, which MySQL doesn't support
     */
    public function emptyInventory($transaction = true) {
        
        if ($transaction) 
        {
            DB::transaction(function() 
            {
                $this->doEmptyInventory();
            });
        }
        else
            $this->doEmptyInventory();
    }
    
    private function doEmptyInventory() {
        
        ## 1. Do a locking read on the driver's inventory
        $di = $this->getInventory(true);
        
        // We need to index the di return, since it's just a dumb array
        $diIdx = DbUtil::makeIndexFromResults($di, 'fk_item');
        
        ## 2. Determine the diffs

        $diffs = array(); // Track diffs
        
        foreach ($diIdx as $key => $row) {
            $diffs[$key] = $row->qty;
        }
        
        
        ## 3. Delete the DriverInventory for this driver

        $sql1 = 'delete from DriverInventory where fk_Driver = ?';
        
        DB::delete($sql1, array($this->id()));

        
        ## 4. Subtract the driver's stuff from the LiveInventory with the diffs

        foreach($diffs as $itemId => $diffAmt) {
            $sql2 = 'update LiveInventory set qty = greatest(0, qty - :diff) where fk_item = :item AND item_type = "Dish"';
            DB::update( $sql2,
                array('diff'=>$diffAmt, 'item'=>$itemId)
            );
        }
    }
    
    
    /**
     * Update this driver's inventory, using a diff qty
     * 
     * There are a number of scenarios that we need to account for:
     * 
     * 1. The item is already in both DI and LI:
     *    This is a locking read for DI, and an update in both tables.
     * 
     * 2. The item is not in the DI, but is in the LI: 
     *    This might happen when first bringing a driver on shift for example, or if we've
     *    added a new item the menu midway, and we're going along and updating all driver inventories.
     *    This is an insert in DI, and update in LI. No locks needed.
     * 
     * 3. The item is in neither place:
     *    When we're adding the first driver of the day. This is two inserts.
     * 
     * 4. The item is in the DI, but not in the LI (inverse of 2):
     *    This should never happen.
     * 
     */
    public function updateInventory($data, $transaction = true) {
        
        if ($transaction) 
        {
            DB::transaction(function() use ($data)
            {
                $this->doUpdateInventory($data);
            });
        }
        else
            $this->doUpdateInventory($data);
    }
    
    private function doUpdateInventory($data) {
        
        #DB::transaction(function() use ($data)
        #{
        
        ## 1. Do a locking read on the driver's inventory

        $di = $this->getInventory(true);

        // We need to index the di return, since it's just a dumb array
        $diIdx = DbUtil::makeIndexFromResults($di, 'fk_item');

        ## 2. Determine the diffs

        $newQtys = $data['newqty']; // 'newqty' is the html form array
        $diffs = array(); // Track diffs

        foreach($newQtys as $key => $newval) 
        {
            // The value already exists
            if (isset($diIdx[$key])) {
                $diffQty = $newval - $diIdx[$key]->qty; //  new value minus existing value
            }
            // It doesn't exist, so the diff is simply what the form has sent
            else
                $diffQty = $newval; 

            // If we've found that there's a difference (pos or neg), record it
            if ($diffQty != 0)
                $diffs[$key] = $diffQty;
        }


        ## 3. Update the DriverInventory

        foreach($diffs as $itemId => $diffAmt) {
            DB::update(
                 "INSERT INTO DriverInventory (fk_Driver, fk_item, item_type, qty) "
                ."VALUES (:driver, :item, 'Dish', greatest(0, qty + :diff)) " 
                .   "ON DUPLICATE KEY UPDATE qty = greatest(0, qty + :diff2)" , 
                array('diff'=>$diffAmt, 'diff2'=>$diffAmt, 'driver'=>$this->id(), 'item'=>$itemId)
            );
        }


        ## 4. Update the LiveInventory

        /*
         * We need to be careful and account for items that are marked as "sold out".
         * This is why we have the IF statement within the SQL below.
         */
        
        foreach($diffs as $itemId => $diffAmt) {
            DB::update(
                 'INSERT INTO LiveInventory (fk_item, item_type, qty, qty_saved) '
                .'VALUES (:item, "Dish", IF(sold_out, 0, greatest(0, qty + :diff)), greatest(0, qty_saved + :diff3)) '
                .   'ON DUPLICATE KEY UPDATE qty = IF(sold_out, 0, greatest(0, qty + :diff2)), qty_saved = greatest(0, qty_saved + :diff4) ' ,
                array('diff'=>$diffAmt, 'diff2'=>$diffAmt, 'diff3'=>$diffAmt, 'diff4'=>$diffAmt, 'item'=>$itemId)
            );
        }
        #});
    }
            
}
