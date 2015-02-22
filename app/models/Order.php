<?php

namespace Bento\Model;


class Order extends \Eloquent {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Order';
    protected $primaryKey = 'pk_Order';
        
    private $pk_Order = NULL;
    
    public function __construct($attributes = array(), $pk_Order = NULL) 
    {
        if (!is_array($attributes))
            $attributes = array();
        
        parent::__construct($attributes);
        
        if ($this->pk_Order === NULL)
            $this->pk_Order = $pk_Order;
    }
    
    
    private function id() {
        return $this->pk_Order;
    }
    
    
    
    public function getOrderJsonObj() {
        
        return json_decode(PendingOrder::withTrashed()->where('fk_Order', $this->id())->get()[0]->order_json);
    }
    
   
    
        
}
