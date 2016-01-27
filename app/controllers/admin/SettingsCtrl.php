<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Settings;
use View;
use Input;
use Redirect;


class SettingsCtrl extends \BaseController {

    private $data = array();
    
    
    public function __construct() {
        // Nav
        $this->data['nav10'] = true;
    }
    
    
    public function getIndex() {
        
        $data = $this->data;
        $settingsHash = array();
        
        $settings = Settings::all();
        
        // Put them into a hash
        foreach ($settings as $setting) {
            $settingsHash[$setting->key] = $setting->value;
        }
        
        $data['settings'] = $settingsHash;
        
        return View::make('admin.settings', $data);
    }
    
    
    public function postSaveSetting() {
        
        Settings::saveOneFromPost();
        
        $key = Input::get('key');
        
        return Redirect::back()->with('msg', array('type' => 'success', 'txt' => "<b>$key</b> updated."));
    }
    
    
    
}
