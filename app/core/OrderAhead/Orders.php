<?php namespace Bento\core\OrderAhead;


use DB;


/**
 * OrderAhead Orders (o.order_type = 2)
 */
class Orders {


    public static function getLunchOrders($date)
    {
        #var_dump($date); die(); #0;
        $sql = "
            select
                o.*, po.*
            from `Order` o
            left join OrderStatus os on (o.pk_Order = os.fk_Order)
            left join PendingOrder po on (o.pk_Order = po.fk_Order)
            WHERE os.status != 'Cancelled'
                # Convert from local timestamp to UTC, since that's what the DB and servers store time in
                # (local time, local timezone, timezone to convert to)
                AND o.utc_scheduled_window_start >= CONVERT_TZ('$date 00:00:00','America/Los_Angeles','UTC')  
                AND o.utc_scheduled_window_start <= CONVERT_TZ('$date 15:59:59','America/Los_Angeles','UTC') 
                AND o.order_type = 2
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function getDinnerOrders($date)
    {
        $sql = "
            select
                o.*, po.*
            from `Order` o
            left join OrderStatus os on (o.pk_Order = os.fk_Order)
            left join PendingOrder po on (o.pk_Order = po.fk_Order)
            WHERE os.status != 'Cancelled'
                # Convert from local timestamp to UTC, since that's what the DB and servers store time in
                # (local time, local timezone, timezone to convert to)
                AND o.utc_scheduled_window_start >= CONVERT_TZ('$date 16:00:00','America/Los_Angeles','UTC')  
                AND o.utc_scheduled_window_start <= CONVERT_TZ('$date 23:59:59','America/Los_Angeles','UTC') 
                AND o.order_type = 2
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
                        
}
