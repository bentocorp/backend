<?php namespace Bento\Model;


class BaseModel extends \Eloquent {
    
    
    /**
     * 
     * @param array $attributes An array of attribute to fill the model with.
     * @param int $pk The primary key of the row.
     */
    public function __construct($attributes = array(), $pk = NULL) 
    {
        // So I can pass a less verbose NULL into the constructor
        if (!is_array($attributes))
            $attributes = array();
        
        // Allow filling (DANGEROUS FOR ALL MODELS!)
        #if (count($attributes > 0))
            #$this->guarded = [];
        
        // Make sure the parent is called
        parent::__construct($attributes);
        
        // Set the pk if the parent constructor hasn't yet
        // Resolves to, for example: $this->pk_Order
        if (!isset($this->{$this->primaryKey}))
            $this->{$this->primaryKey} = $pk;
            
        // Undo empty guards
        $this->guarded = array('*');
    }
    
    
    /*
     * Less verbose than the member name, and can be standard in any model
     */
    protected function id() {
        return $this->{$this->primaryKey};
    }
    
    
    /**
     * A take on //https://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_fill
     * 
     * @param array | obj $attributes
     * @return $this
     */
    public function fillMe($attributes)
    {
        // Allow filling
        $oldGuard = $this->guarded;
        $this->guarded = [];
        
        // Convert
        if (is_object($attributes))
        {
            $ar = [];
            
            foreach ($attributes as $key => $val) {
                $ar[$key] = $val;
            }
            
            $finalAttributes = $ar;
        }
        else
            $finalAttributes = $attributes;
                
        $return = parent::fill($finalAttributes);
        
        // Undo empty guards
        $this->guarded = $oldGuard;
        
        return $return;
    }

    
    
}