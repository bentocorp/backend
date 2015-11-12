<?php namespace Bento\Order;



class Cashier {

    private $orderJsonObj;
    private $pk_PendingOrder;
    private $pk_Order;
    
    private $CustomerBentoBoxList;
    private $AddonList;
    
    
    public function __construct(\stdClass $orderJsonObj, $pk_PendingOrder = NULL, $pk_Order = NULL) 
    {
        $this->orderJsonObj = $orderJsonObj;
        $this->pk_PendingOrder = $pk_PendingOrder;
        $this->pk_Order = $pk_Order;
    }
    
    
    public function getTotalsHash()
    {
        $totals = array();

        // For each OrderItem (it could be a CBB, an AddonList, etc.)
        foreach ($this->orderJsonObj->OrderItems as $orderItem) {
            
            // Make the object
            $classname = "Bento\\Order\\Item\\$orderItem->item_type";
            
            $item = new $classname($orderItem);
            
            // Calculate its totals
            $item->calculateTotals($totals);
        }
        
        return $totals;
    }

    
    public function printOrderItemListForEmail() 
    {
        
    }
    
    
    public function printOrderTotalsForEmail() 
    {
        
    }
    
}
