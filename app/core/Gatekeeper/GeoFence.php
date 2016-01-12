<?php namespace Bento\core\Gatekeeper;



class GeoFence {

    
  /*
   * Source: http://stackoverflow.com/questions/8040671/point-in-polygon-php-errors
   */
    
    private static function pointStringToCoordinates($pointString, $kml = false) 
    {
      $coordinates = explode(",", $pointString);
      
      if ($kml) # KML is reversed
        return array("x" => trim($coordinates[1]), "y" => trim($coordinates[0]));
      else
        return array("x" => trim($coordinates[0]), "y" => trim($coordinates[1]));
    }
    
    
    /**
     * 
     * @param string $point A point string such as 172.34343,42.6767
     * @param array $polygon In the form of $myPolygon = array('4,3', '4,6', '7,6', '7,3','4,3')
     * @param boolean $kmlPoly Is this a KML polygon? If so, lat/long are reversed.
     * @return boolean
     */
    public static function isInsidePolygon($point, $polygon, $kmlPoly = false) 
    {
        #return true; die(); #0
        #var_dump($point);
        #var_dump($polygon);
        
        $result = FALSE;
        
        $point = self::pointStringToCoordinates($point);
        $vertices = array();
        
        foreach ($polygon as $vertex) 
        {
            $vertices[] = self::pointStringToCoordinates($vertex, $kmlPoly); 
        }
        
        #var_dump($vertices); #0
        
        // Check if the point is inside the polygon or on the boundary
        $intersections = 0; 
        $vertices_count = count($vertices);
        
        for ($i=1; $i < $vertices_count; $i++) 
        {
            $vertex1 = $vertices[$i-1]; 
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) 
            { 
                // This point is on an horizontal polygon boundary
                $result = TRUE;
                // set $i = $vertices_count so that loop exits as we have a boundary point
                $i = $vertices_count;
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) 
            { 
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x']; 
                if ($xinters == $point['x']) 
                { // This point is on the polygon boundary (other than horizontal)
                    $result = TRUE;
                    // set $i = $vertices_count so that loop exits as we have a boundary point
                    $i = $vertices_count;
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) 
                {
                    $intersections++; 
                }
            } 
        }
        
        // If the number of edges we passed through is even, then it's in the polygon. 
        // Have to check here also to make sure that we haven't already determined that a point is on a boundary line
        if ($intersections % 2 != 0 && $result == FALSE) 
        {
            $result = TRUE;
        }

        return $result;
    }
    
    
    public static function testAlgorithm()
    {
        $myPolygon = array('4,3', '4,6', '7,6', '7,3','4,3');

        $test_points = array('0,0','1,1','2,2','3,3','3.99999,3.99999','4,4','5,5','6,6','6.99999,5.99999','7,7');
        echo "The test polygon has the co-ordinates ";
        foreach ($myPolygon as $polypoint){
            echo $polypoint.", ";
        }
        echo "<br/>"; 
        foreach ($test_points as $apoint)
        {
            echo "Point ".$apoint." is ";
            if (!self::isInsidePolygon($apoint,$myPolygon))
            {
                echo " NOT ";
            }
            echo "inside the test polygon<br />";
        }
    }
    

}
