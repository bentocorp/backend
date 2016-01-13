<?php namespace Bento\core\Logic;


use Bento\Timestamp\Clock;
use Bento\Model\MealType;


/**
 * The guy who knows everything about the restaurant
 * @singleton
 */
class MaitreD {

    private static $instance = NULL;
    
    
    public static function get()
    {
        if (self::$instance === NULL)
            return new MaitreD();
        else
            return self::instance;
    }
    
    
    /*
     * Get the max order ahead date. Just add 5 days for now.
     */
    public function getMaxOaDate()
    {
        $now = Clock::getLocalCarbon();
        
        return $now->addDays(5)->format('Y-m-d');
    }
    
    
    public function getAvailableMealsLeftToday()
    {
        $mealsAvail = array();
        $now = Clock::getLocalCarbon()->toTimeString();
        
        // Get the meals
        $meals = MealType::where('active', '=', '1')->get();
        
        // See what's available
        foreach ($meals as $meal)
        {
            $now2 = strtotime($now);
            $cutoff = strtotime($meal->oa_cutoff);
            
            if ($now2 <= $cutoff)
                $mealsAvail[$meal->name] = $meal;
        }
        
        return $mealsAvail;
    }
    
    
        
}
