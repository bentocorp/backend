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

        // Then I still get unauthorized
        $this->assertResponseStatus(401);
    }
    
    
    /**
     * Test coupons from the Coupon table
     */
    public function test_AuthdUser_CanApply_Coupon()
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
        
        // Then I still get okay
        $this->assertResponseStatus(200);
        
        // And when I try another valid coupon in ALL CAPS
        $response3 = $this->call('GET', "/coupon/apply/TEST_VINCENT?$api_token");
        
        // Then I get ok
        $this->assertResponseStatus(200);    
    }
    
    
    /**
     * Test UserCoupons (User.user_coupon)
     */
    public function test_AuthdUser_FirstTimeOrder_CanApply_UserCoupons()
    {
        // Given an authenticated user, who has never ordered before, and who hasn't redeemed another user's coupon
        DB::delete('delete from CouponRedemption where fk_User = ?', array(67));
        $api_token = 'api_token=never_ordered';
        
        // When I attempt to apply a valid coupon
        $response = $this->call('GET', "/coupon/apply/vincent2?$api_token");

        // Then I get ok
        $this->assertTrue($this->client->getResponse()->isOk());
        
        // And the amount for this coupon is 15.00
        $json = json_decode($response->getContent());
        $this->assertEquals('15.00', $json->amountOff);
        
        // And when I try again with the same coupon
        $response2 = $this->call('GET', "/coupon/apply/vincent2?$api_token");
        
        // Then I still get ok
        $this->assertResponseStatus(200);
        
        // And when I try another valid coupon in CAPS
        $response3 = $this->call('GET', "/coupon/apply/JASON1?$api_token");
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And when I try my own coupon (for API token never_ordered)
        $response4 = $this->call('GET', "/coupon/apply/vincentt21?$api_token");
        
        // Then I get ok
        $this->assertResponseStatus(200);
        $json4 = json_decode($response4->getContent());
        
        // And the amount for my personal coupon is 15.00
        $this->assertEquals('15.00', $json4->amountOff);
                 
        // Reset (Shouldn't need to)
        #DB::delete('delete from CouponRedemption where fk_User = ?', array(67));
    }
    
    
    /**
     * not_first_order
     */
    public function test_AuthdUser_NotFirstTimeOrder_CannotApply_UserCoupons()
    {
        // Given an authenticated user where has_ordered=1
        $api_token = 'api_token=456';
        
        // When I attempt to use a user's coupon code
        $response = $this->call('GET', "/coupon/apply/vincentt21?$api_token");
        $json = json_decode($response->getContent());

        // Then I get an error
        $this->assertResponseStatus(400);
        
        // and it's the correct error
        $this->assertEquals(Lang::get('coupons.not_first_order'), $json->error);
    }
    
    
    public function test_AuthdUser_NotFirstTimeOrder_CanStillApply_SelfUserCoupon_IfNotUsed()
    {
        // Given an authenticated user where has_ordered=1
        $api_token = 'api_token=08764';
        
        // When I attempt to use my code
        $response = $this->call('GET', "/coupon/apply/VInCENtT31?$api_token");
        $json = json_decode($response->getContent());

        // Then I get ok
        $this->assertResponseStatus(200);
        
        // reset (Shouldn't need to)
        #DB::delete('delete from CouponRedemption where fk_User = ?', array(7));
    }
    
    
    public function test_AuthdUser_NotFirstTimeOrder_CanStillApply_Coupon_IfNotUsed()
    {
        // Given an authenticated user where has_ordered=1
        $api_token = 'api_token=789';
        
        // When I attempt to use my code
        $response = $this->call('GET', "/coupon/apply/1121113370998kkk7?$api_token");
        $json = json_decode($response->getContent());

        // Then I get ok
        $this->assertResponseStatus(200);
    }
    
    
    /**
     * already_used_self_coupon
     */
    public function test_AuthdUser_NotFirstTimeOrder_CannotApply_SelfUserCoupon_IfAlreadyUsed()
    {
        // Given an authenticated user where has_ordered=1
        // "api_token" => "123", below
        
        // When I attempt to use my code
        //---------------------------------------------------------------------
        // Given an order from an authorized user who has a Stripe card on file with us,

        $idempotentTkn = $this->getIdempotentToken();
        
        $data =
        '{
            "OrderItems": [
                {
                    "item_type": "CustomerBentoBox",
                    "unit_price": 12.00,
                    "items": [
                        {"id": 1, "type": "main"},
                        {"id": 2, "type": "side1"}
                    ]
                },
                {
                    "item_type": "CustomerBentoBox",
                    "unit_price": 12.00,
                    "items": [
                        {"id": 1, "type": "main"},
                        {"id": 2, "type": "side1"}
                    ]
                }
            ],
            "OrderDetails": {
                "address": {
                    "number": "1111",
                    "street": "Kearny st.",
                    "city": "San Francisco",
                    "state": "CA",
                    "zip": "94133"
                },
                "coords": {
                    "lat": "37.798220",
                    "long": "-122.405606"
                },
                "tax_cents": 137,
                "tip_cents": 200,
                "total_cents": "1537"
            },
            "Stripe": {
                "stripeToken": NULL
            },
            "IdempotentToken": "%s",
            "Platform": "iOS",
            "CouponCode": "vincentt21",
            
            "order_type": "2",
            "kitchen": "1",
            "OrderAheadZone": "1",
            "for_date": "2030-09-15",
            "scheduled_window_start": "13:00",
            "scheduled_window_end": "14:00",
            "MenuId": "17"
        }';
        
        $parameters = array(
            "data" => '',
            "api_token" => "123"
        );
        
        // and enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
        
        
        // When I attempt to order
        $parameters['data'] = sprintf($data, $idempotentTkn);
        $response = $this->call('POST', '/order', $parameters);   
        //---------------------------------------------------------------------

        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And when I attempt to use my code again (with a new idempotent token)
        $parameters['data'] = sprintf($data, $this->getIdempotentToken());
        $response2 = $this->call('POST', '/order', $parameters);
        $json2 = json_decode($response2->getContent());
        
        // Then I get an error
        $this->assertResponseStatus(400);
        
        // and it's the correct error
        $this->assertEquals(Lang::get('coupons.already_used_self_coupon'), $json2->error);
        
        // reset
        DB::delete('delete from CouponRedemption where fk_User = ?', array(6));
    }
    
    
    public function testCantApplyExpiredCoupon()
    {
        // Given an authenticated user 
        $api_token = 'api_token=123';
        
        // When I try an expired coupon
        $response = $this->call('GET', "/coupon/apply/test_vincent_expired?$api_token");
        $json = json_decode($response->getContent());
        
        // Then I get an error
        $this->assertResponseStatus(404);
        
        // and it's the correct error
        $this->assertEquals(Lang::get('coupons.not_found'), $json->error);
    }
    
    
    public function testBadCouponCode()
    {
        // Given an authenticated user
        $api_token = 'api_token=123';
        
        // When I attempt to apply a non-existent coupon code
        $response = $this->call('GET', "/coupon/apply/someBadCouponCode?$api_token");
        $json = json_decode($response->getContent());

        // Then I get an error
        $this->assertResponseStatus(404);
        
        // and it's the correct error
        $this->assertEquals(Lang::get('coupons.not_found'), $json->error);
    }
    
    
    public function testCantApplyAlreadyUsedCoupon()
    {
        // Given an authenticated user
        $api_token = 'api_token=456';
        
        // When I attempt to apply a coupon that I've already used
        $response = $this->call('GET', "/coupon/apply/1121113370998kkk7?$api_token");
        $json = json_decode($response->getContent());

        // Then I get an error
        $this->assertResponseStatus(400);
        
        // and it's the correct error
        $this->assertEquals(Lang::get('coupons.already_used'), $json->error);
        
        
        // AND When I attempt to apply a coupon that I've already used (in funny caps)
        $response = $this->call('GET', "/coupon/apply/1121113370998KkK7?$api_token");
        $json = json_decode($response->getContent());

        // Then I get an error
        $this->assertResponseStatus(400);
        
        // and it's the correct error
        $this->assertEquals(Lang::get('coupons.already_used'), $json->error);
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
                    "email":"vcardillo@gmail.com",
                    "lat": "37.855542",
                    "long": "-122.485504",
                    "address": "21 Glen Ct, Sausalito, CA 94965, USA",
                    "platform": "iOS"
                }'
        );
        
        // When I request a coupon
        $response = $this->call('POST', '/coupon/request', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        
        // And let's try it again, with some stuff missing
        
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
