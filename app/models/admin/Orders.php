<?php

namespace Bento\Admin\Model;

use Bento\core\Util\DbUtil;
use DB;

class Orders {


    public static function getOpenOrders() {
        
        // Get from db 
        # Open Orders
        $sql = "
            select
                o.pk_Order,
                o.created_at as order_created_at,
                o.number, o.street, o.city, o.state, o.zip, o.amount, o.fk_Coupon,
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
    
    
    public static function getStatusesForDropdown() {
        
        $enum = DbUtil::getEnumValuesHash('OrderStatus', 'status');
        
        return $enum;
    }
    
                        
}
