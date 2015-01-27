<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

#use DB;

class User extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'User';
        protected $primaryKey = 'pk_User';
        

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');
        
        private static $apiUser;
        
        
        public static function set($apiUser) {
            self::$apiUser = $apiUser;
        }

        
        public static function get() {
            return self::$apiUser;
        }
        
        
        public static function getUserByApiToken($api_token) {
            // Get the User
            $sql = 'SELECT * FROM User WHERE api_token = ?';
            #$user = DB::select($sql, array($api_token));
            #$user = self::whereRaw('api_token = ?', array($api_token))->get(); #works
            
            $user = self::hydrateRaw($sql, array($api_token));
            
            #var_dump($user); die();
                  
            return $user;
        }
        
        
        public static function setNewApiToken($api_token, $email) {
            
            // Create new api_token
            DB::table('User')
                    ->where('email', $email)
                    ->update(array('api_token' => $api_token));
        }
        
}
