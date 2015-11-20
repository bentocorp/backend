<?php namespace Bento\Order\ItemList;



class AddonListList implements OrderItemListInterface {

    
    private $pk_Order;
    private $items = array();
    
    
    public function __construct($pk_Order) 
    {
        $this->pk_Order = $pk_Order;
    }
    
    
    public function writeItems() 
    {
        ## ToDo: Insert into OrderItem
    }
    
    
    public function addItem(\stdClass $item)
    {
        $this->items[] = $item;
    }
    
}
