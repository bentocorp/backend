<?php

namespace Bento\Model;

use User;

class PendingOrder extends \Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'PendingOrder';
        protected $primaryKey = 'pk_PendingOrder';
        
        
        /**
         * Check if the user has a pending order already
         * 
         * @return boolean
         */
        public static function checkUser() {
            
            $pending = self::getUserPendingOrder();
            
            if ($pending === NULL)
                return false;
            else
                return true;
        }
        
        
        public static function getUserPendingOrder() {
            $user = User::get();
            $pending = self::where('fk_User', '=', $user->pk_User)->first();
            
            return $pending;
        }
}
