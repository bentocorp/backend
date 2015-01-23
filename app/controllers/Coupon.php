<?php

namespace Bento\Ctrl;


use Response;


class CouponCtrl extends \BaseController {
    
    
    public function getApply($code) {
        
        if ($code == '1121113370998kkk7') 
            return Response::json(array('amountOff' => '12.00'), 200); 
        else
            return Response::json(array('error' => 'Invalid coupon.'), 400); 
    }
    
    
}
