<?php

namespace Bento\Model;



class CouponRedemption extends \Eloquent {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'CouponRedemption';
    protected $primaryKey = 'pk_CouponRedemption';
        
    private $pk_CouponRedemption = NULL;
    
    public function __construct($attributes = array(), $pk_CouponRedemption = NULL) 
    {
        // So I can pass a less verbose NULL into the constructor
        if (!is_array($attributes))
            $attributes = array();
        
        // Make sure the parent is called
        parent::__construct($attributes);
        
        // Set the pk if the parent constructor hasn't yet
        if ($this->pk_CouponRedemption === NULL)
            $this->pk_CouponRedemption = $pk_CouponRedemption;
    }
    
    /*
     * Less verbose than the member name, and can be standard in any model
     */
    private function id() {
        return $this->pk_CouponRedemption;
    }
    
    
    
        
}
