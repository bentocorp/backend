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
    
    /**
     * Get the order string for the queue (showing in the driver app) and such.
     * @param string $orderString
     */
    public function getOrderString(& $orderStr);
    
    /**
     * Get the total amount of Bentos, Addons, etc.
     * MUST be called AFTER getOrderString()
     */
    public function getTotalQty();
    
    /**
     * Get some helpful text about what this list holds. Like "Bento" or "Add-on".
     * Non-plural
     */
    public function getContentsName();
    
    /**
     * Get some helpful text about what this list holds. Like "Bento" or "Add-on".
     * Non-plural
     */
    public function getContentsNamePlural();
}

