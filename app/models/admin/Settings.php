<?php

namespace Bento\Admin\Model;

use Input;


class Settings extends \Eloquent {
     
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settings';
    protected $primaryKey = 'key';
    
    
    public static function saveOneFromPost() {
        
        $key = Input::get('key');
        $value = Input::get('value');
        
        $setting = Settings::find($key);
        $setting->value = $value;
        $setting->save();
    }
            
}
