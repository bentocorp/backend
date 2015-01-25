<?php

namespace Bento\Admin\Model;

use Bento\Lib\Lib;
use DB;

class Orders {


    public static function getOpenOrders() {
        
        // Get from db 
        # Open Orders
        $sql = "
            select
                o.pk_Order,
                o.created_at as order_created_at,
                o.street, o.city, o.state, o.zip,
                os.`status`,
                concat(u.firstname, ' ', u.lastname) as user_name,
                u.phone as user_phone,
                concat(d.firstname, ' ', d.lastname) as driver_name,
                d.pk_Driver
            from `Order` o
            left join OrderStatus os on (o.pk_Order = os.fk_Order)
            left join Driver d on (os.fk_Driver = d.pk_Driver)
            left join User u on (o.fk_User = u.pk_User)
            where os.status IN ('Open', 'En Route')
            ORDER BY order_created_at DESC
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function getBentoBoxesByOrder($pk_Order) {
        
        // Get from db           
        $sql = "
            select 
                cbb.*, 
                (select `name` from Dish d where cbb.fk_main = d.pk_Dish) as main_name,
                (select `name` from Dish d where cbb.fk_side1 = d.pk_Dish) as side1_name,
                (select `name` from Dish d where cbb.fk_side2 = d.pk_Dish) as side2_name,
                (select `name` from Dish d where cbb.fk_side3 = d.pk_Dish) as side3_name,
                (select `name` from Dish d where cbb.fk_side4 = d.pk_Dish) as side4_name
            from CustomerBentoBox cbb
            where fk_Order = $pk_Order
        ";
        $rows = DB::select($sql, array());
        
        return $rows;
    }
    
    
    public static function getStatusesForDropdown() {
        
        $enum = Lib::getEnumValuesHash('OrderStatus', 'status');
        
        return $enum;
    }
                
}
