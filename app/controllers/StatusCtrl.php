<?php

namespace Bento\Ctrl;

use Bento\Model\Status;
use Response;
use Request;
use Route;

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
     * Get current status (inventory, etc.) of each menu item
     * 
     * @return json 
     */
    public function getMenu() {
                
        $status = Status::menu();
        
        return Response::json($status);
    }
    
    
    /**
     * Get everything
     * 
     * @return json 
     */
    public function getAll() {
        
        $request = Request::create('/status/menu/', 'GET');
        $menu = Route::dispatch($request)->getData();
        
        $request2 = Request::create('/status/overall/', 'GET');
        $overall = Route::dispatch($request2)->getData();
        
        $return = array(
            'menu' => $menu,
            'overall' => $overall
        );
        
        return Response::json($return);
    }

}
