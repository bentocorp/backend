<?php namespace Bento\Ctrl;


use Bento\core\Gatekeeper\Gatekeeper;
use Bento\core\Logic\Frontend;
use Bento\Model\MealType;
use Bento\core\Logic\MaitreD;
use Response;
#use Bento\core\Gatekeeper\GeoFence; #0


class GatekeeperCtrl extends \BaseController {

    
    public function getHere($lat, $long)
    {
        #GeoFence::testAlgorithm(); #0
        #die(); #0
        #Frontend::getOnDemandWidget(); die(); #0
        
        $response = array(
            #'t' => 'hi24', #0;
            'isInAnyZone' => NULL,
            'MyZones' => NULL,
            'hasService' => false,
            'AvailableServices' => new \stdClass(),
            'MealTypes' => MealType::getList(),
            'appState' => NULL,
                # See Frontend->getState()
        );
        
        // Determine current MealType
        $md = MaitreD::get();
        $mealType = $md->determineCurrentMealType();
        $response['CurrentMealType'] = $mealType;
        
        $gatekeeper = new Gatekeeper($lat, $long);
        
        ## Am I in any service area zones?
        $isInZone = $gatekeeper->isInAnyZone();
        $myZones = $gatekeeper->getMyZones();
        
        $response['isInAnyZone'] = $isInZone;
        $response['MyZones'] = $myZones;
        
        
        ## If so, give back what's available
        if ($isInZone) 
        {
            // Build the selection dropdown for the frontend.
            // This also determines whether or not OD is truly available.
            $response['appOnDemandWidget'] = Frontend::getOnDemandWidget();
            
            // If no OD, make sure it's not available!
            if ($response['appOnDemandWidget'] === NULL)
                $gatekeeper->removeService('OnDemand');

            ## Determine if service is available
             # Just because you're in a zone, doesn't mean that stuff is available!!
             # Important Example: I am in OA zone, BUT there are NO OA menus available,
             # then hasService will be false, and the ServicesAvailable array will not contain OA!!
            $hasService = $gatekeeper->hasService(); 
            
            $response['hasService'] = $hasService;
            $response['AvailableServices'] = $gatekeeper->listAvailableServices();
        }
        ## Otherwise, no service
        else {
            
        }
        
        // Determine the app state for the frontend. 
        $response['appState'] = Frontend::getState($gatekeeper->hasOrderAhead(), $isInZone, $gatekeeper->hasService());
                
        return Response::json($response);
    }

}
