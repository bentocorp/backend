<?php

namespace Bento\Model;

use Bento\Model\PendingOrder;
use DB;
use User;
use Illuminate\Database\QueryException;


class LiveInventory extends \Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'LiveInventory';
        protected $primaryKey = 'pk_LiveInventory';
        
        
        /**
         * Attempt to reserve the inventory for this order.
         * 
         * @param array $data
         * @return boolean False if inventory is not there.
         *  Otherwise, the PendingOrder id.
         */
        public static function reserve($data) {
            
            /*
             * The DB is set to an unsigned int, so it cannot become negative.
             * We wrap each deduction in a transaction, and if any of them fail, we know
             * that we don't have enough inventory to complete this order.
             */
            try {
                DB::transaction(function() use ($data)
                {
                    foreach ($data->order as $order) {
                      DB::update("update LiveInventory set qty = qty - ? WHERE fk_item = ?", array($order->qty, $order->id));
                    }
                });
            }
            catch(QueryException $e) {
                return false;
            }
            
            // Everything is good so far. Insert into PendingOrder.
            $user = User::get();
            
            $pendingOrder = new PendingOrder;
            $pendingOrder->fk_User = $user->pk_User;
            $pendingOrder->order_json = json_encode($data);
            $pendingOrder->save();
            
            // Returning the PendingOrder id
            return $pendingOrder->pk_PendingOrder;
        }
}
