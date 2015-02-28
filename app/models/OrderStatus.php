<?php

namespace Bento\Model;

use Bento\Drivers\DriverMgr;
use Bento\Admin\Model\Driver;
use Bento\Model\Order;
use DB;

class OrderStatus extends \Eloquent {


	/**
	 * The database table and primary key used by the model.
	 *
	 * @var string
	 */
	protected $table = 'OrderStatus';
        protected $primaryKey = 'pk_OrderStatus';
    
        
        public static function setStatus($pk_Order, $data) {
               
            $from = $data['pk_Driver']['current'];
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
}
