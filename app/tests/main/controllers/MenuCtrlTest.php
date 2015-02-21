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
    
    
    public function testGetNextMenu() {
        
        // Given a date
        $menuDate = '/menu/next/2015-01-08';
        
        // When I ask for the next menu
        $response = $this->call('GET', $menuDate);

        // Then I get ok, 
        $this->assertResponseStatus(200);
        
        // And I get the correct next menu
        $json = json_decode($response->getContent());
        $this->assertEquals('2015-01-27', $json->Menu->for_date);
    }

}
