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
        
        DB::table('Dish')
                    ->where('pk_Dish', $id)
                    ->update($data);
    }
    
    
    public static function getDishesByMenuId($menuId) {
        
        $sql = "
        select * from Menu_Item mi
        left join Dish d on (mi.fk_item = d.pk_Dish)
        where fk_Menu = ?
        ";
        
        $results = DB::select($sql, array($menuId));
        
        $keyedResults = array();
        
        foreach ($results as $row) {
            $keyedResults[$row->pk_Dish] = $row;
        }
        
        return $keyedResults;
    }
    
        
}
