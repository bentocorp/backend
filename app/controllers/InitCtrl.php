<?php

namespace Bento\Ctrl;

use Bento\Admin\Model\Settings;
use Request;
use Route;
use Response;

class InitCtrl extends \BaseController {

    /**
     * Init the application
     * 
     * @return json 
     */
    public function getIndex($date = NULL) {
        
        $return = array();
                
        ## /status/all
        $request = Request::create('/status/all', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/status/all'] = $instance;
        
        ## /ioscopy
        $request = Request::create('/ioscopy', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/ioscopy'] = $instance;
                
        ## /menu Calls
        if ($date !== NULL) {
            // /menu/{date}
            $request = Request::create("/menu/$date", 'GET');
            $instance = json_decode(Route::dispatch($request)->getContent());
            $return['/menu/{date}'] = $instance;
            
            // /menu/next/{date}
            $request = Request::create("/menu/next/$date", 'GET');
            $instance = json_decode(Route::dispatch($request)->getContent());
            $return['/menu/next/{date}'] = $instance;
        }
        
       
        ## App versions
        $return['ios_min_version'] = 2.63;
        $return['android_min_version'] = 16;
        
        
        ## Settings
        $settings = Settings::where('public', '=', '1')->get(array('key', 'value'));
        $settingsHash = array();
        
        foreach ($settings as $row) {
            $settingsHash[$row->key] = $row->value;
        }
        
        $return['settings'] = $settingsHash;
        
        
        ## Meal (Breakfast/Lunch/Dinner) Information
        $meals = array(            
            "2" => array(
                "order" => "2",
                "name" => "lunch",
                "startTime" => "11:30:00",
                ),
            
            "3" => array(
                "order" => "3",
                "name" => "dinner",
                "startTime" => "16:30:00",
                ),
        );
        $return['meals'] = $meals;
        
        return Response::json($return);
    }
    
}
