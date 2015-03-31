<?php

namespace Bento\Model;


use Bento\Coupon\CouponInterface;
use Bento\Coupon\CouponTrait;


class Coupon extends \Eloquent implements CouponInterface {

    use CouponTrait;

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
    public function id() {
        return $this->pk_Coupon;
    }
    
                    
    public function getGiveAmount() {
        
        return $this->give_amount;
    }
    
   
    
        
}
