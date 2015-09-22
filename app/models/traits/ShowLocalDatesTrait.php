<?php

namespace Bento\Model\Traits;

use Config;


trait ShowLocalDatesTrait {
    
    /*********************************************************************
     * Attribute getters and setters
     *********************************************************************/

    public function getCreatedAtAttribute($value) {
        
        return $this->getLocalDate($value);
    }
    
    
    public function getUpdatedAtAttribute($value) {
        
        return $this->getLocalDate($value);
    }
    
    
    public function getDeletedAtAttribute($value) {

        return $this->getLocalDate($value);
    }
    
    
    private function getLocalDate($value) {
        
        if ($value == '' || $value === NULL)
                return $value;
        
        $datetime = $this->asDateTime($value);

        return $datetime->timezone(Config::get('app.timezone_local_name')) .' '. Config::get('app.timezone_local_short');
    }
    
}
