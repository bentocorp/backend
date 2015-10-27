<?php namespace Bento\Drivers;

/**
 * 
    for example:
    http://localhost:9000/api/order/assign?orderId=123&driverId=8&insertAt=567

    marc [10:03 PM]
    is: assign order 123 to driver 8 before order 567 in his queue

    marc [10:03 PM]
    if driverId <= 0; it means unassign the order from it’s current driver

    marc [10:04 PM]
    if insertAt <=0, it means assign the order to the driver at the end of his queue
 
    --- 

    marc [3:21 PM]
    FYI–afterId is the ID of the next order after insertion

    marc [3:21 PM]
    so if the queue is g-1,g-2,g-3

    marc [3:22 PM]
    Then `/order/assign/g-100/8/g-2` should result in

    marc [3:23 PM]
    g-1,g-100,g-2,g-3

    marc [3:23 PM]
    which is how most Array#insertAt implementations work
 */
class DriverQueue {

    
    private $queueStr;
    
    private $insertAt;
    private $insertIdx = NULL;
    
    private $pk_Order;
    private $pk_OrderIdx = NULL;
    
    private $queueAr;
    
    
    public function __construct($queueStr) 
    {
        #$this->insertAt = $insertAt;
        $this->queueStr = $queueStr;
        #die($this->queueStr);
        #$this->pk_Order = $globalTaskId;
    }
    
    
    public function addOrder($pk_Order, $insertAt)
    {   
        $this->pk_Order = 'o-'.$pk_Order;
        $this->insertAt = $insertAt;

        // Explode the string, and find the insert and task indices 
        $this->buildAndSearchArray();
        
        // Remove it (make sure you don't set an order into their queue twice)
        $this->removeExistingId();
        
        // If insertAt is 0, or DB cell is currently empty, append to the end
        #var_dump( is_numeric($this->insertAt) && $this->insertAt <= 0 );
        #die( is_numeric($this->insertAt) && $this->insertAt <= 0);
        if ( (is_numeric($this->insertAt) && $this->insertAt <= 0) || $this->isQueueEmpty())
            $this->push();
        // Otherwise, insert where we've been asked to
        else
            $this->insertAt();
        
        return $this->getString();
    }
    
    
    public function removeOrder($pk_Order)
    {
        // If it's already empty, we're done
        if ($this->isQueueEmpty())
            return;
        
        $this->pk_Order = 'o-'.$pk_Order;
        $this->removeExistingId();
        
        return $this->getString();
    }
    
    
    private function isQueueEmpty()
    {
        if ($this->queueStr == '' || $this->queueStr === NULL)
            return true;
        else
            return false;
    }
    
    
    private function buildAndSearchArray()
    {
        // If empty, we're done
        if ($this->isQueueEmpty())
            return;
        
        $this->queueAr = explode(',' , $this->queueStr);
        
        // Where's the stuff we want?
        foreach($this->queueAr as $key => $val) {
            if ($val == $this->insertAt)
                $this->insertIdx = $key;
            
            if ($val == $this->pk_Order)
                $this->pk_OrderIdx = $key;
        }
    }
    
    
    private function removeExistingId()
    {
        // Remove if it's there
        if ($this->pk_OrderIdx !== NULL)
        {
            array_splice($this->queueAr, $this->pk_OrderIdx, 1); // Delete and shift
            
            // And we need to find the insertAt again, if it's there
            if ($this->insertIdx !== NULL) 
            {
                foreach($this->queueAr as $key => $val) {
                    if ($val == $this->insertAt)
                        $this->insertIdx = $key;
                }
            }
        }
    }
    
    
    private function push()
    {
        $this->queueAr[] = $this->pk_Order;
    }
    
    
    private function insertAt() 
    {
        array_splice($this->queueAr, $this->insertIdx, 0, $this->pk_Order);
    }
    
    
    private function getString() 
    {
        if ($this->queueAr === NULL || '')
            return NULL;
        else
            return implode(',' , $this->queueAr);
    }
    
    /*
    $orderQueueAr = array();
    $newOrderQueue = null;
    
    // If 0, or currently empty, append to the end
    if ($this->order_queue !== NULL)
    {
        $orderQueueAr = explode(',' , $this->order_queue);
    }
    else
        $orderQueueAr = explode(',' , $this->order_queue);

    $orderQueueAr[] = $pk_Order;

    $newOrderQueue = implode(',' , $orderQueueAr);
     * *
     */
    
}
