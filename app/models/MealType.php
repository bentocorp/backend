<?php

namespace Bento\Model;


class MealType extends \Eloquent {

    private static $mealsList = NULL;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'MealType';
    protected $primaryKey = 'pk_MealType';
    
    
    public static function getList() 
    {
        if (self::$mealsList !== NULL)
            return self::$mealsList;
        
        $mealsList = new \stdClass();
            $mealsIdx = new \stdClass();
            $mealsOrder = array();
        #$meals = MealType::where('active', '=', '1')->get(['name', 'order']);
        $meals = self::getRows();
        
        // Build list
        foreach ($meals as $meal)
        {
            # First hash them based on pk
            $mealsIdx->{$meal->pk_MealType} = $meal;
            # Build a walkable array whose key/val pairs are key/pk to lookup in the idx
            $mealsOrder[] = $meal->pk_MealType;
        }
        
        $mealsList->hash = $mealsIdx;
        $mealsList->ordering = $mealsOrder;
        
        self::$mealsList = $mealsList;
        
        return $mealsList;
    }
    
    
    public static function getRows()
    {
        return MealType::where('active', '=', '1')->orderBy('order','asc')->get();
    }

    
}
