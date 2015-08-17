<?php

namespace Bento\Coupon;

use Bento\Model\Coupon;
use User;

/**
 * A mother class to abstract away the handling of a specific type of coupon, 
 * such as a UserCoupon.
 */

class AppCoupon {
    
    # The resolved coupon
    private $foundCoupon = NULL;
    
    # The pk of the insertion into CouponRedemption
    private $redemptionId;
    
    /**
     * Instantiate the correct coupon type.
     * 
     * @param string $code
     * @return boolean
     */
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
    
        
    public function redeem($fk_Order = NULL) {
        $this->redemptionId = $this->foundCoupon->redeem($fk_Order);
        
        return $this->redemptionId;
    }
    
    
    public function getGiveAmount() {
        return $this->foundCoupon->getGiveAmount();
    }
    
    
    public function getRedemptionId() {
        return $this->redemptionId;
    }
    
    
    public function getCode() {
        return $this->foundCoupon->id();
    }
    
        
}

