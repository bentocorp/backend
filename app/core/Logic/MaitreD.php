<?php namespace Bento\core\Logic;


use Bento\Timestamp\Clock;
use Bento\Model\MealType;


/**
 * The guy who knows everything about the restaurant
 * @singleton
 */
class MaitreD {

    private static $instance = NULL;
    
    private $nowCarbon = NULL;
    
    public static function get()
    {
        if (self::$instance === NULL)
            return new MaitreD();
        else
            return self::$instance;
    }
    
    
    public function __construct()
    {
        $this->nowCarbon = Clock::getLocalCarbon();
    }
    
    
    /*
     * Get the max order ahead date. Just add 5 days for now.
     */
    public function getMaxOaDate()
    {
        $now = $this->nowCarbon;
        
        return $now->addDays(5)->format('Y-m-d');
    }
    
    
    public function getAvailableMealsLeftToday()
    {
        $mealsAvail = array();
        $now = $this->nowCarbon->toTimeString();
        
        // Get the meals
        $meals = MealType::getList();
        #var_dump($meals); die(); #0
        
        // See what's available
        foreach ($meals->ordering as $mealId)
        {
            $meal = $meals->hash->$mealId;
            
            $now2 = strtotime($now);
            $cutoff = strtotime($meal->oa_cutoff);
            #var_dump($now2); #0
            #var_dump($cutoff); #0
            
            #echo "$now <br>";
            #echo "$meal->oa_cutoff <br>";
            
            // Less than, not lte. Why? A 10am cutoff means the last order must
            // be completed by 9:59:59
            if ($now2 < $cutoff)
                $mealsAvail[$meal->name] = $meal;
        }
        
        #var_dump($mealsAvail); die(); #0
        return $mealsAvail;
    }
    
    
    /**
     * This function will determine what mode the app is assuming for TODAY, based on
     * the current time.
     * 
     *  @return string
     *      # {meal_id}: Today's current pk_MealType
     */
    public function determineCurrentMealType()
    {
        $meals = MealType::getList();
        $n = count($meals->ordering);
        #var_dump($meals); die();
        $now = $this->nowCarbon->toTimeString();
        $now2 = strtotime($now);
        
        // Base case 1
        // If the time now is less than the end time of the 1st item, then we are in that mode
        $firstIdx = $meals->ordering[0];
        $firstMeal = $meals->hash->$firstIdx;
        if ($now2 <= strtotime($firstMeal->endTime))
            return $firstMeal->pk_MealType;
               
        // Base case 2
        // If the time now is greater than the start time of the 1st item, then we are in that mode
        $lastIdx = $meals->ordering[$n-1];
        $lastMeal = $meals->hash->$lastIdx;
        if ($now2 >= strtotime($lastMeal->startTime))
            return $lastMeal->pk_MealType;
        
        // Otherwise, check everything
        foreach ($meals->ordering as $key => $val)
        {
            $meal = $meals->hash->$val;
            
            $start = strtotime( $meal->startTime );
            $end = strtotime( $meal->endTime );
           
            if ( ($now2 >= $start) && ($now2 <= $end) )
                return $meal->pk_MealType;
        }
        
        // Catchall for inbetween times.
        // This is a hack, and it's mostly for OnDemand.
        return 3;
    }
    
    
    /**
     * Determine which one is next
     * @param obj $instance From the API call: /menu/next/$date
     * @return obj | NULL
     */
    public function findNextMenuFromApi($instance)
    {
        $meals = MealType::getList();
        
        // Lookup!
        foreach ($meals->ordering as $val) 
        {
            $name = $meals->hash->$val->name;
            
            if( isset($instance->menus->$name) )
                return $instance->menus->$name;
        }
        
        // Else catchall
        return NULL;
    }
    
    
        
}
