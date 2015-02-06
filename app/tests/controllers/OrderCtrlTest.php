<?php

class OrderCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testCantOrderIfPublic()
    {
        // Given a non-authenticated user
        
        // When I attempt to order
        $crawler = $this->client->request('POST', '/order');

        // Then I get unauthorized
        $this->assertResponseStatus(401);
    }
    
    

}
