<?php

namespace Bento\Model;

use Bento\Model\CouponRedemption;
use DB;
use User;


class Coupon extends \Eloquent {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Coupon';
    protected $primaryKey = 'pk_Coupon';
        
    
    public function __construct($attributes = array(), $pk_Coupon = NULL) 
    {
        // So I can pass a less verbose NULL into the constructor
        if (!is_array($attributes))
            $attributes = array();
        
        // Make sure the parent is called
        parent::__construct($attributes);
        
        // Set the pk if the parent constructor hasn't yet
        if (!isset($this->pk_Coupon))
            $this->pk_Coupon = $pk_Coupon;
    }
    
    /*
     * Less verbose than the member name, and can be standard in any model
     */
    private function id() {
        return $this->pk_Coupon;
    }
    
    
    public static function find2($code) {
        
        $coupon = self::where('pk_Coupon', '=' , $code)->where('is_expired', '!=', '1')->get();
        
        if ($coupon->count() == 0)
            return NULL;
        else
            return $coupon[0];
    }
    
        
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
        
        $redemption->save();
    }
    
   
    
        
}
