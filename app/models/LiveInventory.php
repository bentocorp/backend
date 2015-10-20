<?php namespace Bento\Model;

use Bento\Model\PendingOrder;
use Bento\Admin\Model\Driver;
use Bento\Model\CustomerBentoBox;
use Bento\core\InternalResponse;
use DB;
use User;
use Illuminate\Database\QueryException;


class LiveInventory extends \Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'LiveInventory';
    protected $primaryKey = 'pk_LiveInventory';


    /**
     * Attempt to reserve the inventory for this order.
     * 
     * @param array $data
     * @return boolean False if inventory is not there.
     *  Otherwise, the PendingOrder id.
     */
    public static function reserve($data) {

        $response = new InternalResponse;
        
        // ## First, detect a duplicate order
        
        // Create a new PendingOrder object
        $pendingOrder = new PendingOrder;
        
        // Get the user for this request
        $user = User::get();

        // Attempt to save the PendingOrder:
        
        $pendingOrder->fk_User = $user->pk_User;
        $pendingOrder->order_json = json_encode($data);
        
        // Try the idempotent token!
        $pendingOrder->idempotent_token = NULL;
        if (isset($data->IdempotentToken))
            $pendingOrder->idempotent_token = $data->IdempotentToken;
        
        try {
            $pendingOrder->save();
        } 
        catch (QueryException $e) {
            #var_dump($e->errorInfo[0]); die();
            if ($e->errorInfo[0] == 23000) { // SQLSTATE: 23000 (ER_DUP_KEY)
                // Return an appropriate status object back to the OrderCtrl
                $response->setSuccess(false);
                $response->setStatusCode(23000);
                
                return $response;
            }
            else
                throw new QueryException;
        }
        
        // ## At this point, we're sure that there isn't a duplicate order,
        // so let's continue trying to process the order.
        
        /* First calculate the totals */
        $totals = CustomerBentoBox::calculateTotalsFromJson($data);

        /* Next, try to reserve the totals
         * 
         * The DB is set to an unsigned int, so it cannot become negative.
         * We wrap each deduction in a transaction, and if any of them fail, we know
         * that we don't have enough inventory to complete this order.
         */
        try {
            DB::transaction(function() use ($totals)
            {
                foreach ($totals as $itemId => $itemQty) {
                  DB::update("update LiveInventory set qty = qty - ? WHERE fk_item = ?", array($itemQty, $itemId));
                }
            });
        }
        catch(QueryException $e) {
            // Hard delete the pending order. We don't need it.
            $pendingOrder->forceDelete();
            
            $response->setSuccess(false);
            $response->setStatusCode(410);

            return $response;
        }
        
        // ## We had enough inventory for this order.

        // ## Everything is good.
                
        // Return the PendingOrder
        $response->setSuccess(true);
        $response->setStatusCode(200);
        $response->bag->pendingOrder = $pendingOrder;
        
        return $response;
    }
    
    
    public static function getItemNames() {
        
        // Get from db           
        $sql = "
        select 
            li.fk_item, li.item_type,
            d.`type`,
            d.`name`, d.short_name, d.label
        from LiveInventory li 
        left join Dish d on (li.fk_item = d.pk_Dish)
        order by type asc, d.`name` asc
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function getLiveAndDriver() {
        
        $sql = "
        # count stuff in the LiveInventory
        SELECT 
            li.pk_LiveInventory, 
            li.fk_item, d.`name`,  
            li.qty as lqty,
            li.item_type,
            li.qty_saved,
            li.sold_out,
            d.label,
            d.type, 
            (select sum(di.qty) from DriverInventory di where di.fk_item = li.fk_item group by fk_item) as dqty
        FROM bento.LiveInventory li
        left join Dish d on (li.fk_item = d.pk_Dish)
        
        order by type asc, name asc
        ";

        /* VJC 26-03-2015: Removing this, because it's causing issues, and is an extreme edge case for the UI
        union
        
        # for Drivers, roll into items that are NOT in the LiveInventory
        SELECT di.fk_item, d.`name`, d.label, li.qty as lqty, d.type, di.item_type,
                sum(di.qty) as dqty
        from DriverInventory di
        left join Dish d on (di.fk_item = d.pk_Dish)
        left join LiveInventory li on (li.fk_item = di.fk_item)
        where li.qty IS NULL
        group by di.fk_item

        order by type asc, name asc
        ";
         */
        
        $rows = self::hydrateRaw($sql, array());
        
        return $rows;
    }
    
    
    public static function recalculate() {
        
        DB::transaction(function()
        {
            $driverInventory = Driver::getAggregateInventory();
            
            // Empty it
            #DB::table('LiveInventory')->truncate(); # The problem with a truncate is that it does an implicit commit of the transaction
            DB::table('LiveInventory')->delete();
            
            // Populate it
            # (GREATEST() ensures we don't go negative)
            foreach ($driverInventory as $row) {
                $sql = "insert into LiveInventory (fk_item, qty, change_reason) values (?, GREATEST(?,0), ?)";
                DB::insert($sql, array($row->fk_item, $row->dqty, 'admin_update'));
            }
        });
    }
    
    
    public static function sellOut($mode, $fk_item) {
        
        // Mark something as sold out
        if ($mode == 'on') {
            
            DB::transaction(function() use ($fk_item)
            {
                // Ensure idempotency
                $row = DB::select('select * from LiveInventory where fk_item = ? FOR UPDATE', array($fk_item))[0];
                
                if ($row->sold_out)
                    return;
                
                DB::update('update LiveInventory set `qty_saved` = `qty`, sold_out = 1 where fk_item = ?', array($fk_item));
                DB::update('update LiveInventory set `qty` = 0 where fk_item = ?', array($fk_item));
            });
        }
        // Resurrect a sold out item
        else if ($mode == 'off') {
            
            DB::transaction(function() use ($fk_item)
            {
                // Ensure idempotency
                $row = DB::select('select * from LiveInventory where fk_item = ? FOR UPDATE', array($fk_item))[0];
                
                if (!$row->sold_out)
                    return;
                
                DB::update('update LiveInventory set `qty` = `qty_saved`, sold_out = 0 where fk_item = ?', array($fk_item));
            });
        }
    }
    
}
