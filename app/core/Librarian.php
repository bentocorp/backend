<?php namespace Bento\core;


use Bento\Timestamp\Clock;
use Bento\Model\Order;
use DB;
use Carbon\Carbon;


/**
 * The purpose of the Librarian is to find orders that you're looking for.
 */
class Librarian {

    
    public static function getInProgress($pk_User)
    {
        $sql = "
            (
            # Get In Progress Orders
            select * 
            #select 
                    #o.created_at utc_created_at, o.order_type, os.status, po.order_json
            from `Order` o
            left join OrderStatus os on (os.fk_Order = o.pk_Order)
            left join PendingOrder po on (po.fk_Order = o.pk_Order)
            where os.status NOT IN ('Open', 'Delivered', 'Cancelled')
                    AND o.fk_User = ? AND o.order_type = 1
            order by o.created_at desc
            )
            UNION
            (
            select * 
            #select 
                    #o.created_at utc_created_at, o.order_type, os.status, po.order_json
            from `Order` o
            left join OrderStatus os on (os.fk_Order = o.pk_Order)
            left join PendingOrder po on (po.fk_Order = o.pk_Order)
            where os.status NOT IN ('Open', 'Delivered', 'Cancelled')
                    AND o.fk_User = ? AND o.order_type > 1
            order by o.created_at desc 
            )
        ";
        $data = DB::select($sql, array($pk_User, $pk_User));
        
        $items = array();
        
        foreach ($data as $row) 
        {
            $order = new Order(NULL);
            $order->fillMe($row);
            $item = new \stdClass();
            
            $item->title = self::getTitleForApp($order, 'InProgress');
            $item->price = '$'.$order->amount;
            
            $items[] = $item;
        }

        return $items;
    }
    
    
    public static function getUpcoming($pk_User)
    {
        /*
         * We'll query OD and OA separately, because a UNION returns a random ordering
         * of each subset, thus making it useless here.
         * More: http://stackoverflow.com/questions/8685168/mysql-order-by-with-union-doesnt-seem-to-work
         */
        
        $items = array();
        
        // OD
        $sql1 = "
            # Get Upcoming Orders, OD on top
            select * 
            #select 
                    #o.created_at utc_created_at, o.order_type, os.status, po.order_json
            from `Order` o
            left join OrderStatus os on (os.fk_Order = o.pk_Order)
            left join PendingOrder po on (po.fk_Order = o.pk_Order)
            where os.status IN ('Open')
                    AND o.fk_User = ? AND o.order_type = 1
            order by o.created_at desc
        ";
        $data1 = DB::select($sql1, array($pk_User));
        #var_dump($data); die(); #0
        
        foreach ($data1 as $row1) 
        {
            #var_dump($row); #0
            $order = new Order(NULL);
            $order->fillMe($row1);
            #var_dump($order); #0
            #die(); #0
            $item = new \stdClass();
            
            $item->title = self::getTitleForApp($order, 'Upcoming');
            $item->price = '$'.$order->amount;
            
            $items[] = $item;
        }
        
        
        // OA
        $sql2 = "
            select * 
            #select 
                    #o.created_at utc_created_at, o.order_type, os.status, po.order_json
            from `Order` o
            left join OrderStatus os on (os.fk_Order = o.pk_Order)
            left join PendingOrder po on (po.fk_Order = o.pk_Order)
            where os.status IN ('Open')
                    AND o.fk_User = ? AND o.order_type > 1
            order by o.scheduled_window_start asc
        ";
        $data2 = DB::select($sql2, array($pk_User));
        #var_dump($data); die(); #0
        
        foreach ($data2 as $row2) 
        {
            #var_dump($row); #0
            $order = new Order(NULL);
            $order->fillMe($row2);
            #var_dump($order); #0
            #die(); #0
            $item = new \stdClass();
            
            $item->title = self::getTitleForApp($order, 'Upcoming');
            $item->price = '$'.$order->amount;
            
            $items[] = $item;
        }

        #var_dump($items); die(); #0
        return $items;
    }
    
    
    public static function getCompleted($pk_User)
    {
        $sql = "
            # Get Past Orders
            select o.*, o.created_at order_created_at,
                    IF(o.order_type = 1, o.created_at, o.scheduled_window_start) as sortable_date
            from `Order` o
            left join OrderStatus os on (os.fk_Order = o.pk_Order)
            left join PendingOrder po on (po.fk_Order = o.pk_Order)
            where os.status IN ('Delivered')
                    AND o.fk_User = ?
            order by sortable_date desc
            limit 5
        ";
        $data = DB::select($sql, array($pk_User));

        $items = array();
        
        foreach ($data as $row) 
        {
            $order = new Order(NULL);
            $order->fillMe($row);
            $item = new \stdClass();
            
            $item->title = self::getTitleForApp($order, 'Delivered');
            $item->price = '$'.$order->amount;
            
            $items[] = $item;
        }

        return $items;
    }
    
    
    /*
        Decision Tree:
      
        + IF InProgress
            OD: "Arriving Shortly" or
            OA: "Arriving soon, from 8:00-9:00p"
     
        + IF Upcoming (Scheduled)
            OD: "ASAP - Being prepped!" or
            OA: "{Today from 8:00-9:00p} {Tomorrow from 8:00-9:00p} {In 5 days, Mon Feb 5th, from 8:00-9:00p}" 
     
        + IF Delivered, either:
            OD: "ASAP on {Aug 5th 2016}" or
            OA: "Aug 5th 2016, 8-9p"
     */
    private static function getTitleForApp($row, $type) 
    {
        if ($type == 'InProgress')
        {
            if ($row->order_type == 1)
                return 'Arriving Shortly';
            else if ($row->order_type == 2)
            {
                $window = $row->getScheduledTimeWindowStr();
                
                return "Arriving soon, from $window";
            }
        }
        
        
        if ($type == 'Upcoming')
        {
            // On-Demand
            if ($row->order_type == 1)
            {
                return 'ASAP - Being prepped!';
            }
            // Order Ahead
            else if ($row->order_type == 2)
            {
                // Today
                if (Clock::isToday($row->scheduled_window_start))
                    $when = 'Today';
                // Tomorrow
                else if (Clock::isTomorrow($row->scheduled_window_start))
                    $when = 'Tomorrow';
                // Later
                else {
                    $carbon = Carbon::parse($row->scheduled_window_start, $row->scheduled_timezone);
                    $diff = $carbon->diffForHumans();
                    $date = $carbon->format('D M jS');
                    $when = "In $diff, $date,";
                }
                
                $window = $row->getScheduledTimeWindowStr();
                
                return "$when $window";
            }
        }
        
        
        if ($type == 'Delivered')
        {
            // On-Demand
            if ($row->order_type == 1)
            {
                $when = Carbon::parse($row->order_created_at, 'UTC')->tz(Clock::getTimezone())->format('M jS Y');
                return "$when, ASAP";
            }
            // Order Ahead
            else if ($row->order_type == 2)
            {
                $when = Carbon::parse($row->scheduled_window_start, $row->scheduled_timezone)->format('M jS Y');
                
                $window = $row->getScheduledTimeWindowStr();
                
                return "$when, $window";
            }
        }
    }
     
}
