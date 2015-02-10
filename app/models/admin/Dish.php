<?php

namespace Bento\Admin\Model;

use DB;


class Dish extends \Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Dish';
    protected $primaryKey = 'pk_Dish';
    protected $guarded = array('pk_Dish');
        
        
    public static function saveChanges($id, $data) {
        
        unset($data['_token']);
        
        #$data['name'] = strtoupper($data['name']);
        
        DB::table('Dish')
                    ->where('pk_Dish', $id)
                    ->update($data);
    }
    
        
}
