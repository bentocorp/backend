<?php namespace Bento\core\Gatekeeper;


use Bento\Model\Area;
use Bento\Model\OrderAheadZone;
use Bento\core\Gatekeeper\GeoFence;
use Bento\core\OrderAhead\Menu as OrderAheadMenu;
use Bento\core\Logic\MaitreD;
use Bento\Admin\Model\Settings;


class Gatekeeper {

    private $lat;
    private $long;

    private $services = array();
    private $myZones = array();
    
    private $area = NULL; # Eloquent/Area
    private $oaZone = NULL; # Eloquent/OrderAheadZone
    private $odZone = NULL; # String {lunch, dinner}
    
    
    public function __construct($lat, $long)
    {
        $this->lat = $lat;
        $this->long = $long;
        
        $this->determineAvailableServices();
    }
    
    
    public function getMyZones()
    {
        return $this->myZones;
    }
    
        
    public function listAvailableServices()
    {        
        return $this->services;
    }
    
        
    public function hasService() 
    {
        return count($this->services) > 0 ? true : false;
    }
    
    
    public function isInAnyZone() 
    {
        return count($this->myZones) > 0 ? true : false;
    }
    
    
    public function hasOrderAhead()
    {
        $services = $this->services;
        
        if( isset($services['OrderAhead']) )
            return true;
        else
            return false;
    }
    
    
    public function getOaZone()
    {
        return $this->oaZone;
    }
    
    
    private function determineAvailableServices()
    {
        // Am I in an area?
        $area = $this->whichArea();
        #die( var_dump($area) ); #0;
        
        // If not, we're done
        if ($area === NULL)
            return;
        else
            $this->area = $area;
            
        // Am I in on_demand?
        if( $this->isInOnDemand() )
            $this->services['OnDemand'] = true;
        
        // Am I in order_ahead?
        if( $this->isInOrderAhead() )
            $this->services['OrderAhead'] = true;
        
        $this->processServices();
    }
    
    private function processServices()
    {
        $services = &$this->services;
        $availableOaMenus = OrderAheadMenu::getMenus($this->oaZone->fk_Kitchen);
        
        # OrderAhead
        # Show more stuff for OrderAhead, IF it has menus
        # ToDo: Have a service resolver to handle this sort of thing in an encapsulated way
        if( isset($services['OrderAhead']) )
        {
            if ($availableOaMenus !== NULL)
            {
                $services['OrderAhead'] = array(
                    'kitchen' => $this->oaZone->fk_Kitchen ,
                    'zone' => $this->oaZone->pk_OrderAheadZone ,
                    'availableMenus' => $availableOaMenus ,
                );
            }
            else 
                unset($services['OrderAhead']);
        }
    }
    
    
    private function isInOnDemand() 
    {
        $odZone = $this->whichOdZone();
        
        // We know we're in an area. In an OD zone?
        if ($odZone !== NULL) {
            $this->myZones['OnDemand'] = true;
            return true; # yep
        }
        else 
            return false; # nope
    }
    
    
    private function isInOrderAhead() 
    {
        $oaZone = $this->whichOaZone();
        
        // We know we're in an area. In an OA zone?
        if ($oaZone !== NULL) {
            $this->myZones['OrderAhead'] = true;
            return true; # yep
        }
        else 
            return false; # nope
    }
    
    
    /*
     * Determine if the coords are in any area
     */
    private function whichArea() 
    {
        // Get the Areas
        $areas = Area::all();
        
        $whichRow = $this->findInEloquentPolygons($areas);
        
        return $whichRow;
    }
    
    
    private function whichOdZone()
    {
        // Determine current MealType name
        $md = MaitreD::get();
        $mealName = $md->determineCurrentMealName();
        #echo $mealName; #0
        
        // Get the OdZone
        $zone = Settings::find("serviceArea_$mealName");
        #var_dump($zone); die(); #0
        
        $inZone = $this->findInPolygon($zone->value);
        
        // Record it
        if ($inZone !== NULL)
            $this->odZone = $mealName;
                
        return $inZone;
    }
    
    
    private function whichOaZone()
    {
        // Get the OaZones
        $zones = OrderAheadZone::where('fk_Area', '=', $this->area->pk_Area)->get();
        
        $whichRow = $this->findInEloquentPolygons($zones);
        
        // Record it
        if ($whichRow !== NULL)
            $this->oaZone = $whichRow;
        
        return $whichRow;
    }
    
    
    /**
     * Determine if a point is in a list of polygons, that exist as rows in the DB.
     * The polygon must be stored in a "polygon" DB field.
     * 
     * Assumes KML.
     * 
     * @param array of Eloquent $rows
     * 
     * @return Eloquent object | NULL
     */
    private function findInEloquentPolygons($rows)
    {
        $row = NULL;
        
        foreach($rows as $area) 
        {
            $polyArr = explode(' ', $area->polygon);
            
            $isInArea = GeoFence::isInsidePolygon($this->getPointString(), $polyArr, true);
 
            if ($isInArea)
                $row = $area;
        }
        
        return $row;
    }
    
    
    /**
     * Determine if a point is in a polygon.
     * Assumes KML.
     * 
     * @param string KML polygon
     * 
     * @return Eloquent object | NULL
     */
    private function findInPolygon($polygon)
    {
        $polyArr = explode(' ', $polygon);

        $isInArea = GeoFence::isInsidePolygon($this->getPointString(), $polyArr, true);

        if ($isInArea)
            return true;
        else
            return NULL;
    }


    private function getPointString()
    {
        return "$this->lat,$this->long";
    }
    
        

}
