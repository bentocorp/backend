<?php

namespace Bento\Coupon;

use Bento\Model\CouponRedemption;
use User;


trait CouponTrait {
    
    
    /**
     * Record a CouponRedemption, and return the pk.
     * 
     * @return int
     */
    public function redeem($fk_Order) 
    {    
        $user = User::get();
        
        $redemption = new CouponRedemption;
        
        $redemption->fk_Coupon = $this->id();
        $redemption->fk_User = $user->pk_User;
        $redemption->type = $this->determinedType;
        $redemption->fk_Order = $fk_Order;
        
        $redemption->save(); // Save to DB
                
        return $redemption->pk_CouponRedemption;
    }
    
    
    public function getInvalidReasonString() 
    {
        return $this->invalidReasonString;
    }
    
}