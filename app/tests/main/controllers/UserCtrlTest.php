<?php


class UserCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    /**************************************************************************
     * Login Tests
    /*************************************************************************/
    
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
    
    
    public function testAuthUserWithNullPasswordCantLogin()
    {
        // Given a user with a null password
        $parameters = array(
            "data" =>
            '{"email":"vcardillo+nullpass@gmail.com"}',
        );
        
        // When I attempt to login
        $crawler = $this->client->request('POST', '/user/login', $parameters);

        // Then I get not found
        $this->assertResponseStatus(404);
    }
    
    
    public function testFbUserWithNullPasswordCantLogin()
    {
        // Given a FB user with a null password
        $parameters = array(
            "data" =>
            '{"email":"vcardillo+nullpass@gmail.com"}',
        );
        
        // When I attempt to login
        $crawler = $this->client->request('POST', '/user/fblogin', $parameters);

        // Then I get not found
        $this->assertResponseStatus(404);
    }
    
    
    public function testAuthLoginWorksWithValidCredentials() {
        
        // Given a valid user
        $parameters = array(
            "data" =>
            '{
                "email": "vincent+5@bentonow.com",
                "password": "somepassword716*"
            }'
        );
        
        // When I login
        $crawler = $this->client->request('POST', '/user/login', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
    }
    
    
    /*
     * A mismatch test case
     */
    public function testFbLoginFailsEvenWithValidAuthCredentials() {
        
        // Given a valid user who is using a correct password
        $parameters = array(
            "data" =>
            '{
                "email": "vincent+5@bentonow.com",
                "password": "somepassword716*"
            }'
        );
        
        // When I attempt to login via Facebook with the API
        $crawler = $this->client->request('POST', '/user/fblogin', $parameters);
        
        // Then I get an error
        $this->assertResponseStatus(404);
    }
    
    
    public function testAuthLoginFailsWithInvalidCredentials() {
        
        // Given a valid user
        $parameters = array(
            "data" =>
            '{
                "email": "vincent+5@bentonow.com",
                "password": "wrongpassword"
            }'
        );
        
        // When I login
        $crawler = $this->client->request('POST', '/user/login', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(403);
    }
    
    
    public function testFbLoginWorksWithValidFbCredentials() {
        
        // Given a valid user
        $parameters = array(
            "data" =>
            '{
                "email": "vincent+4@bentonow.com",
                "fb_token": "myfbtoken"
            }'
        );
        
        // When I login
        $response = $this->call('POST', '/user/fblogin', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And,
        // This user does not have a stored card
        $data = json_decode($response->getContent());
        $this->assertEquals($data->card, NULL);
    }
    
    
    public function testCreditCardReturnedOnValidLogin() {
        
        // Given a valid user
        $parameters = array(
            "data" =>
            '{
                "email": "vincent+6@bentonow.com",
                "fb_token": "myfbtoken"
            }'
        );
        
        // When I login via Facebook
        $response = $this->call('POST', '/user/fblogin', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And,
        // This user has a stored card
        $data = json_decode($response->getContent());
        $this->assertEquals($data->card->brand, "Visa");
        $this->assertEquals($data->card->last4, "4242");
        
        // ---
        
        // Given a valid user 
        $parameters2 = array(
            "data" =>
            '{
                "email": "vincent+5@bentonow.com",
                "password": "somepassword716*"
            }'
        );
        
        // When I attempt to login via normal auth
        $response2 = $this->call('POST', '/user/login', $parameters2);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And,
        // This user has a stored card
        $data2 = json_decode($response2->getContent());
        $this->assertEquals($data2->card->brand, "Visa");
        $this->assertEquals($data2->card->last4, "4242");
    }
    
    
    public function testFbLoginFailsWithInvalidFbCredentials() {
        
        // Given a valid user
        $parameters = array(
            "data" =>
            '{
                "email": "vincent+4@bentonow.com",
                "fb_token": "myBADfbtoken"
            }'
        );
        
        // When I login
        $crawler = $this->client->request('POST', '/user/fblogin', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(403);
    }
    

    /**************************************************************************
     * Signup Tests
    /*************************************************************************/
    
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
        
        // Clean up
        DB::delete('delete from User where email = "vcardillo+42.0@gmail.com"');
    }
    
    
    /**************************************************************************
     * All Other Tests
    /*************************************************************************/
    
    public function testUserCanChangeTheirPhoneNumber()
    {
        // Given an authenticated user
        $parameters = array(
            "data" =>
                '{
                    "new_phone":"585-502-7804"
                }',
            "api_token" => '123'
        );
        
        // With a bad number
        $user = User::where('api_token', '123')->first();
        $user->phone = '1-800-somebadnumber';
        $user->save();
        
        // When I change my phone number
        $response = $this->call('POST', '/user/phone', $parameters);
        
        // Then I get ok
        $this->assertResponseStatus(200);
        
        // And when I check
        $user2 = User::where('api_token', '123')->first();
        
        // It's been set
        $user2->phone = '585-502-7804';
    }
    
}
