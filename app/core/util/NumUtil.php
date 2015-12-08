<?php

namespace Bento\core\Util;


class NumUtil {
    
    public static function formatPriceForEmail($amt)
    {
        $amt2 = $amt * 100;
        
        if ($amt2%100 == 0)
            return (float) $amt;
        else
           return number_format($amt, 2);
    }
       
}

