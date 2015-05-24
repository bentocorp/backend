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
        $return['ios_min_version'] = '1.7';
        $return['android_min_version'] = '1.0';
        
        return Response::json($return);
    }
    
}
