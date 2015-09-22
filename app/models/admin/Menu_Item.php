<?php

namespace Bento\Admin\Model;

use DB;


class Menu_Item extends \Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Menu_Item';
    protected $primaryKey = 'pk_Menu_Item';
    protected $guarded = array('pk_Menu_Item');

    public static function setDishes($menuId, $dishes) {
        
        // Clear
        DB::delete('delete from Menu_Item where fk_Menu = ?', array($menuId));
        
        // Insert
        foreach ($dishes as $dishPk) {
            DB::insert('insert into Menu_Item (fk_Menu, fk_item) values (?, ?)', array($menuId, $dishPk));
        }
    }
        
        
        
}
