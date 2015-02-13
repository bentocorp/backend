<?php

#use Bento\Facades\FacebookAuth;


class UserCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    /**************************************************************************
     * Integration Tests
    /*************************************************************************/
    
    
    public function testFbSignupIntegrationWorks()
    {
        // Given a new user with a valid FB access token
        $parameters = array(
            "data" =>
                '{
                    "firstname": "John",
                    "lastname": "Smith",
                    "email": "vcardillo+42.1@gmail.com",
                    "phone": "555-123-4567",
                    "fb_id": "10101199060609965",
                    "fb_token": "'.$_ENV['FB_valid_token'].'",
                    "fb_profile_pic": "http://profilepic.jpg",
                    "fb_age_range": "some range",
                    "fb_gender": "male"
                }'
        );
        
        // When I attempt to register
        $crawler = $this->client->request('POST', '/user/fbsignup', $parameters);

        // Then I get success
        $this->assertResponseStatus(200);
        
        // Clean up
        DB::delete('delete from User where email = "vcardillo+42.1@gmail.com"');
    }
    
    
    
    public function testFbLoginIntegrationWorksWithBadDbTokenButGoodProvidedToken() {
        
        // Given that the provided token doesn't match the token in the database,
        // but is still a valid FB access token
        
        $user = User::find(9);
        $user->fb_token = 'badExistingToken';
        $user->save();
        
        $parameters = array(
            "data" =>
            '{
                "email": "vincent+3@bentonow.com",
                "fb_token": "'.$_ENV['FB_valid_token'].'"
            }'
        );
        
        // When I attempt to login
        $response = $this->call('POST', '/user/fblogin', $parameters);
        
        // Then I reach out to FB to get a new token, and still get success        
        $this->assertResponseStatus(200);
        
        // And,
        // Given the new token
        $data = json_decode($response->getContent());
        $newValidToken = $data->fb_token;
        
        $parameters2 = array(
            "data" =>
            '{
                "email": "vincent+3@bentonow.com",
                "fb_token": "'.$newValidToken.'"
            }'
        );
        
        // When I try to login subsequently with the newly provided token
        $response2 = $this->call('POST', '/user/fblogin', $parameters2);
        
        // It works
        $this->assertResponseStatus(200);
    }
        
    
}
