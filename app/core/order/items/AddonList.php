<?php namespace Bento\Order\Item;



class AddonList implements OrderItemInterface {

    
    private $item;
    
    
    public function __construct($orderItem)
    {
        $this->item = $orderItem;
    }
    
    
    public function calculateTotals(&$totals)
    {
        // Now for each thing in the box
        foreach($this->item->items as $item) {
            // Increment, or init
            $totals[$item->id] = isset($totals[$item->id]) 
                    ? $totals[$item->id] += $item->qty  
                    : $item->qty;
        }
    }
    
}
