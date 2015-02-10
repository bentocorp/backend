<?php

class UserCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testAuthLoginNotFound()
    {
        // Given a non-existant user
        $parameters = array(
            "data" =>
            '{"email":"idontexist@no.com"}',
        );
        
        // When I attempt to login
        $crawler = $this->client->request('POST', '/user/login', $parameters);

        // Then I get not found
        $this->assertResponseStatus(404);
    }
    
    
    public function testFbLoginNotFound()
    {
        // Given a non-existant user
        $parameters = array(
            "data" =>
            '{"fb_id":"10101199060609965","fb_token":"somefbtoken","email":"idontexist@no.com"}',
        );
        
        // When I attempt to login
        $crawler = $this->client->request('POST', '/user/fblogin', $parameters);

        // Then I get not found
        $this->assertResponseStatus(404);
    }
    

    public function testAuthSignupUserAlreadyExists()
    {
        // Given an existing user
        $parameters = array(
            "data" =>
                '{
                    "name": "John J. Smith",
                    "email": "vcardillo+0@gmail.com",
                    "phone": "555-123-4567",
                    "password": "somepassword"
                }'
        );
        
        // When I attempt to register
        $crawler = $this->client->request('POST', '/user/signup', $parameters);

        // Then I get a conflict http status code
        $this->assertResponseStatus(409);
    }
    
    
    public function testFbSignupUserAlreadyExists()
    {
        // Given an existing user
        $parameters = array(
            "data" =>
                '{
                    "firstname": "John",
                    "lastname": "Smith",
                    "email": "vcardillo+0@gmail.com",
                    "phone": "555-123-4567",
                    "fb_id": "someid",
                    "fb_token": "somefbtoken",
                    "fb_profile_pic": "http://profilepic.jpg",
                    "fb_age_range": "some range",
                    "fb_gender": "male"
                }'
        );
        
        // When I attempt to register
        $crawler = $this->client->request('POST', '/user/fbsignup', $parameters);

        // Then I get a conflict http status code
        $this->assertResponseStatus(409);
    }
    
    
    public function testAuthSignupWorks()
    {
        // Given a new user
        $parameters = array(
            "data" =>
                '{
                    "name": "John J. Smith",
                    "email": "vcardillo+42.0@gmail.com",
                    "phone": "555-123-4567",
                    "password": "somepassword"
                }'
        );
        
        // When I attempt to register
        $crawler = $this->client->request('POST', '/user/signup', $parameters);

        // Then I get success
        $this->assertResponseStatus(200);
    }
    
    
    public function testFbSignupIntegrationWorks()
    {
        // Given a new user
        $parameters = array(
            "data" =>
                '{
                    "firstname": "John",
                    "lastname": "Smith",
                    "email": "vcardillo+42.1@gmail.com",
                    "phone": "555-123-4567",
                    "fb_id": "10101199060609965",
                    "fb_token": "CAALQCVl1AkkBAD3OMgwDkJ7BbmfEZBOBV2VBMKT9dIP6sZBVF0wPGZCt9IoZA4aZAqUEzJTdf9lNyg7Kwh3CznTCycSaio671oXfDEBK69yHf4vSjEiXgM11ejbU5y4ZCZC4tghdcKOunRz4cdNVDyZCMcFXHZBNgig5QTCriZAXW4nDyZAGlJnZBRBt2ZAF5VwsLTZC2OBA37L5B3HIcmpVgbSZBfH",
                    "fb_profile_pic": "http://profilepic.jpg",
                    "fb_age_range": "some range",
                    "fb_gender": "male"
                }'
        );
        
        // When I attempt to register
        $crawler = $this->client->request('POST', '/user/fbsignup', $parameters);

        // Then I get success
        $this->assertResponseStatus(200);
    }
    
    

}
