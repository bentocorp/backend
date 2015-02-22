<?php

namespace Bento\Model;

use Bento\Drivers\DriverMgr;
use DB;

class OrderStatus extends \Eloquent {


	/**
	 * The database table and primary key used by the model.
	 *
	 * @var string
	 */
	protected $table = 'OrderStatus';
        protected $primaryKey = 'pk_OrderStatus';
    
        
        public static function saveStatus($pk_Order, $data) {
            
            #var_dump($data); die();
            
            $update = array(
                'fk_Driver' => $data['pk_Driver']['new'],
                'status' => $data['status'],
            );
            
            DB::table('OrderStatus')
                    ->where('fk_Order', $pk_Order)
                    ->update($update);
            
            // Update driver inventories based on this new assignment (if any)
            #Driver::updateInventoryByAssignment($pk_Order, $data);
            DriverMgr::setOrderDriver($data['pk_Driver']['current'], $data['pk_Driver']['new'], $pk_Order);
        }
}
