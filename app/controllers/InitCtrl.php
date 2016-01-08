<?php

namespace Bento\Ctrl;

use Bento\Admin\Model\Settings;
use Request;
use Route;
use Response;
use DB;

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
        $return['ios_min_version'] = (float) DB::select('select * from `settings` where `key` = ?', array('ios_min_version'))[0]->value;
        $return['android_min_version'] = (int) DB::select('select * from `settings` where `key` = ?', array('android_min_version'))[0]->value;
        
        
        ## Settings
        $settings = Settings::all();
        $pubSettingsHash = array();
        $privSettingsHash = array();
        
        // Put public stuff into the API return
        foreach ($settings as $row)
        {
            if ($row->public)
                $pubSettingsHash[$row->key] = $row->value;
            else
                $privSettingsHash[$row->key] = $row->value;
        }
        
        $return['settings'] = $pubSettingsHash;
        
        /*
        $settings = Settings::where('public', '=', '1')->get(array('key', 'value'));
        $settingsHash = array();
        
        foreach ($settings as $row) {
            $settingsHash[$row->key] = $row->value;
        }
        
        $return['settings'] = $settingsHash;
         * 
         */
        
        ## ETA
        $sse = $privSettingsHash['sse_result'];
        $sse_multiplier = $privSettingsHash['sse_minutesMultiplier'];
        $eta_min = 0;
        
        if ($sse == '' || $sse == NULL || $sse == 0)
            $eta_min = $sse_multiplier;
        else
            $eta_min = $sse;
        
        $eta_max = $eta_min + 10;
        
        $eta = array(
            'eta_min' => (int) $eta_min,
            'eta_max' => (int) $eta_max,
        );
        
        $return['eta'] = $eta;
        
        ## Meal (Breakfast/Lunch/Dinner) Information
        $meals = array(            
            "2" => array(
                "order" => "2",
                "name" => "lunch",
                "startTime" => "11:00:00",
                ),
            
            "3" => array(
                "order" => "3",
                "name" => "dinner",
                "startTime" => "17:00:00",
                ),
        );
        $return['meals'] = $meals;
        
        return Response::json($return);
    }
    
}
