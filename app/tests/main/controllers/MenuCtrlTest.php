<?php

class MenuCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testGetMenuThatExists()
    {
        // Given a menu that exists
        $menuDate = '/menu/20150127';
        
        // When I call it
        $crawler = $this->client->request('GET', $menuDate);

        // Then I get ok
        $this->assertTrue($this->client->getResponse()->isOk());
    }
    
    
    public function testGetMenuThatDoesNotExist()
    {
        // Given a menu that does not exist
        $menuDate = '/menu/20120101';
        
        // When I call it
        $crawler = $this->client->request('GET', $menuDate);

        // Then I get 404 not found
        $this->assertResponseStatus(404);
    }

}
