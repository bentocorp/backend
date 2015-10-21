<?php

namespace Bento\Admin\Model;

#use Illuminate\Auth\UserTrait;


class AdminUser extends \Eloquent {
     
    // Static vars
    private static $apiUser;
    
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_User';
    protected $primaryKey = 'pk_admin_User';
    
    
    public static function set($apiUser) {
        self::$apiUser = $apiUser;
    }


    public static function get() {
        return self::$apiUser;
    }
    
    
    public static function getAdminUserByApiToken($api_token) {
        // Get the User
        $sql = 'SELECT * FROM admin_User WHERE api_token = ?';

        $user = self::hydrateRaw($sql, array($api_token));

        if ($user->count() == 1)
            return $user[0];
        else
            return NULL;
    }
            
}
