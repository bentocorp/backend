<?php

namespace Bento\Ctrl;

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
        
        ## /status/overall
        $request = Request::create('/status/overall', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/status/overall'] = $instance;
        
        ## /status/all
        $request = Request::create('/status/all', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/status/all'] = $instance;
        
        ## /ioscopy
        $request = Request::create('/ioscopy', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/ioscopy'] = $instance;
        
        ## /servicearea
        $request = Request::create('/servicearea', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/servicearea'] = $instance;
        
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
        $return['ios_min_version'] = '1.0';
        $return['android_min_version'] = '1.0';
        
        ## Menu (Breakfast/Lunch/Dinner) Times
        $times = array(
            "tzName" => "America/Los_Angeles",
            
            "2" => array(
                "key" => "2",
                "name" => "lunch",
                "startTime" => "11:30:00",
                ),
            
            "3" => array(
                "key" => "3",
                "name" => "dinner",
                "startTime" => "16:30:00",
                ),
        );
        $return['times'] = $times;
        
        return Response::json($return);
    }
    
}
