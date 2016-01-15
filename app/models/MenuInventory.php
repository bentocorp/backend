<?php namespace Bento\Model;


use DB;


class MenuInventory extends \Eloquent {
        
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'MenuInventory';
    protected $primaryKey = 'pk_MenuInventory';

    
    public function __construct($attributes = array(), $pk_MenuInventory = NULL) 
    {
        // So I can pass a less verbose NULL into the constructor
        if (!is_array($attributes))
            $attributes = array();
        
        // Make sure the parent is called
        parent::__construct($attributes);
        
        // Set the pk if the parent constructor hasn't yet
        if (!isset($this->pk_MenuInventory))
            $this->pk_MenuInventory = $pk_MenuInventory;
    }
    
    
    private function id() {
        return $this->pk_MenuInventory;
    }
    

    public static function setInventory($pk_Menu, $menuInv)
    {
        DB::transaction(function() use ($pk_Menu, $menuInv)
        {
            // Ensure idempotency
            $curRows = DB::select('select * from MenuInventory where fk_Menu = ? FOR UPDATE', array($pk_Menu));

            // Clear out what's there
            DB::delete('delete from MenuInventory where fk_Menu = ?', array($pk_Menu));
            
            // Insert new stuff
            foreach($menuInv as $id => $qty) 
            {
                $sql = "insert into MenuInventory (fk_Menu, fk_item, qty, change_reason) values (?, ?, ?, ?)";
                DB::insert($sql, array($pk_Menu, $id, $qty, 'admin_update'));
            }
        });
    }
    
    
    public static function getInventory($pk_Menu) 
    {
        $invIdx = array();
        $invs = self::where('fk_Menu', '=', $pk_Menu)->get();
        
        // Index it
        foreach ($invs as $inv) 
        {
            $invIdx[$inv->fk_item] = $inv->qty;
        }
        
        return $invIdx;
    }
    
    
    /*
     * Member Functions:
     */
    
        
    
    
            
}
