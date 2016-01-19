<?php namespace Bento\core;


use DB;


/**
 * The purpose of the Librarian is to find orders that you're looking for.
 */
class Librarian {

    
    public static function getInProgress($pk_User)
    {
        $sql = "
            # Get In Progress Orders
            select 
                    o.created_at utc_created_at, o.order_type, os.status, po.order_json
            from `Order` o
            left join OrderStatus os on (os.fk_Order = o.pk_Order)
            left join PendingOrder po on (po.fk_Order = o.pk_Order)
            where os.status NOT IN ('Open', 'Delivered', 'Cancelled')
                    AND o.fk_User = ?
            order by o.created_at desc
        ";
        $data = DB::select($sql, array($pk_User));

        return $data;
    }
    
    
    public static function getUpcoming($pk_User)
    {
        $sql = "
            # Get Upcoming Orders
            #select * 
            select 
                    o.created_at utc_created_at, o.order_type, os.status, po.order_json
            from `Order` o
            left join OrderStatus os on (os.fk_Order = o.pk_Order)
            left join PendingOrder po on (po.fk_Order = o.pk_Order)
            where os.status IN ('Open')
                    AND o.fk_User = ?
            order by o.created_at desc
        ";
        $data = DB::select($sql, array($pk_User));

        return $data;
    }
    
    
    public static function getCompleted($pk_User)
    {
        $sql = "
            # Get Past Orders
            #select * 
            select 
                    o.created_at utc_created_at, o.order_type, os.status, po.order_json
            from `Order` o
            left join OrderStatus os on (os.fk_Order = o.pk_Order)
            left join PendingOrder po on (po.fk_Order = o.pk_Order)
            where os.status IN ('Delivered')
                    AND o.fk_User = ?
            order by o.created_at desc
            limit 5
            ;
        ";
        $data = DB::select($sql, array($pk_User));

        return $data;
    }
     
}
