<?php

namespace Bento\Model;


class CustomerBentoBox extends \Eloquent {


    /**
     * The database table and primary key used by the model.
     *
     * @var string
     */
    protected $table = 'CustomerBentoBox';
    protected $primaryKey = 'pk_CustomerBentoBox';
    
    
    public static function calculateTotalsFromJson($json) {
        
        $totals = array();

        // For each CustomerBentoBox
        foreach ($json->OrderItems as $orderItem) {
            // Now for each thing in the box
            foreach($orderItem->items as $item) {
                // Increment, or init
                $totals[$item->id] = isset($totals[$item->id]) 
                        ? $totals[$item->id] += 1  
                        : 1;
            }
        }
        
        return $totals;
    }
    
}
