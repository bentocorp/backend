<?php

namespace Bento\Model;

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
            
            DB::table('OrderStatus')
                    ->where('fk_Order', $pk_Order)
                    ->update($data);
            
        }
}
