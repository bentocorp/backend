<?php

namespace Bento\Ctrl;

use Bento\Model\Status;
use Response;

#use Request; use Route;

class StatusCtrl extends \BaseController {

    /**
     * Get current overall restaurant status
     * 
     * @return json 
     */
    public function getOverall() {
        
        #$request = Request::create('/status/menu/', 'GET');
        #$response = Route::dispatch($request)->getData();
        #$response = Route::dispatch($request);
        #var_dump( $response ); die();
        
        $status = Status::overall();
        
        return Response::json($status);
    }
    
    
    /**
     * Get current status of each menu item
     * 
     * @return json 
     */
    public function getMenu() {
                
        $status = Status::menu();
        
        return Response::json($status);
    }

}
