<?php

class OrderCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testStripeOrderIntegrationWithSentToken() 
    {
        // Given an order from an authorized user who is sending us a new stripe token,
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
                        "stripeToken": "'.$_ENV['Stripe_card_token'].'"
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
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // Clean up
        $user = User::find(1);
        $user->stripe_customer_obj = NULL;
        $user->save();
    }
    

}
