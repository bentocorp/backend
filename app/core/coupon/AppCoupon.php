<?php

namespace Bento\Coupon;

use Bento\Model\Coupon;
use User;


class AppCoupon {
    
    private $foundCoupon = NULL;
    
    public function find($code) {
             
        // Try to find the Coupon
        $coupon = Coupon::where('pk_Coupon', '=' , $code)->where('is_expired', '!=', '1')->get();
        
        if ($coupon->count() > 0) {
            $this->foundCoupon = $coupon[0];
            $this->foundCoupon->determinedType = 'Coupon';
            
            return true;
        }
        
        // Try to find the UserCoupon
        $userCoupon = User::where('coupon_code', '=' , $code)->get();
        
        if ($userCoupon->count() > 0) {
            $this->foundCoupon = new UserCoupon($userCoupon[0]);
            $this->foundCoupon->determinedType = 'UserCoupon';
            
            return true;
        }
        
        // Otherwise...
        return false;
    }
    
    
    public function isValidForUser() {
        return $this->foundCoupon->isValidForUser();
    }
    
    
    public function getGiveAmount() {
        return $this->foundCoupon->getGiveAmount();
    }
    
    
    public function redeem() {
        $this->foundCoupon->redeem();
    }
    
    
}

