<?php namespace Bento\Model;

use Bento\app\Bento;
use Bento\Admin\Model\Driver;
use DB;


class LiveInventory extends \Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'LiveInventory';
    protected $primaryKey = 'pk_LiveInventory';

    
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
    
    
    /**
     * @deprecated 
     */
    public static function recalculate() {
        
        DB::transaction(function()
        {
            // Lock the Order table, for the moment
            // Issues with doing this: http://stackoverflow.com/questions/12942967/mysql-lock-error-or-bug
            #DB::unprepared('LOCK TABLES `Order` WRITE');
            
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
            
            // Unlock
            #DB::unprepared('UNLOCK TABLES');
        });
        
         Bento::alert(null, 'LiveInventory was recalculated!', '46a77b53-a821-494b-a567-526f37e6e197');
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
