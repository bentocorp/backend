<?php

namespace Bento\core\Util;


class NumUtil {
    
    /**
     * The idea here is to show $7 (not $7.00), but $4.75. So don't display .00
     * @param decimal $amt
     * @return int | float
     */
    public static function formatPriceForEmail($amt)
    {
        $amt2 = $amt * 100;
        
        if ($amt2%100 == 0)
            return (float) $amt;
        else
           return number_format($amt, 2);
    }
    
    
    public static function formatPriceFromCents($cents)
    {
        $dollars = $cents / 100;
        
        return number_format($dollars, 2);
    }
       
}

