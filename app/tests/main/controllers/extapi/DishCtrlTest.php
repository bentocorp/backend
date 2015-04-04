<?php

class DishCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testCantUseApiIfPublic()
    {
        // Given a non-authenticated user
        
        // When I attempt to use the API
        $crawler = $this->client->request('GET', '/extapi/dish');

        // Then I get unauthorized
        $this->assertResponseStatus(401);
    }
    
    
    public function testCantUseApiWithBadCredentials()
    {
        // Given a user with bad credentials
        $api_username = 'baduser';
        $api_password = 'badpass';
        
        // When I attempt to use the API
        $crawler = $this->client->request('GET', "/extapi/dish?api_username=$api_username&api_password=$api_password");

        // Then I get unauthorized
        $this->assertResponseStatus(401);
    }

}
