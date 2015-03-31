<?php

Use Bento\Model\CouponUserHash;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;



class User extends \Eloquent implements UserInterface, RemindableInterface {

    use UserTrait, RemindableTrait;


    // Static vars

    private static $apiUser;

    // Instance vars

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

        if ($user->count() == 1)
            return $user[0];
        else
            return NULL;
    }


    public static function setNewApiToken($api_token, $email) {

        // Create new api_token
        DB::table('User')
                ->where('email', $email)
                ->update(array('api_token' => $api_token));
    }


    public static function getUserForLogin($email) {

        $sql = "SELECT pk_User, email, phone, api_token, password, is_admin, stripe_customer_obj
                FROM User WHERE email = ? AND email IS NOT NULL 
                    and password IS NOT NULL";

        $user = self::hydrateRaw($sql, array($email));

        return self::parseUserForLogin($user);
    }


    public static function getFbUserForLogin($email) {

        $sql = "SELECT pk_User, email, phone, api_token, fb_token, fb_id, is_admin, stripe_customer_obj
                FROM User WHERE email = ? AND email IS NOT NULL 
                    and fb_id IS NOT NULL and fb_token IS NOT NULL";

        $user = self::hydrateRaw($sql, array($email));

        return self::parseUserForLogin($user);
    }


    private static function parseUserForLogin($user) {

        if ($user->count() == 1) {
            self::$apiUser = $user[0];
            return $user;
        }
        else 
            return NULL;
    }


    public static function logout() {

        $user = self::$apiUser;

        // Delete Token
        $sql = "UPDATE User SET api_token = NULL WHERE api_token = ?";
        DB::update($sql, array($user->api_token));
    }


    public static function exists($email) {

        $existingUser = User::where('email', $email)->get();

        if ($existingUser->count() > 0)
            return true;
        else
            return false;
    }
    
    
    /*************************************************************************
     * Member Functions
     *************************************************************************
     */
    
    public function makeCouponCode() {
        
        // Create the base
        $cleanName = preg_replace('/[^a-zA-Z0-9]+/', '', $this->firstname);
        $baseName = strtolower( substr($cleanName, 0, 10) );
        
        $baseNum = null;
        
        DB::transaction(function() use ($baseName, & $baseNum)
        {
            $row = CouponUserHash::find($baseName);
            
            // It's the first one
            if ($row === NULL) {
                $baseNum = 1;
                
                $couponUserHash = new CouponUserHash;
                $couponUserHash->pk_CouponUserHash = $baseName;
                $couponUserHash->count = $baseNum;
                $couponUserHash->save();
            }
            // There's already something there
            else {
                $baseNum = $row->count + 1;
                
                $row->count = $baseNum;
                $row->save();
            }
        });
        
        $userCouponCode = $baseName.$baseNum;
        
        return $userCouponCode;
    }


    /*********************************************************************
     * Attribute getters and setters
     *********************************************************************/

    public function getStripeCustomerObjAttribute($value) {
        try {
            return unserialize(base64_decode($value));
        }   catch (\Exception $e) {
            return $value;
        }
        #return base64_decode($value);
    }


    public function setStripeCustomerObjAttribute($value) {
        $this->attributes['stripe_customer_obj'] = base64_encode(serialize($value));
    }


    public function getFbTokenAttribute($value) {
        try {
            return Crypt::decrypt($value);
        }   catch (\Exception $e) {
            return $value;
        }
    }


    public function setFbTokenAttribute($value) {
        $this->attributes['fb_token'] = Crypt::encrypt($value);
    }
                
}
