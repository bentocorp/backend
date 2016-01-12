<?php namespace Bento\Ctrl;


use Bento\core\Gatekeeper\Gatekeeper;
use Bento\core\Logic\Frontend;
use Response;
use Bento\core\Gatekeeper\GeoFence; #0


class GatekeeperCtrl extends \BaseController {

    
    public function getHere($lat, $long)
    {
        #GeoFence::testAlgorithm(); #0
        #die();
        
        $response = array(
            'hasService' => false,
            'servicesAvailable' => array(),
            'appState' => 'no_service_wall',
                # no_service_wall: Nothing is available. Show bummer wall.
                # closed_wall: No OA available, and we are closed
                # soldout_wall: No OA available, and we are sold out
                # open: Open to order something! Show the order flow
        );
        
        $gatekeeper = new Gatekeeper($lat, $long);
        
        ## Determine if service is available
        $hasService = $gatekeeper->hasService();
        
        ## If so, give back what's available
        if ($hasService) 
        {
            $response['hasService'] = true;
            $response['servicesAvailable'] = $gatekeeper->listAvailableServices();

            ### Menus

            ### FE Logic
            ### Better to encapsulate the logic here in the backend.
            
            // Determine the app state for the frontend. 
            $response['appState'] = Frontend::getState();
            
            // Build the selection dropdown for the frontend. 
            $response['appDropdown'] = Frontend::getDropdown();
        }  
        
        ## Otherwise, no service
        else {
            
        }
        
        return Response::json($response);
    }

}
