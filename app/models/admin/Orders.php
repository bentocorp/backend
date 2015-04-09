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
                o.number, o.street, o.city, o.state, o.zip, o.amount,
                os.`status`,
                concat(u.firstname, ' ', u.lastname) as user_name,
                u.phone as user_phone, u.email as user_email,
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
                d1.`name` as main_name,  d1.label as main_label, 
                d2.`name` as side1_name, d2.label as side1_label, 
                d3.`name` as side2_name, d3.label as side2_label, 
                d4.`name` as side3_name, d4.label as side3_label, 
                d5.`name` as side4_name, d5.label as side4_label
                    #(select `name` from Dish d where cbb.fk_main = d.pk_Dish) as main_name,
                    #(select `name` from Dish d where cbb.fk_side1 = d.pk_Dish) as side1_name,
                    #(select `name` from Dish d where cbb.fk_side2 = d.pk_Dish) as side2_name,
                    #(select `name` from Dish d where cbb.fk_side3 = d.pk_Dish) as side3_name,
                    #(select `name` from Dish d where cbb.fk_side4 = d.pk_Dish) as side4_name
            from CustomerBentoBox cbb
            left join Dish d1 on (cbb.fk_main = d1.pk_Dish)
            left join Dish d2 on (cbb.fk_side1 = d2.pk_Dish)
            left join Dish d3 on (cbb.fk_side2 = d3.pk_Dish)
            left join Dish d4 on (cbb.fk_side3 = d4.pk_Dish)
            left join Dish d5 on (cbb.fk_side4 = d5.pk_Dish)
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
