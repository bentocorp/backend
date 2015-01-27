<?php

namespace Bento\Model;

use User;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PendingOrder extends \Eloquent {

        use SoftDeletingTrait;
    
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
        
        
        /*
        public static function getUserPendingOrderByPendingId($pk_PendingOrder) {
            $user = User::get();
            $pending = self::
                    where('fk_User', '=', $user->pk_User)
                    ->where('pk_PendingOrder', '=', $pk_PendingOrder);
            
            return $pending;
        }
         * 
         */
}
