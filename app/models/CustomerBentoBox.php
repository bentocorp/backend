<?php

namespace Bento\Model;

use DB;


class CustomerBentoBox extends \Eloquent {


    /**
     * The database table and primary key used by the model.
     *
     * @var string
     */
    protected $table = 'CustomerBentoBox';
    protected $primaryKey = 'pk_CustomerBentoBox';
    
     
    public static function getBoxesForSurvey($start, $end) {
        
        $sql = "
            select 
               o.pk_Order, cbb.pk_CustomerBentoBox, o.created_at, u.email, 
               d1.`name` as main_name,  d1.pk_Dish as main_id,
               d2.`name` as side1_name, d2.pk_Dish as side1_id,
               d3.`name` as side2_name, d3.pk_Dish as side2_id,
               d4.`name` as side3_name, d4.pk_Dish as side3_id,
               d5.`name` as side4_name,  d5.pk_Dish as side4_id
            from CustomerBentoBox cbb
            left join Dish d1 on (cbb.fk_main = d1.pk_Dish)
            left join Dish d2 on (cbb.fk_side1 = d2.pk_Dish)
            left join Dish d3 on (cbb.fk_side2 = d3.pk_Dish)
            left join Dish d4 on (cbb.fk_side3 = d4.pk_Dish)
            left join Dish d5 on (cbb.fk_side4 = d5.pk_Dish)
            left join `Order` o on (o.pk_Order = cbb.fk_Order)
            left join OrderStatus os on (o.pk_Order = os.fk_Order)
            left join User u on (u.pk_User = o.fk_User)
            where cbb.created_at >= ? AND cbb.created_at <= ?
               AND status != 'Cancelled'
            order by created_at asc
        ";
        
        $rows = DB::select($sql, array($start, $end));
        
        return $rows;
    }
    
}
