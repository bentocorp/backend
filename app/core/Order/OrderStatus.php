<?php namespace Bento\Order;

use Bento\Drivers\DriverMgr;
use Bento\Admin\Model\Driver;
use Bento\Model\Order;
use Bento\core\Response\InternalResponse;
use DB;

class OrderStatus {

    private $pk_Order;
    private $insertAt = NULL;
    
    
    public function __construct($pk_Order) 
    {
        $this->pk_Order = $pk_Order;
    }

    
    /**
     * @deprecated
     */
    public static function setStatus($data) {

        $pk_Order = $this->pk_Order;
        
        // The currently assigned driver
        $from = $data['pk_Driver']['current'];

        // Intended assignment by the admin
        $to = $data['pk_Driver']['new'];

        $update = array(
            'fk_Driver' => $data['pk_Driver']['new'],
            'status' => $data['status'],
        );

        DB::table('OrderStatus')
                ->where('fk_Order', $pk_Order)
                ->update($update);

        // Update driver inventories based on this new assignment (if any)
        DriverMgr::setOrderDriver($from, $to, $pk_Order);

        // Undo the count if the order is cancelled
        if ($data['status'] == 'Cancelled') {

            $order = new Order(null, $pk_Order);
            $order->rollback();

            // If the prior selection wasn't blank, add it back in
            if ($to > 0) {
                $toDriver = new Driver(null, $to);
                $toDriver->addOrderToInventory($pk_Order);
            }
        }
    }
    
    
    public function setDriver($data, $insertAt = NULL) {
        
        $pk_Order = $this->pk_Order;
        $this->insertAt = $insertAt;
        
        DB::transaction(function() use ($pk_Order, $data)
        {
            // Lock for read the order status
            $row = DB::select('select * from OrderStatus where fk_Order = ? FOR UPDATE', array($pk_Order))[0];
            
            // If the order is cancelled, exit
            if ($row->status == 'Cancelled')
                return;
            
            // From: The current driver
            $from = $row->fk_Driver <= 0 ? NULL : $row->fk_Driver; // (treat <= 0 as NULL, just in case)

            // To: Intended assignment by the admin
            // (treat 0 as NULL)
            $to = $data['pk_Driver']['new'] <= 0 ? NULL : $data['pk_Driver']['new'];
            
            # 1. Even if current driver doesn't differ from intended driver, set the order_queue properly.
            // Even though the from/to might be the same, the admin might be re-ordering
            // an existing assignment in the driver's queue, so we need to let this processing happen.
        
            if ($from == $to) 
            {
                // From null to null doesn't need to do anything (and at this point, we know that they're equal)
                // A null insertAt index does nothing
                if ($to !== NULL && $this->insertAt !== NULL) {
                    #$toDriver = new Driver(null, $to);
                    $toDriver = Driver::find($to);
                    $toDriver->addOrderToQueue($pk_Order, $this->insertAt);
                }
                
                // If current driver doesn't differ from intended driver,
                // then there's nothing left to do with OrderStatus
                return;
            }
            
            # 2. Adjust the Driver assignment, if necessary
            
            // Current driver differs from intended driver; Order isn't cancelled;
            // Therefore, execute:
            
            // Set the status to Assigned if we're assigning to a driver,
            // or back to Open if we're unassigning
            $status = 'Assigned';
            if ($to === NULL)
                $status = 'Open';
            
            $update = array(
                'fk_Driver' => $to,
                'status' => $status
            );

            DB::table('OrderStatus')
                    ->where('fk_Order', $pk_Order)
                    ->update($update);

            // IF on-demand, Update driver inventories based on this new assignment (if any)
            $order = Order::find($pk_Order);
            if ($order->order_type == 1) {
                $this->updateDriverInventories($from, $to);
            }
        });
    }
    
    /**
     * Update their inventories accordingly
     * 
     * @param pk_Driver $from
     * @param pk_Driver $to
     * @param int $pk_Order
     */
    private function updateDriverInventories($from, $to) {
        
        $pk_Order = $this->pk_Order;
        
        /*
         * This is handled above by setDriver()
        // No Change
        if ($from == $to)
            return;
        
        // Something has changed
         */
        
        // If the prior selection wasn't blank, add it back in,
        // and remove from order_queue
        if ($from != NULL) 
        {
            $fromDriver = Driver::find($from);
            $fromDriver->addOrderToInventory($pk_Order, false); // Already in a transaction
            $fromDriver->removeOrderFromQueue($pk_Order);
        }
        
        // If the new selection isn't blank, subtract it,
        // and add to order_queue
        if ($to != NULL) 
        {
            $toDriver = Driver::find($to);
            $toDriver->subtractOrderFromInventory($pk_Order, false); // Already in a transaction
            
            if ($this->insertAt !== NULL) 
                $toDriver->addOrderToQueue($pk_Order, $this->insertAt);
        }
            
    }
    
    
    public function cancel() {
        
        $internalResponse = new InternalResponse();
        
        DB::transaction(function() use ($internalResponse)
        {
            $pk_Order = $this->pk_Order;

            // Lock for read the current status
            $row = DB::select('select * from OrderStatus where fk_Order = ? FOR UPDATE', array($pk_Order))[0];

            // If the current status is already Cancelled, return ok and exit
            if ($row->status == 'Cancelled')
            {             
                $internalResponse->setStatusCode(200);
                $internalResponse->setSuccess(true);
                $internalResponse->setPubMsg('This order was already cancelled.');
                
                return;
            }
            
            // If the current status is already Delivered, return error and exit
            if ($row->status == 'Delivered')
            {                
                $internalResponse->setStatusCode(400);
                $internalResponse->setSuccess(false);
                $internalResponse->setPubMsg('This order cannot be cancelled, as it has already been delivered.');
                
                return;
            }
            
            
            // The status hasn't already been set to this, or isn't final, so execute
                
            // Update the table
            $update = array(
                'status' => 'Cancelled',
                'fk_Driver' => NULL
            );

            DB::table('OrderStatus')
                    ->where('fk_Order', $pk_Order)
                    ->update($update);

            // Rollback the LI
            $order = new Order(null, $pk_Order);
            $order->rollback();

            // Rollback the DI
            // If the prior selection wasn't blank, add it back in
            if ($row->fk_Driver != 0 && $row->fk_Driver != NULL) 
            {
                $driver = Driver::find($row->fk_Driver);
                $driver->addOrderToInventory($pk_Order);
                $driver->removeOrderFromQueue($pk_Order);
            }
            
            // And finally, Success
            $internalResponse->setStatusCode(200);
            $internalResponse->setSuccess(true);
            $internalResponse->setPubMsg('The order has been cancelled.');

            return;
        });
        
        return $internalResponse;
    }
    
    
}
