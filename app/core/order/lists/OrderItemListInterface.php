<?php namespace Bento\Order\ItemList;


interface OrderItemListInterface {
    
    /**
     * Add an item to the List
     * @param \stdClass $item
     */
    public function addItem(\stdClass $item);
    
    /**
     * Write the items to the database
     */
    public function writeItems();
    
}

