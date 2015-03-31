<?php

class CouponCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testPublicUserCannotApplyCoupon()
    {
        // Given a public user (or user with a bad token)
        $api_token = 'api_token=1badtoken';
        
        // When I attempt to apply a valid coupon
        $response = $this->call('GET', "/coupon/apply/1121113370998kkk7?$api_token");

        // Then I get unathorized
        $this->assertResponseStatus(401);
        
        // Given an explicit public user
        $response = $this->call('GET', "/coupon/apply/1121113370998kkk7");

        // Then I get unauthorized
        $this->assertResponseStatus(401);
    }
    
    
    public function testAuthdUserCanApplyCoupon()
    {
        // Given an authenticated user who hasn't redeemed
        DB::delete('delete from CouponRedemption where fk_User = ?', array(6));
        $api_token = 'api_token=123';
        
        // When I attempt to apply a valid coupon
        $response = $this->call('GET', "/coupon/apply/1121113370998kkk7?$api_token");

        // Then I get ok
        $this->assertTrue($this->client->getResponse()->isOk());
        
        // And the amount for this test coupon is 12.00
        $json = json_decode($response->getContent());
        $this->assertEquals('12.00', $json->amountOff);
        
        // And when I try again with the same coupon
        $response2 = $this->call('GET', "/coupon/apply/1121113370998kkk7?$api_token");
        
        // Then I get an error
        $this->assertResponseStatus(400);
        
        // And when I try another valid coupon
        $response3 = $this->call('GET', "/coupon/apply/test_vincent?$api_token");
        
        // Then I get ok
        $this->assertResponseStatus(200);
                
        // Reset
        DB::delete('delete from CouponRedemption where fk_User = ?', array(6));
    }
    
    
    public function testAuthdUserCanApplyUserCoupon()
    {
        // Given an authenticated user who hasn't redeemed a user's coupon
        DB::delete('delete from CouponRedemption where fk_User = ?', array(6));
        $api_token = 'api_token=123';
        
        // When I attempt to apply a valid coupon
        $response = $this->call('GET', "/coupon/apply/vincent2?$api_token");

        // Then I get ok
        $this->assertTrue($this->client->getResponse()->isOk());
        
        // And the amount for this coupon is 5.00
        $json = json_decode($response->getContent());
        $this->assertEquals('5.00', $json->amountOff);
        
        // And when I try again with the same coupon
        $response2 = $this->call('GET', "/coupon/apply/vincent2?$api_token");
        
        // Then I get an error
        $this->assertResponseStatus(400);
        
        // And when I try another valid coupon
        $response3 = $this->call('GET', "/coupon/apply/jason1?$api_token");
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And when I try my own coupon (for API token 123)
        $response3 = $this->call('GET', "/coupon/apply/vincentt21?$api_token");
        
        // Then I get ok
        $this->assertResponseStatus(200);
                 
        // Reset
        DB::delete('delete from CouponRedemption where fk_User = ?', array(6));
    }
    
    
    public function testCantApplyExpiredCoupon()
    {
        // Given an authenticated user 
        $api_token = 'api_token=123';
        
        // When I try an expired coupon
        $response = $this->call('GET', "/coupon/apply/test_vincent_expired?$api_token");
        
        // Then I get an error
        $this->assertResponseStatus(400);     
    }
    
    
    public function testBadCouponCode()
    {
        // Given an authenticated user
        $api_token = 'api_token=123';
        
        // When I attempt to apply a non-existant coupon code
        $response = $this->call('GET', "/coupon/apply/someBadCouponCode?$api_token");

        // Then I get an error
        $this->assertResponseStatus(400);
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
