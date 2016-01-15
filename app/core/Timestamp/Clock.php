<?php namespace Bento\Timestamp;


use Carbon\Carbon;


/**
 * The Cashier knows everything about an Order, and can manage all desired actions on it.
 */
class Clock {

    /**
     * Later, we'll use this class to figure out different timezones for different kitchens.
     * 
     * @param string $tzname The standard timezone name that you want the current time for
     * @param string $format The Date format
     * @return date | time | timestamp. Y-m-d by default.
     */
    public static function getLocalTimestamp($tzname = 'America/Los_Angeles', $format = 'Y-m-d') 
    {    
        $date = Carbon::now($tzname)->format($format);
 
        return $date;
    }
    
    public static function getLocalCarbon($tzname = 'America/Los_Angeles') 
    {    
        return Carbon::now($tzname);
    }
    
    
    public static function getTimezone()
    {
        return 'America/Los_Angeles';
    }
    
    
    /**
     * 
     * @param string $date Y-m-d
     * @return boolean
     */
    public static function isTomorrow($date) 
    {
        $tomorrow = Carbon::tomorrow(self::getTimezone())->format('Y-m-d');
        
        if ($tomorrow == $date)
            return true;
        else
            return false;
    }
    
        
}
