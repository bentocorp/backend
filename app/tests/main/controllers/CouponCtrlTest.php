<?php

class CouponCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testAuthdUserCanApplyCoupon()
    {
        // Given an authenticated user
        $api_token = 'api_token=123';
        
        // When I attempt to apply a valid coupon
        $response = $this->call('GET', "/coupon/apply/1121113370998kkk7?$api_token");

        // Then I get ok
        $this->assertTrue($this->client->getResponse()->isOk());
        
        // And the amount for this test coupon is 12.00
        $json = json_decode($response->getContent());
        $this->assertEquals('12.00', $json->amountOff);
    }
    
    
    public function testPublicUserCannotApplyCoupon()
    {
        // Given a public user (or user with a bad token)
        $api_token = 'api_token=1badtoken';
        
        // When I attempt to apply a valid coupon
        $response = $this->call('GET', "/coupon/apply/1121113370998kkk7?$api_token");

        // Then I get an error
        $this->assertResponseStatus(401);
    }
    
    
    public function testAuthdUserCanRequestCoupon() 
    {
        // Given an authenticated user
        $parameters = array(
            "data" =>
                '{
                    "reason":"outside of delivery zone",
                    "email":"vcardillo@gmail.com"
                }',
            "api_token" => '123'
        );
        
        // When I request a coupon
        $response = $this->call('POST', '/coupon/request', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
    }
    
    
    public function testPublicUserCanRequestCoupon() 
    {
        // Given a public user
        $parameters = array(
            "data" =>
                '{
                    "reason":"outside of delivery zone",
                    "email":"vcardillo@gmail.com"
                }'
        );
        
        // When I request a coupon
        $response = $this->call('POST', '/coupon/request', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
    }

}
