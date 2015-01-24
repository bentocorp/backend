<?php

namespace Bento\Lib;

use DB;

class Lib {
    
    public static function getEnumValues($table, $field) {

       $test = DB::select(DB::raw("show columns from {$table} where field = '{$field}'"));

       preg_match('/^enum\((.*)\)$/', $test[0]->Type, $matches);
       
       foreach( explode(',', $matches[1]) as $value )
       {
           $enum[] = trim( $value, "'" );   
       }

       return $enum;
    }
    
    
    public static function getEnumValuesHash($table, $field) {

       $test = DB::select(DB::raw("show columns from {$table} where field = '{$field}'"));

       preg_match('/^enum\((.*)\)$/', $test[0]->Type, $matches);
       
       foreach( explode(',', $matches[1]) as $value )
       {
           $value2 = trim( $value, "'" );
           $enum[$value2] = $value2;   
       }

       return $enum;
    }
    
}

