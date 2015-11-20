<?php namespace Bento\Order\ItemList;


use Bento\Model\CustomerBentoBox;


class CustomerBentoBoxList implements OrderItemListInterface {

    
    private $pk_Order;
    private $items = array();
    
    
    public function __construct($pk_Order) 
    {
        $this->pk_Order = $pk_Order;
    }
    
    
    public function writeItems() 
    {
        // For each CustomerBentoBox
        foreach ($this->items as $orderItem) {
            
            // Make a CustomerBentoBox
            $box = new CustomerBentoBox;
            $box->fk_Order = $this->pk_Order;
            
            // Now for each thing in the box
            foreach($orderItem->items as $item) {
                $fk = "fk_$item->type";
                $box->{$fk} = $item->id;
            }
            
            // Save the box
            $box->save();
            
            ## ToDo: Insert into OrderItem
        }
    }
    
    
    public function addItem(\stdClass $item)
    {
        $this->items[] = $item;
    }
    
}
