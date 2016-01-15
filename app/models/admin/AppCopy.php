<?php namespace Bento\Model;


use DB;


class AppCopy extends \Eloquent {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_ios_copy';
    protected $primaryKey = 'pk_admin_ios_copy';
    
    
    public static function getValue($key)
    {
        $sql = "select `value` from admin_ios_copy where `key` = ?";
        $response = DB::select($sql, array($key))[0];
        
        return $response->value;
    }
    
}
