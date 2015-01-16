<?php

namespace Bento\Model;

use DB;
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
            
            #UPDATE `bento`.`LiveInventory` SET `qty`='-1' WHERE `pk_LiveInventory`='17';

            /*
             * The DB is set to an unsigned int, so it cannot become negative.
             * We wrap each deduction in a transaction, and if any of them fail, we know
             * that we don't have enough inventory to complete this order.
             */
            try {
                DB::transaction(function()
                {
                    DB::update("update LiveInventory set qty = qty - 1 WHERE fk_item = ?", array(11));
                    DB::update("update LiveInventory set qty = qty - 1 WHERE fk_item = ?", array(9));
                });
            }
            catch(QueryException $e) {
                return false;
            }
        }
}
