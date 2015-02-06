<?php

class MenuCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testGetMenuThatExists()
    {
        // Given a menu that exists
        
        // When I call it
        $crawler = $this->client->request('GET', '/menu/20150127');

        // Then I get ok
        $this->assertTrue($this->client->getResponse()->isOk());
    }
    
    
    public function testGetMenuThatDoesNotExist()
    {
        // Given a menu that does not exist
        
        // When I call it
        $crawler = $this->client->request('GET', '/menu/20120101');

        // Then I get 404 not found
        $this->assertResponseStatus(404);
    }

}
