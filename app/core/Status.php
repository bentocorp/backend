<?php namespace Bento\core;


use Bento\Model\Status as StatusMdl;
use Bento\Admin\Model\Settings;
use Bento\Model\MealType;
use DB;


class Status {

    
    public static function get()
    {
        return StatusMdl::getOverall();
    }
    
    
    public static function open() {
        
        $setting = Settings::find('status');
        $setting->value = 'open';
        $setting->save();
    }
    
    
    public static function isOpen() 
    {
        $setting = Settings::find('status');
        
        return $setting->value == 'open' ? true : false;
    }
    
    
    public static function closed() {
        
        $setting = Settings::find('status');
        $setting->value = 'closed';
        $setting->save();
    }
    
    
    public static function isClosed() 
    {
        $setting = Settings::find('status');
        
        return $setting->value == 'closed' ? true : false;
    }
    
    
    public static function soldout() {
        
        $setting = Settings::find('status');
        $setting->value = 'sold out';
        $setting->save();
    }
    
    
    public static function isSoldout() 
    {
        $setting = Settings::find('status');
        
        return $setting->value == 'sold out' ? true : false;
    }
    
    
    public static function getClass() {
        
        $status = StatusMdl::overall()->value;

        $statusClass = '';

        switch ($status) {
            case 'open':
                $statusClass = 'success';
                break;
            case 'closed':
                $statusClass = 'danger';
                break;
            case 'sold out':
                $statusClass = 'warning';
                break;
        }
        
        return $statusClass;
    }
    
    
    public static function getMsg() {
        
        $status = StatusMdl::overall()->value;

        $statusMsg = '';

        switch ($status) {
            case 'open':
                $statusMsg = 'Restaurant is Open!';
                break;
            case 'closed':
                $statusMsg = "Sorry, we're Closed!";
                break;
            case 'sold out':
                $statusMsg = "We're Sold Out!";
                break;
        }
        
        return $statusMsg;
    }
    
    
    public static function getMealMode() {
        
        $sql = "
            select mt.* 
            from settings s
            left join MealType mt on (s.`value` = mt.pk_MealType)
            where s.`key` = ?
        ";
        
        $mode = DB::select($sql, array('fk_MealType_mode'));
        
        return $mode[0];
    }
    
    
    public static function getMealModesForDropdown() {
        
        $mealTypes = MealType::getRows();
        
        $return = array();
        
        foreach ($mealTypes as $mealType) {
            $return[$mealType->pk_MealType] = $mealType->name;
        }
        
        return $return;
    }
     
}
