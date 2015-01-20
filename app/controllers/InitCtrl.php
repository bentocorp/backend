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
    public function getIndex() {
        
        $return = array();
        
        ## /status/overall
        $request = Request::create('/status/overall', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/status/overall'] = $instance;
        
        ## /ioscopy
        $request = Request::create('/ioscopy', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/ioscopy'] = $instance;
        
        ## /servicearea
        $request = Request::create('/servicearea', 'GET');
        $instance = json_decode(Route::dispatch($request)->getContent());
        $return['/servicearea'] = $instance;
        
        return Response::json($return);
    }
    
}
