<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Settings;
use View;
use Input;
use Redirect;


class SettingsCtrl extends \BaseController {

    public function getIndex() {
        
        $data = array();
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
