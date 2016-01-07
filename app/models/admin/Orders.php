<?php

namespace Bento\Admin\Model;

use Bento\core\Util\DbUtil;
use Bento\Timestamp\Clock;
use DB;

class Orders {


    public static function getOpenOrders() {
        
        // Get from db 
        # Open Orders
        $sql = "
            select
                o.*,
                o.created_at as order_created_at,
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
            where os.status NOT IN ('Delivered', 'Cancelled')
            ORDER BY order_created_at DESC
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function getTodaysOrders()
    {
        $todaysDate = Clock::getLocalTimestamp();
                
        # Delivered and Cancelled
        $sql = "
            select
                o.*,
                o.created_at as order_created_at,
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
            WHERE os.status IN ('Delivered', 'Cancelled')
                # Convert from local timestamp to UTC, since that's what the DB and servers store time in
                # (local time, local timezone, timezone to convert to)
                AND o.created_at >= CONVERT_TZ('$todaysDate 00:00:00','America/Los_Angeles','UTC')  
                AND o.created_at <= CONVERT_TZ('$todaysDate 23:59:59','America/Los_Angeles','UTC') 
            ORDER BY order_created_at DESC
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function getStatusesForDropdown() {
        
        $enum = DbUtil::getEnumValuesHash('OrderStatus', 'status');
        
        return $enum;
    }
    
                        
}
