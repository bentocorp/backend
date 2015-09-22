<?php

namespace Bento\core\Util;

use DB;

class DbUtil {
    
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
    
    
    /**
     * Make an index from a result array with some primary key field as the array key
     * @param array $results
     * @param string $keyName The name of the DB field to use as the new array key
     * @return array
     */
    public static function makeIndexFromResults(array $results, $keyName) {
        
        // We need to index the return, since it's just a dumb array
        $idx = array();
        foreach ($results as $row) {
        $idx[$row->{$keyName}] = $row; # ToDo: We are going to need a UUID system when we have multiple item types
        }
        
        return $idx;
    }
    
}

