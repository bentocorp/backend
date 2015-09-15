<?php namespace Bento\Coupon;


use Bento\Coupon\CouponTrait;


/**
 * A UserCoupon is the user's personal coupon code to give out
 */

class GenericCoupon implements CouponInterface {
    
    use CouponTrait;
    
    # The User who corresponds to this User Coupon Code
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