<?php

class MenuCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testGetMenuThatExists()
    {
        // Given a menu that exists
        $menuDate = '/menu/20140127';
        
        // When I call it
        $crawler = $this->client->request('GET', $menuDate);

        // Then I get ok
        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertResponseStatus(200);
    }
    
    
    public function testGetMenuThatDoesNotExist()
    {
        // Given a menu that does not exist
        $menuDate = '/menu/1999-01-01';
        
        // When I call it
        $crawler = $this->client->request('GET', $menuDate);

        // Then I get 404 not found
        $this->assertResponseStatus(404);
    }
    
    
    public function testGetNextSingleMenu() {
        
        // Given a date
        $menuDate = '/menu/next/2014-01-08';
        
        // When I ask for the next menu
        $response = $this->call('GET', $menuDate);

        // Then I get ok, 
        $this->assertResponseStatus(200);
        
        // And I get the correct next menu
        $json = json_decode($response->getContent());
        $this->assertEquals('2014-01-27', $json->menus->dinner->Menu->for_date);
    }
    
    
    public function testGetNextMultiMenu() {
        
        // Given a date
        $menuDate = '/menu/next/2014-05-04';
        
        // When I ask for the next menu
        $response = $this->call('GET', $menuDate);

        // Then I get ok, 
        $this->assertResponseStatus(200);
        
        // And I get the correct next menu
        $json = json_decode($response->getContent());
        
        $this->assertEquals('2014-05-09', $json->menus->lunch->Menu->for_date);
        $this->assertEquals('2014-05-09', $json->menus->dinner->Menu->for_date);
    }

}
