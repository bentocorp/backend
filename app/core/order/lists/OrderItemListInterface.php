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
    
    /**
     * Output the html chunks for email
     */
    public function printEmailReceipt();
    
    /**
     * A function to warm up an array by getting the items from the DB.
     * ToDo: This can be a global library for faster lookup.
     */
    # private function itemsInit();
    
}

