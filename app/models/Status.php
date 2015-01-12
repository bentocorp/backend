<?php

namespace Bento\Model;

use DB;

class Status {


    public static function overall() {
        
        $sql = "select `value` from hash where `key` = 'status'";
        $response = DB::select($sql, array())[0];
        
        return $response;
    }
    
    
    public static function menu() {
        
        $sql = "select fk_item itemId, qty from LiveInventory";
        $response = DB::select($sql, array());
        
        return $response;
    }
    
}
