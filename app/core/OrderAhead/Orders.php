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
    
    
    /**
     * Get all days, inclusive of $date, with upcoming OA orders
     * @param date $date
     */
    public static function getFutureGroupList($date)
    {
        $sql = "
        select 
            date_format(o.scheduled_window_start, '%Y-%m-%d') `orderDate`
            ,date_format(o.scheduled_window_start, '%W %M %D, %Y') `niceOrderDate`
            ,count(*) order_qty
            ,sum((select count(*) from CustomerBentoBox cbb where cbb.fk_Order = o.pk_Order)) `bentoCount`
        from `Order` o
        left join OrderStatus os on (o.pk_Order = os.fk_Order)
        WHERE 
            o.scheduled_window_start >= ?
            AND os.status != 'Cancelled'
        group by `orderDate`
        order by `orderDate` asc
        ";
        $rows = DB::select($sql, array($date));
        
        return $rows;
    }
    
    
    /**
     * 
     * @param date $date
     * @return Array of row objects
     */
    public static function getOrdersByDay($date, $statusClause)
    {
        # All OA orders
        $sql = "
            select
                o.*,
                o.created_at as order_created_at,
                o.scheduled_window_start, o.scheduled_window_end, o.scheduled_timezone,
                os.updated_at as order_updated_at,
                os.`status`, os.trak_status,
                concat(u.firstname, ' ', u.lastname) as user_name,
                u.phone as user_phone, u.email as user_email,
                concat(d.firstname, ' ', d.lastname) as driver_name,
                u.is_top_customer,
                d.pk_Driver
            from `Order` o
            left join OrderStatus os on (o.pk_Order = os.fk_Order)
            left join Driver d on (os.fk_Driver = d.pk_Driver)
            left join User u on (o.fk_User = u.pk_User)
            #WHERE os.status IN ('Delivered', 'Cancelled')
            WHERE
                # Convert from local timestamp to UTC, since that's what the DB and servers store time in
                # (local time, local timezone, timezone to convert to)
                    o.scheduled_window_start >= '$date 00:00:00'
                AND o.scheduled_window_start <= '$date 23:59:59'
                AND o.order_type = 2
                $statusClause
            ORDER BY o.scheduled_window_start ASC
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
        
    public static function getMonetizedOrdersByDay($date)
    {
        return self::getOrdersByDay($date, " AND os.status != 'Cancelled' ");
    }
    
    public static function getCancelledOrdersByDay($date)
    {
        return self::getOrdersByDay($date, " AND os.status = 'Cancelled' ");
    }
    
                        
}
