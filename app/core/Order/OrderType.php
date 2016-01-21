<?php namespace Bento\Order;



/**
 * This guy knows everything about an order_type!
 */
class OrderType {

    
    public static function getAbbrNameFromId($id)
    {
        switch ($id) {
            case 1:
                return 'OD';
                #break;
            case 2:
                return 'OA';
                #break;
        }
    }
    
        
}
