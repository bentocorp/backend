<?php

#use User;

class OrderCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    private function getIdempotentToken() {
        return rand(1000,9999) . chr(rand(65,90));
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
        $parameters['data'] = json_encode(new \stdClass());
        
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
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
                        {
                            "item_type": "CustomerBentoBox",
                            "unit_price": 12.00,
                            "items": [
                                {
                                    "id": 1,
                                    "type": "main"
                                },
                                {
                                    "id": 2,
                                    "type": "side1"
                                }
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
                        "total_cents": "50"
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    "Eta": {
                        "min": "15",
                        "max": "25"
                    }
                }',
            "api_token" => "456"
        );
        
        #print_r($parameters); die();
                        
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(402);
    }
    
    
    public function testCantOrderIfNotEnoughInventory() 
    {
        // Given an order from an authorized user,
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
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
                        "total_cents": "50"
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS"
                }'
                ,
            "api_token" => "123"
        );
        
        // and we're open
        DB::update('update settings set `value` = "open" where `key` = ?', array('status'));
        
        // and not enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 1));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
                
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(410);
    }
    
            
    public function testStripeOrderIntegrationFailsWithBadToken() 
    {
        // Given an order from an authorized user who is sending us a bad stripe token,
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
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
                        "stripeToken": "badToken!"
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS"
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
    
    
  
    /**************************************************************************
     * Success Scenarios
     *************************************************************************/
    
    
    public function testStripeOrderIntegrationWithUserCardSavedInDb() 
    {
        // Given an order from an authorized user who has a Stripe card on file with us,
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
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
                            "unit_price": 10.00,
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
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    "Eta": {
                        "min": "15",
                        "max": "25"
                    }
                }'
                ,
            "api_token" => "123"
        );
        
        // and enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
        
        // and the user hasn't ordered before
        $user = User::find(6);
        $user->has_ordered = 0;
        $user->save();
        
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And the user is now marked as having ordered
        $user = User::find(6);
        $this->assertEquals(1, $user->has_ordered);
    }
    
    
    public function testOrderSuccessWithBadTrakAddress() 
    {
        // Given an order from an authorized user who has a Stripe card on file with us,
        // AND A BAD ADDRESS
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
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
                            "number": "1111998883",
                            "street": "Kearny st.",
                            "city": "San Francisco",
                            "state": "CA",
                            "zip": "94199"
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
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    "Eta": {
                        "min": "15",
                        "max": "25"
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
    

    public function testOrderSuccessWhenItsAllGoneWrongInTrak() 
    {
        // Given an order from an authorized user who has a Stripe card on file with us,
        // AND A BAD ADDRESS, AND a bad lat/long
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
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
                            "number": "1111998883",
                            "street": "Kearny st.",
                            "city": "San Francisco",
                            "state": "CA",
                            "zip": "94199"
                        },
                        "coords": {
                            "lat": "9937.798220",
                            "long": "-99122.405606"
                        },
                        "tax_cents": 137,
                        "tip_cents": 200,
                        "total_cents": "1537"
                    },
                    "Stripe": {
                        "stripeToken": NULL
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    "Eta": {
                        "min": "15",
                        "max": "25"
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
    
    
    public function testOrderSuccessWithTrakIfMissingAddressInfo() 
    {
        // Given an order from an authorized user who has a Stripe card on file with us,
        // AND an address with missing information
        
        $idempotentTkn = $this->getIdempotentToken();
        
        $data = <<<DATA
{
    "OrderItems": [
        {
            "item_type": "CustomerBentoBox",
            "unit_price": 12.00,
            "items": [
                {"id": 1, "type": "main", "name":"Soba Noodles with \"Chick'n\""},
                {"id": 2, "type": "side1", "name":"Soba Noodles with \"Chick'n\""}
            ]
        }
    ],
    "OrderDetails": {
        "address": {
            "number": "",
            "street": "Kearny st.",
            "city": "",
            "state": "CA",
            "zip": "94199"
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
    "IdempotentToken": "$idempotentTkn",
    "Platform": "iOS",
    "Eta": {
        "min": "15",
        "max": "25"
    }
}      
DATA;
        
        $parameters = array(
            "data" => $data,
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
    
    
    public function testOrderSuccessWithKnownWeirdTrakAddress() 
    {
        // Given an order from an authorized user who has a Stripe card on file with us,
        // AND an address with missing information
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
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
                            "number": "706",
                            "street": "Buchanan Street",
                            "city": "San Francisco",
                            "state": "CA",
                            "zip": "94102"
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
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    "Eta": {
                        "min": "15",
                        "max": "25"
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
    
    
    /**
     * @EmailTest
     */
    public function testAdminUserCanOrderEvenIfRestaurantIsClosed() 
    {
        // Given:
        //  an order from an authorized user, 
        //  who has a Stripe card on file with us, 
        //  and is an admin,
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
                        {
                            "item_type": "CustomerBentoBox",
                            "unit_price": 12.00,
                            "items": [
                                {"id": 8, "type": "main"},
                                {"id": 1, "type": "side1"},
                                {"id": 12, "type": "side2"},
                                {"id": 5, "type": "side3"},
                                {"id": 6, "type": "side4"}
                            ]
                        },
                        {
                            "item_type": "CustomerBentoBox",
                            "unit_price": 12.50,
                            "items": [
                                {"id": 8, "type": "main"},
                                {"id": 1, "type": "side1"},
                                {"id": 12, "type": "side2"}
                            ]
                        },
                        {
                            "item_type": "CustomerBentoBox",
                            "unit_price": 12.75,
                            "items": [
                                {"id": 8, "type": "main"},
                                {"id": 1, "type": "side1"},
                                {"id": 12, "type": "side2"}
                            ]
                        },
                        {
                            "item_type": "AddonList",
                            "items": [
                                {"id": 20,  "qty": 1, "unit_price": 3.75},
                                {"id": 21,  "qty": 2, "unit_price": 2.75}
                            ]
                        }
                    ],
                    "OrderDetails": {
                        "address": {
                            "number": "1142",
                            "street": "Kearny st.",
                            "city": "San Francisco",
                            "state": "CA",
                            "zip": "94133"
                        },
                        "coords": {
                            "lat": "37.798220",
                            "long": "-122.405606"
                        },
                        "items_total": 31.25,
                        "delivery_price": 2.75,
                        "coupon_discount_cents": 0,
                        "tax_percentage": 8.75,
                        "tax_cents": 254,
                        "subtotal": 31.54,
                        "tip_percentage": 15,
                        "tip_cents": 469,
                        "total_cents": 3623,
                        "total_cents_without_coupon": 4166
                    },
                    "Stripe": {
                        "stripeToken": NULL
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    "Eta": {
                        "min": "15",
                        "max": "25"
                    }
                }'
                ,
            "api_token" => "00123"
        );
        
        // and a closed state
        DB::update('update settings set `value` = "closed" where `key` = ?', array('status'));
        
        // and enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
                
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // Clean up
        DB::update('update settings set `value` = "open" where `key` = ?', array('status'));
    }
    
    
    public function testUserWithSavedStripeInfoCanOrderWithZeroAmount() 
    {
        // Given an order from an authorized user who has a Stripe card on file with us,
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
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
                        "total_cents": "000"
                    },
                    "Stripe": {
                        "stripeToken": NULL
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    "Eta": {
                        "min": "15",
                        "max": "25"
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
    
    
    public function testUserWithoutSavedStripeInfoCanOrderWithZeroAmount() 
    {
        // Given an order from an authorized user who does NOT have a Stripe card on file with us,
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
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
                        "total_cents": "000"
                    },
                    "Stripe": {
                        "stripeToken": NULL
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    "Eta": {
                        "min": "15",
                        "max": "25"
                    }
                }'
                ,
            "api_token" => "789"
        );
        
        // and enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
                
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And indeed, this user has no Stripe info on file
        $user = User::find(4);
        $this->assertEquals(NULL, $user->stripe_customer_obj);
    }
    
    
    public function testCantOrderWithUnder50Cents() 
    {
        // Given an order from an authorized user who does NOT have a Stripe card on file with us,
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
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
                        "tax_cents": 000,
                        "tip_cents": 000,
                        "total_cents": "049"
                    },
                    "Stripe": {
                        "stripeToken": NULL
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS"
                }'
                ,
            "api_token" => "789"
        );
                        
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(462);
    }
    
    
    public function testOrderFilteringOnBadInput() 
    {
        // Given an order from an authorized user who has a Stripe card on file with us,
        // AND some bad input (a blank order)
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [],
                    "OrderDetails": {
                        "address": {
                            "number": "706",
                            "street": "Buchanan Street",
                            "city": "San Francisco",
                            "state": "CA",
                            "zip": "94102"
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
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS"
                }'
                ,
            "api_token" => "123"
        );

        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(400);
    }

    
    
    /**************************************************************************
     * Coupon Scenarios
     *************************************************************************/
    
    public function testCoupon_404CantOrder() 
    {
        // Given a nonexistent coupon
        $coupon = 'someBadCoupon';
        
        $idempotentTkn = $this->getIdempotentToken();
        
        $parameters = array(
            "data" =>
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
                    "CouponCode": "'.$coupon.'",
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS"
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
        
        // Then I get an error
        $this->assertResponseStatus(404);
    }
    
    
    public function testCoupon_GoodCanOrder() 
    {
        // Given a valid coupon
        $coupon = '1121113370998kkk7';
        
        $idempotentTkn = $this->getIdempotentToken();
        
        $parameters = array(
            "data" =>
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
                    "CouponCode": "'.$coupon.'",
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS"
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
        
        // Then I get okay
        $this->assertResponseStatus(200);
        
        // And when I try again with the same coupon
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(400);
        
        // Reset
        DB::delete('delete from CouponRedemption where fk_User = ?', array(6));
    }
    
    
    public function testCoupon_InavlidForUser_CantOrder() 
    {
        // Given an invalid coupon *for this user*
        $coupon = '1121113370998kkk7';
        
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
            "CouponCode": "%s",
            "IdempotentToken": "'.$idempotentTkn.'",
            "Platform": "iOS"
        }';
        
        $parameters = array(
            "data" => '',
            "api_token" => "00123"
        );
        
        // and enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
        
        
        // When I attempt to order
        $parameters['data'] = sprintf($data, $coupon);
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(400);
        
        // And when I try again with the same data
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I still get the same error
        $this->assertResponseStatus(400);
        
        // And when I try again with a coupon that I've also already used
        $parameters['data'] = sprintf($data, 'vincent2');
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I still get the same error
        $this->assertResponseStatus(400);
    }
    
    
    
    /**************************************************************************
     * Idempotent Scenarios
     *************************************************************************/
    
    function testThatADuplicateOrderReturns200ButIsntProcessed()
    {
        // Reset
        DB::delete('delete from `Order` where amount = ?', array(1337.00));

        // Given a valid order
        
        $idempotentTkn = $this->getIdempotentToken();
        
        $parameters = array(
            "data" =>
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
                        "total_cents": "133700"
                    },
                    "Stripe": {
                        "stripeToken": NULL
                    },
                    "CouponCode": "",
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS"
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
        
        // Then I get okay
        $this->assertResponseStatus(200);
        
        // And when I try again with the exact same order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I still get ok
        $this->assertResponseStatus(200);
        
        // And there is only one item
        $rows = DB::select('select * from `Order` where amount = ?', array(1337.00));
        
        $this->assertEquals(1, count($rows));
        
        // And the is_processing flag has been correctly set to 0
        $row = $rows[0];
        $pendingOrder = DB::select('select * from `PendingOrder` where fk_Order = ?', array($row->pk_Order))[0];
        
        $this->assertEquals(0, $pendingOrder->is_processing);
    }
    
    
    /**************************************************************************
     * Test Emails
     *************************************************************************/
    
    /**
     * @EmailTest
     */
    public function testEmailWithPromoCode() 
    {
        // Given:
        //  an order from an authorized user, 
        //  who has a Stripe card on file with us, 
        //  and is an admin,
        
        // And is using a promo code
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
                '{
                    "OrderItems": [
                        {
                            "item_type": "CustomerBentoBox",
                            "unit_price": 12.00,
                            "items": [
                                {"id": 8, "type": "main"},
                                {"id": 1, "type": "side1"},
                                {"id": 12, "type": "side2"},
                                {"id": 5, "type": "side3"},
                                {"id": 6, "type": "side4"}
                            ]
                        },
                        {
                            "item_type": "CustomerBentoBox",
                            "unit_price": 12.50,
                            "items": [
                                {"id": 8, "type": "main"},
                                {"id": 1, "type": "side1"},
                                {"id": 12, "type": "side2"}
                            ]
                        },
                        {
                            "item_type": "CustomerBentoBox",
                            "unit_price": 12.75,
                            "items": [
                                {"id": 8, "type": "main"},
                                {"id": 1, "type": "side1"},
                                {"id": 12, "type": "side2"}
                            ]
                        },
                        {
                            "item_type": "AddonList",
                            "items": [
                                {"id": 20,  "qty": 1, "unit_price": 3.75},
                                {"id": 21,  "qty": 2, "unit_price": 2.75}
                            ]
                        }
                    ],
                    "OrderDetails": {
                        "address": {
                            "number": "1142",
                            "street": "Kearny st.",
                            "city": "San Francisco",
                            "state": "CA",
                            "zip": "94133"
                        },
                        "coords": {
                            "lat": "37.798220",
                            "long": "-122.405606"
                        },
                        "items_total": 31.25,
                        "delivery_price": 2.75,
                        "coupon_discount_cents": 500,
                        "tax_percentage": 8.75,
                        "tax_cents": 254,
                        "subtotal": 31.54,
                        "tip_percentage": 15,
                        "tip_cents": 469,
                        "total_cents": 3623,
                        "total_cents_without_coupon": 4166
                    },
                    "Stripe": {
                        "stripeToken": NULL
                    },
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS"
                }'
                ,
            "api_token" => "00123"
        );
        
        // and a closed state
        DB::update('update settings set `value` = "closed" where `key` = ?', array('status'));
        
        // and enough inventory for the order,
        DB::table('LiveInventory')->truncate();
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
                
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // Clean up
        DB::update('update settings set `value` = "open" where `key` = ?', array('status'));
    }
    
    
    /**************************************************************************
     * Order Ahead
     *************************************************************************/

    public function testOrderAhead_BasicSuccess() 
    {
        // Given an order ahead from an authorized user who has a Stripe card on file with us,
        $idempotentTkn = $this->getIdempotentToken();
        $parameters = array(
            "data" =>
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
                            "unit_price": 10.00,
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
                    "IdempotentToken": "'.$idempotentTkn.'",
                    "Platform": "iOS",
                    
                    "order_type": "2",
                    "kitchen": "1",
                    "OrderAheadZone": "1",
                    "for_date": "2455-09-15",
                    "scheduled_window_start": "13:00",
                    "scheduled_window_end": "14:00",
                    "MenuId": "17"
                }'
                ,
            "api_token" => "123"
        );
        
        // and enough inventory for the order,
        #DB::table('LiveInventory')->truncate();
        #DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(1, 100));
        #DB::insert('insert into LiveInventory (fk_item, qty) values (?, ?)', array(2, 100));
        
        
        
        // When I attempt to order
        $response = $this->call('POST', '/order', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
    }
    
}
