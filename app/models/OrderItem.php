<?php namespace Bento\Model;


use DB;


class OrderItem extends \Eloquent {
    
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'OrderItem';
    protected $primaryKey = 'pk_OrderItem';
    protected $guarded = array('pk_OrderItem');

    
    public function __construct($attributes = array(), $pk_OrderItem = NULL) 
    {
        // So I can pass a less verbose NULL into the constructor
        if (!is_array($attributes))
            $attributes = array();
        
        // Make sure the parent is called
        parent::__construct($attributes);
        
        // Set the pk if the parent constructor hasn't yet
        if (!isset($this->pk_OrderItem))
            $this->pk_OrderItem = $pk_OrderItem;
    }
    
    
    private function id() {
        return $this->pk_OrderItem;
    }
    

    public static function getItemsByOrder($pk_Order, $type) {
        
        // Get from db           
        $sql = "
            select 
                oi.*, d.*
            from OrderItem oi 
            left join Dish d on (d.pk_Dish = oi.fk_item)
            where fk_Order = ? AND item_type = ?
        ";
        $rows = DB::select($sql, array($pk_Order, $type));
        
        return $rows;
    }    
    
            
}
