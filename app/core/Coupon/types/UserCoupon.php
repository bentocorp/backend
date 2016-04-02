<?php namespace Bento\Coupon;


use Bento\Coupon\CouponTrait;
use DB;
use Lang;
use User;


/**
 * A UserCoupon is the user's personal coupon code to give out
 */

class UserCoupon implements CouponInterface {
    
    use CouponTrait;
    
    # The User who corresponds to this User Coupon Code
    private $userRow;
    
    # The invalid reason
    private $invalidReasonString;
    

    public function __construct($userRow) {
        
        $this->userRow = $userRow;
    }
    
    
    public function id() {
        return $this->userRow->coupon_code;
    }
    
        
    public function getGiveAmount() {
        
        return "15.00";
    }
    
    
    /**
     * Determine if a coupon code is valid.
     * 
     * Rules:
     * 1. A user can ALWAYS use their own coupon code, regardless of whether this is their first order or not
     * 2. For all else, the auto-generated user coupon codes are only good for your FIRST order
     * 
     * @return boolean
     */
    public function isValidForUser() {
        
        # The person who is USING the coupon
        $user = User::get();
        
        //2016-02-28: Nope. We're killing this, as per Jason, to avoid double dipping.
        // 1. A user can ALWAYS use their own coupon code, regardless of whether this is their first order or not
        
        // If it's their own coupon, just make sure they haven't already used it
        /*
        if ( $this->id() == $user->coupon_code ) 
        {
            $results = DB::select('select * from CouponRedemption where fk_User = ? AND fk_Coupon = ?', 
                    array( $user->pk_User, $this->id() ));
            #var_dump($results); die(); #0

            if (count($results) == 0)
                return true;
            else {
                $this->invalidReasonString = Lang::get('coupons.already_used_self_coupon');
                return false;
            }
        }
         * 
         */
        // 2. For all else, the auto-generated user coupon codes are only good for your FIRST order
        //else
        //{
            if ($user->has_ordered) {
                $this->invalidReasonString = Lang::get('coupons.not_first_order');
                return false;
            }
            else
                return true;
        //}
    }
    
    
        
}