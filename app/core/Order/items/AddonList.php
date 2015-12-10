<?php namespace Bento\Order\Item;



class AddonList implements OrderItemInterface {

    
    private $itemJsonObj;
    
    
    public function __construct(\stdClass $orderItemJsonObj)
    {
        $this->itemJsonObj = $orderItemJsonObj;
    }
    
    
    public function calculateTotals(&$totals)
    {
        // Now for each thing in the box
        foreach($this->itemJsonObj->items as $item) {
            // Increment, or init
            $totals[$item->id] = isset($totals[$item->id]) 
                    ? $totals[$item->id] += $item->qty  
                    : $item->qty;
        }
    }
    
}
