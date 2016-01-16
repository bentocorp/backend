<?php

namespace Bento\Ctrl;

use Bento\Admin\Model\Settings;
use Request;
use Route;
use Response;
use DB;
use Input;

/**
 * v2 of /init (/init2)
 */
class Init2Ctrl extends \BaseController {

    /**
     * Init the application
     * 
     * @return json 
     */
    public function getIndex() {
        
        $return = array();
        
        $copy = Input::get('copy', 1); # Want app copy?
        $date = Input::get('date', 0); # Want menus?
        $gatekeeper = Input::get('gatekeeper', 0); # Shall we determine what services are available based on your location?
            $lat  = Input::get('lat', NULL);
            $long = Input::get('long', NULL);
            
        
        ## /status/all
        $request = Request::create('/status/all', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/status/all'] = $instance;
        
        ## /ioscopy
        if ($copy) {
            $request = Request::create('/ioscopy', 'GET');
            $instance = json_decode(Route::dispatch($request)->getContent());
            $return['/ioscopy'] = $instance;
        }
                
        ## /menu Calls
        if ($date) {
            // /menu/{date}
            $request = Request::create("/menu/$date", 'GET');
            $instance = json_decode(Route::dispatch($request)->getContent());
            $return['/menu/{date}'] = $instance;
            
            // /menu/next/{date}
            $request = Request::create("/menu/next/$date", 'GET');
            $instance = json_decode(Route::dispatch($request)->getContent());
            $return['/menu/next/{date}'] = $instance;
        }
        
        ## /gatekeeper Calls
        if ($gatekeeper && $lat && $long) {
            // /gatekeeper/{lat}/{long}
            $request = Request::create("/gatekeeper/here/$lat/$long", 'GET');
            $instance = json_decode(Route::dispatch($request)->getContent());
            $return['/gatekeeper/here/{lat}/{long}'] = $instance;
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
                "startTime" => "11:00:00", # For on-demand
                ),
            
            "3" => array(
                "order" => "3",
                "name" => "dinner",
                "startTime" => "17:00:00", # For on-demand
                ),
        );
        $return['meals'] = $meals;
        
        return Response::json($return);
    }
    
}
