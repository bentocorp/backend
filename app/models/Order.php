<?php

namespace Bento\Model;

use Bento\Model\CustomerBentoBox;
use DB;


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
        $totals = CustomerBentoBox::calculateTotalsFromJson($this->getOrderJsonObj());
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
    
   
    
        
}
