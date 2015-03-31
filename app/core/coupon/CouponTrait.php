<?php

namespace Bento\Coupon;

use Bento\Model\CouponRedemption;
use DB;
use User;


trait CouponTrait {
    
    /**
     * Determine if a coupon code is valid. Right now we are just doing something very simple.
     * No give/get coupons currently.
     * 
     * @return boolean
     */
    public function isValidForUser() {
        
        $user = User::get();
        
        $results = DB::select('select * from CouponRedemption where fk_User = ? AND fk_Coupon = ?', 
                array( $user->pk_User, $this->id() ));
        #var_dump($results); die(); #0
        
        if (count($results) == 0)
            return true;
        else
            return false;
    }
    
    
    public function redeem() {
        
        $user = User::get();
        
        $redemption = new CouponRedemption;
        
        $redemption->fk_Coupon = $this->id();
        $redemption->fk_User = $user->pk_User;
        $redemption->type = $this->determinedType;
        
        $redemption->save();
    }
    
}