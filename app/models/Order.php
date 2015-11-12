<?php namespace Bento\Model;


use Bento\Model\CustomerBentoBox;
use Bento\Order\Cashier;
use DB;


class Order extends \Eloquent {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Order';
    protected $primaryKey = 'pk_Order';
        
    #private $pk_Order = NULL;
    
    public function __construct($attributes = array(), $pk_Order = NULL) 
    {
        // So I can pass a less verbose NULL into the constructor
        if (!is_array($attributes))
            $attributes = array();
        
        // Make sure the parent is called
        parent::__construct($attributes);
        
        // Set the pk if the parent constructor hasn't yet
        if (!isset($this->pk_Order))
            $this->pk_Order = $pk_Order;
    }
    
    /*
     * Less verbose than the member name, and can be standard in any model
     */
    private function id() {
        return $this->pk_Order;
    }
    
    
    public function getOrderJsonObj($pending = false) {
        
        if ($pending)
            $key = 'pk_PendingOrder';
        else
            $key = 'fk_Order';
        
        return json_decode(PendingOrder::withTrashed()->where($key, $this->id())->get()[0]->order_json);
    }
    
    
    public function getDriversDropdown($driversDropdown) 
    {
        #var_dump($driversDropdown); die(); #0
        $possibleDrivers = $this->findPossibleDrivers();
        #var_dump($possibleDrivers); die(); #0
        
        $possibleDriversAr = array();
        
        $possibleDriversAr[0] = ''; // First item in list is blank/unassigned
        
        foreach ($possibleDrivers as $possibleDriver) 
        {
            // Get the driver and add to dropdown group
            // Set the key=fk_Driver, and the value equal to the name. Just like $driversDropdown
            $possibleDriversAr[$possibleDriver->fk_Driver] = $driversDropdown[$possibleDriver->fk_Driver];
            
            // Remove from regular list
            unset($driversDropdown[$possibleDriver->fk_Driver]);
        }
         
        $return = array(
            'Possible Drivers' => $possibleDriversAr,
            'Other Drivers' => $driversDropdown,
        );
        
        return $return;
    }
    
    
    /**
     * Aiming for this query example:
        select * from (
            SELECT fk_Driver, count(*) as `count`
            FROM DriverInventory
            where (fk_item = 1 AND qty >= 1) || (fk_item = 2 AND qty >= 50) 
            group by fk_Driver
        ) t
        where t.count >= $n
     */
    private function findPossibleDrivers() 
    {
        #$totals = CustomerBentoBox::calculateTotalsFromJson($this->getOrderJsonObj());
        $cashier = new Cashier($this->getOrderJsonObj());
        $totals = $cashier->getTotalsHash();
        #var_dump($totals); die(); #0
        
        $n = count($totals);
        
        $where = '';
        
        $i = 0;
        
        foreach ($totals as $id => $qty) {
            $where .= " (fk_item = $id AND qty >= $qty) ";
            if ($i < $n-1)
                $where .= ' || '; // Don't add to the last one
                
            $i++;
        }
        
        $sql = "
        select * from (
                SELECT fk_Driver, count(*) as `count`
                FROM DriverInventory
                where $where 
                group by fk_Driver
        ) t
        where t.count >= $n
        ";
        
        return DB::select($sql);
    }
    
        
    public function rollback($pending = false) 
    {
        // 1. Rollback the LiveInventory
        
        #$totals = CustomerBentoBox::calculateTotalsFromJson($this->getOrderJsonObj($pending));
        $cashier = new Cashier($this->getOrderJsonObj($pending));
        $totals = $cashier->getTotalsHash();
              
        // Add back in
        DB::transaction(function() use ($totals)
        {
            foreach ($totals as $itemId => $itemQty) {
              DB::update("UPDATE LiveInventory SET
                            qty = IF(sold_out, 0, greatest(0, qty + ?)), 
                            qty_saved = greatest(0, qty_saved + ?),
                            change_reason='admin_update' 
                          WHERE fk_item = ?", 
                      array($itemQty, $itemQty, $itemId));
            }
        });
    }
    
   
    
        
}
