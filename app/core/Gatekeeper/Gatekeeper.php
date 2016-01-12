<?php namespace Bento\core\Gatekeeper;


use Bento\Model\Area;
use Bento\Model\OrderAheadZone;
use Bento\core\Gatekeeper\GeoFence;


class Gatekeeper {

    private $lat;
    private $long;

    private $services = array();
    
    private $area = NULL; # Eloquent/Area
    private $oaZone = NULL; # Eloquent/OrderAheadZone
    
    
    public function __construct($lat, $long)
    {
        $this->lat = $lat;
        $this->long = $long;
        
        $this->determineAvailableServices();
    }
    
        
    public function listAvailableServices()
    {
        $services = $this->services;
        
        // Show more stuff for OrderAhead
        if( isset($services['OrderAhead']) ) {
            $services['OrderAhead'] = array(
                'kitchen' => $this->oaZone->fk_Kitchen,
            );
        }
        
        return $services;
    }
    
        
    public function hasService() 
    {
        return count($this->services) > 0 ? true : false;
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
        #if( $this->isInOnDemand() )
            #$this->services['OnDemand'] = true;
        
        // Am I in order_ahead?
        if( $this->isInOrderAhead() )
            $this->services['OrderAhead'] = true;
    }
    
    
    private function isInOrderAhead() 
    {
        $oaZone = $this->whichOaZone();
        
        // We know we're in an area. In an OA zone?
        if ($oaZone !== NULL)
            return true; # yep
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
     * @param array of Eloquent $rows
     * @return Eloquent object
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


    private function getPointString()
    {
        return "$this->lat,$this->long";
    }
    
        

}
