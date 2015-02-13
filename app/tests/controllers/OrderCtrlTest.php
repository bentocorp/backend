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
    
    
    public function testCantOrderIfNotOpen()
    {
        // Given a non-open state, 
        DB::update('update settings set `value` = "closed" where `key` = ?', array('status'));
        
        // and an authorized user
        $parameters['api_token'] = '123';
        
        // When I attempt to order
        $crawler = $this->client->request('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(423);
    }
    
    

}
