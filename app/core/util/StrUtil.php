<?php namespace Bento\core\Util;


class StrUtil {
    
    /**
     * Encode a string for Onfleet / Houston
     * @param string $str
     * @return string Encoded string.
     */
    public static function encodeForHouston($str) {
        
        if ($str === NULL || $str == '')
            return $str;
        
        // "Eggplant" results in "\"Eggplant\""
        $str2 = json_encode($str);
        
        // Remove the outer quotes
        $n = strlen($str2);
        
        return substr($str2, 1, $n-2);
    }
    
}

