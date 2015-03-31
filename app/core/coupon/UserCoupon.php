<?php

namespace Bento\Coupon;

use Bento\Coupon\CouponTrait;


class UserCoupon implements CouponInterface {
    
    use CouponTrait;
    
    private $userRow;
    
    
    public function __construct($userRow) {
        
        $this->userRow = $userRow;
    }
    
    
    public function id() {
        return $this->userRow->coupon_code;
    }
    
        
    public function getGiveAmount() {
        
        return "5.00";
    }

    
}