<?php

namespace Bento\Coupon;


interface CouponInterface {
    
    public function id();
    
    public function isValidForUser();
    
    public function getGiveAmount();

    public function redeem($fk_Order);    
}

