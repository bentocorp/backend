<?php namespace Bento\Order\Item;


interface OrderItemInterface {
    
    public function calculateTotals(&$totals);
    
}

