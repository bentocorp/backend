<?php namespace Bento\core\Stats;


use Bento\Timestamp\Clock;
use DB;


class Stats {

    public static function getTodaysStats()
    {
        $result = array();
        
        # Lunch
        $result['lunch'] = self::getTodayLunch();
        
        # Dinner
        $result['dinner'] = self::getTodayDinner();
        
        return $result;
    }
   
    
    public static function getTodayLunch()
    {
        $todaysDate = Clock::getLocalTimestamp();
        
        $sql = "
        # Lunch v2: Count items by day
        SELECT item_type, sum(qty) as TotalSold
        FROM bento.OrderItem ois
        left join OrderStatus os on (ois.fk_Order = os.fk_Order) 
        left join `Order` o on (ois.fk_Order = o.pk_Order) 
        where 
            # Convert from local timestamp to UTC, since that's what the DB and servers store time in
            # (local time, local timezone, timezone to convert to)
            ois.created_at >= CONVERT_TZ('$todaysDate 00:00:00','America/Los_Angeles','UTC') AND 
            ois.created_at <= CONVERT_TZ('$todaysDate 15:59:59','America/Los_Angeles','UTC') 
            AND os.status != 'Cancelled' # And don't count cancelled orders
            AND o.order_type = 1
        group by item_type
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function getTodayDinner()
    {
        $todaysDate = Clock::getLocalTimestamp();
        
        $sql = "
        # Lunch v2: Count items by day
        SELECT item_type, sum(qty) as TotalSold
        FROM bento.OrderItem ois
        left join OrderStatus os on (ois.fk_Order = os.fk_Order) 
        left join `Order` o on (ois.fk_Order = o.pk_Order) 
        where 
            # Convert from local timestamp to UTC, since that's what the DB and servers store time in
            # (local time, local timezone, timezone to convert to)
            ois.created_at >= CONVERT_TZ('$todaysDate 16:00:00','America/Los_Angeles','UTC') AND 
            ois.created_at <= CONVERT_TZ('$todaysDate 23:59:59','America/Los_Angeles','UTC') 
            AND os.status != 'Cancelled' # And don't count cancelled orders
            AND o.order_type = 1
        group by item_type
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }

}
