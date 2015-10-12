<?php namespace Bento\core;

use Bento\Drivers\DriverMgr;
use Bento\Admin\Model\Driver;
use Bento\Model\Order;
use DB;

class OrderStatus {

    private $pk_Order;
    
    
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
    
    
    public function setDriver($data) {
        
        $pk_Order = $this->pk_Order;
        
        DB::transaction(function() use ($pk_Order, $data)
        {
            // Lock for read the order status
            $row = DB::select('select * from OrderStatus where fk_Order = ? FOR UPDATE', array($pk_Order))[0];
            $from = $row->fk_Driver == 0 ? NULL : $row->fk_Driver; // (treat 0 as NULL, just in case)

            // Intended assignment by the admin
            // (treat 0 as NULL)
            $to = $data['pk_Driver']['new'] == 0 ? NULL : $data['pk_Driver']['new'];
            
            // If current driver doesn't differ from intended driver, exit
            if ($from == $to)
                return;
            
            // If the order is cancelled, exit
            if ($row->status == 'Cancelled')
                return;

            // Current driver differs from intended driver; Order isn't cancelled;
            // execute
            
            $update = array(
                'fk_Driver' => $to,
            );

            DB::table('OrderStatus')
                    ->where('fk_Order', $pk_Order)
                    ->update($update);

            // Update driver inventories based on this new assignment (if any)
            $this->setOrderDriver($from, $to);
        });
    }
    
    /**
     * Change the assigned driver of this order
     * 
     * @param pk_Driver $from
     * @param pk_Driver $to
     * @param int $pk_Order
     */
    private function setOrderDriver($from, $to) {
        
        $pk_Order = $this->pk_Order;
        
        /*
         * This is handled above by setDriver()
        // No Change
        if ($from == $to)
            return;
        
        // Something has changed
         */
        
        // If the prior selection wasn't blank, add it back in
        if ($from != NULL) {
            $fromDriver = new Driver(null, $from);
            $fromDriver->addOrderToInventory($pk_Order, false); // Already in a transaction
        }
        
        // If the new selection isn't blank, subtract it
        if ($to != NULL) {
            $toDriver = new Driver(null, $to);
            $toDriver->subtractOrderFromInventory($pk_Order, false); // Already in a transaction
        }
            
    }
    
    
    public function cancel() {
        
        DB::transaction(function() 
        {
            $pk_Order = $this->pk_Order;

            // Lock for read the current status
            $row = DB::select('select * from OrderStatus where fk_Order = ? FOR UPDATE', array($pk_Order))[0];

            // If the current status is already cancelled, exit
            if ($row->status == 'Cancelled')
                return;
            
            // The status hasn't already been set to this, execute
                
            // Update the table
            $update = array(
                'status' => 'Cancelled'
            );

            DB::table('OrderStatus')
                    ->where('fk_Order', $pk_Order)
                    ->update($update);

            // Rollback the LI
            $order = new Order(null, $pk_Order);
            $order->rollback();

            // Rollback the DI
            // If the prior selection wasn't blank, add it back in
            if ($row->fk_Driver != 0 && $row->fk_Driver != NULL) {
                $driver = new Driver(null, $row->fk_Driver);
                $driver->addOrderToInventory($pk_Order);
            }
            
        });
    }
    
}
