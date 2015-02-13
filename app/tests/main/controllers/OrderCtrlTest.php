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
        
        // Clean up
        DB::update('update settings set `value` = "open" where `key` = ?', array('status'));
    }
    
    
    public function testCantOrderIfNoPaymentSentAndNoPaymentOnFile() 
    {
        // Given an order from an authorized user, with no payment specified and none on file,
        $parameters = array(
            "data" =>
                '"OrderItems": [
                    {
                        "item_type": "CustomerBentoBox",
                        "items": [
                            {"id": 1,  "type": "main"}, 
                            {"id": 2,  "type": "side1"},
                        ]
                    }',
            "api_token" => "456"
        );
                        
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(402);
    }
    
    
    public function testCantOrderIfNotEnoughInventory() 
    {
        // Given an order from an authorized user,
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
                        {
                            "item_type": "CustomerBentoBox",
                            "items": [
                                {"id": 1, "type": "main"},
                                {"id": 2, "type": "side1"}
                            ]
                        },
                        {
                            "item_type": "CustomerBentoBox",
                            "items": [
                                {"id": 1, "type": "main"},
                                {"id": 2, "type": "side1"}
                            ]
                        }
                    ]
                }'
                ,
            "api_token" => "123"
        );
        
        // and not enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 1));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
                
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(410);
    }
    
    
    public function testStripeOrderIntegrationWithUserCardSavedInDb() 
    {
        // Given an order from an authorized user who has a Stripe card on file with us,
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
                        {
                            "item_type": "CustomerBentoBox",
                            "items": [
                                {"id": 1, "type": "main"},
                                {"id": 2, "type": "side1"}
                            ]
                        }
                    ],
                    "OrderDetails": {
                        "address": {
                            "street": "1111 Kearny st.",
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
                    }
                }'
                ,
            "api_token" => "123"
        );
        
        // and enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
                
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
    }
    
        
    public function testStripeOrderIntegrationFailsWithBadToken() 
    {
        // Given an order from an authorized user who is sending us a bad stripe token,
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
                        {
                            "item_type": "CustomerBentoBox",
                            "items": [
                                {"id": 1, "type": "main"},
                                {"id": 2, "type": "side1"}
                            ]
                        }
                    ],
                    "OrderDetails": {
                        "address": {
                            "street": "1111 Kearny st.",
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
                        "stripeToken": "badToken!"
                    }
                }'
                ,
            "api_token" => "456"
        );
        
        // and enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
                
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(406);
        
        // Clean up
        $user = User::find(1);
        $user->stripe_customer_obj = NULL;
        $user->save();
    }
    
    

}
