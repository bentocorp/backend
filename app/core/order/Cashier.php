<?php namespace Bento\Order;


class Cashier {

    private $orderJsonObj = NULL;
    private $pk_PendingOrder;
    private $pk_Order;
    
    private $lists = NULL;
    private $isListsInit = false;
    private $CustomerBentoBoxList;
    private $AddonList;
    
    
    public function __construct(\stdClass $orderJsonObj, $pk_PendingOrder = NULL, $pk_Order = NULL) 
    {
        $this->orderJsonObj = $orderJsonObj;
        $this->pk_PendingOrder = $pk_PendingOrder;
        $this->pk_Order = $pk_Order;
    }
    
    
    /**
     * Build the objects, and put them into their corresponding lists
     * @return none
     */
    private function listInit()
    {
        // Base case
        if ($this->isListsInit)
            return;
        
        $this->isListsInit = true;
        
        // Put the objects into the lists
        foreach ($this->orderJsonObj->OrderItems as $orderItem)
        {
            // If list doesn't exist, make the list
            if (!isset($this->AddonList[$orderItem->item_type])) {
                $classname = "Bento\\Order\\ItemList\\$orderItem->item_type";
                $this->AddonList[$orderItem->item_type] = new $classname($this->pk_Order);
            }
            
            // Add the item
            $this->AddonList[$orderItem->item_type]->addItem($orderItem);
        }
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
    
    
    public function writeItems() 
    {
        $this->listInit();
        
        foreach ($this->lists as $list) {
            $list->writeItems();
        }
    }

    
    public function printOrderItemListForEmail() 
    {
        
    }
    
    
    public function printOrderTotalsForEmail() 
    {
        
    }
    
}
