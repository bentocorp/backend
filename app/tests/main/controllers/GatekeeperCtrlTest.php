<?php


use Carbon\Carbon;


class GatekeeperCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testOutsideAnyArea()
    {
        // Given a point outside of any area (the ocean)
        $lat = '37.70772645289051';
        $long = '-124.74426269531249';
        
        // When I query
        $response = $this->call('GET', "/gatekeeper/here/$lat/$long");
        $json = json_decode($response->getContent());
         
        // Then I get ok,
        $this->assertResponseStatus(200);
        
        // And I am not marked as being in any zone,
        $this->assertEquals($json->isInAnyZone, false);
        
        // And I have no service
        $this->assertEquals($json->hasService, false);
        $this->assertEquals($json->AvailableServices, []);
    }
    
    
    public function testInAreaOutsideZone()
    {
        // Given a point inside an area, but outside of any zone
        $lat = '37.689254214025276';
        $long = '-122.33413696289064';
        
        // When I query
        $response = $this->call('GET', "/gatekeeper/here/$lat/$long");
        $json = json_decode($response->getContent());
         
        // Then I get ok,
        $this->assertResponseStatus(200);
        
        // And I am not marked as being in any zone,
        $this->assertEquals($json->isInAnyZone, false);
        
        // And I have no service
        $this->assertEquals($json->hasService, false);
        $this->assertEquals($json->AvailableServices, []);
    }
    
    
    public function testOrderAheadMenuListLogic()
    {
        // Given I am in an OA zone
        $lat = '37.77071473849609';
        $long = '-122.44262695312501';
        
        
        # Day Before ########
        
        // Given a day that has upcoming OA menus
        //Carbon::create($year, $month, $day, $hour, $minute, $second, $tz);
        $knownDate = Carbon::create('1990', '08', '04', '23', '30', '00', 'America/Los_Angeles');
        Carbon::setTestNow($knownDate);
        
        // When I query
        $response = $this->call('GET', "/gatekeeper/here/$lat/$long");
        $json = json_decode($response->getContent());
        
        // Then I get ok,
        $this->assertResponseStatus(200);
        
        // And the services are available
        $this->assertEquals($json->hasService, true);
        $this->assertObjectHasAttribute('OrderAhead', $json->AvailableServices);
        
        // And the menus are present
        $this->assertEquals(2, count($json->AvailableServices->OrderAhead->availableMenus->menus));
        
        
        # Same Day ########
        
        
        ## Before lunch closes ########
        
        // Given a day that has upcoming OA menus, but before lunch cutoff
        //Carbon::create($year, $month, $day, $hour, $minute, $second, $tz);
        $knownDate = Carbon::create('1990', '08', '05', '09', '30', '00', 'America/Los_Angeles');
        Carbon::setTestNow($knownDate);
        
        // When I query
        $response = $this->call('GET', "/gatekeeper/here/$lat/$long");
        $json = json_decode($response->getContent());
        
        // Then I get ok,
        $this->assertResponseStatus(200);
        
        // And the services are available
        $this->assertEquals($json->hasService, true);
        $this->assertObjectHasAttribute('OrderAhead', $json->AvailableServices);
        
        // And the menu is present
        $this->assertEquals(2, count($json->AvailableServices->OrderAhead->availableMenus->menus));
        
            
        ## After lunch closes ########
        
        // Given a day that has upcoming OA menus, but after lunch cutoff
        //Carbon::create($year, $month, $day, $hour, $minute, $second, $tz);
        $knownDate = Carbon::create('1990', '08', '05', '10', '00', '00', 'America/Los_Angeles');
        Carbon::setTestNow($knownDate);
        
        // When I query
        $response = $this->call('GET', "/gatekeeper/here/$lat/$long");
        $json = json_decode($response->getContent());
        
        // Then I get ok,
        $this->assertResponseStatus(200);
        
        // And the services are available
        $this->assertEquals($json->hasService, true);
        $this->assertObjectHasAttribute('OrderAhead', $json->AvailableServices);
        
        // And the menu is present
        $this->assertEquals(1, count($json->AvailableServices->OrderAhead->availableMenus->menus));
        $this->assertEquals('dinner', $json->AvailableServices->OrderAhead->availableMenus->menus[0]->Menu->meal_name);
        $this->assertEquals('1990-08-05', $json->AvailableServices->OrderAhead->availableMenus->menus[0]->Menu->for_date);
        
        
        ## After dinner closes ########
        
        // Given a day that has upcoming OA menus, but after all cutoffs
        //Carbon::create($year, $month, $day, $hour, $minute, $second, $tz);
        $knownDate = Carbon::create('1990', '08', '05', '15', '00', '00', 'America/Los_Angeles');
        Carbon::setTestNow($knownDate);
        
        // When I query
        $response = $this->call('GET', "/gatekeeper/here/$lat/$long");
        $json = json_decode($response->getContent());
        
        // Then I get ok,
        $this->assertResponseStatus(200);
        
        // And the services are NOT available
        $this->assertEquals(false, isset($json->AvailableServices->OrderAhead));

        
        # Day After ########
        
        // Given a day that has NO upcoming OA menus
        //Carbon::create($year, $month, $day, $hour, $minute, $second, $tz);
        $knownDate = Carbon::create('1990', '08', '06', '01', '00', '00', 'America/Los_Angeles');
        Carbon::setTestNow($knownDate);
        
        // When I query
        $response = $this->call('GET', "/gatekeeper/here/$lat/$long");
        $json = json_decode($response->getContent());
        
        // Then I get ok,
        $this->assertResponseStatus(200);
        
        // And the services are NOT available
        $this->assertEquals(false, isset($json->AvailableServices->OrderAhead));
        
    }

}
