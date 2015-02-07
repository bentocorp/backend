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
            '{"fb_id":"10101199060609965","fb_token":"CAALQCVl1AkkBAPX13sPFYPqrcETqpARco5ZAYkoQdhjrn8NpOZCPAcR4ctblHR8TJwv57KD59sNN1RwuTbZB169Hl8mZCXVTLiEIPWZAQnjWCQcPqbx24vCggiGZC4ofyikVuWeWmcFtRZCZA9vlobVEC0hysWoJ2VoHOObUB4PZAR20B2IxxdikXuVVyoJq18gWpiEhhnD9OZAsDZC0mzushK41RULdL119taaz1cG9HKAEQZDZD","email":"idontexist@no.com"}',
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
            '{"fb_id":"10101199060609965","fb_token":"CAALQCVl1AkkBAPX13sPFYPqrcETqpARco5ZAYkoQdhjrn8NpOZCPAcR4ctblHR8TJwv57KD59sNN1RwuTbZB169Hl8mZCXVTLiEIPWZAQnjWCQcPqbx24vCggiGZC4ofyikVuWeWmcFtRZCZA9vlobVEC0hysWoJ2VoHOObUB4PZAR20B2IxxdikXuVVyoJq18gWpiEhhnD9OZAsDZC0mzushK41RULdL119taaz1cG9HKAEQZDZD","email":"idontexist@no.com"}',
        );
        
        // When I attempt to login
        $crawler = $this->client->request('POST', '/user/fblogin', $parameters);

        // Then I get not found
        $this->assertResponseStatus(404);
    }
    

    public function testAuthSignupUserExists()
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
    
    
    public function testFbSignupUserExists()
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
                    "fb_token": "CAALQCVl1AkkBAMz3ycA3l4mkNvBqVu0y5qjh1dhZARrjqTitqyZAl62z77I80AZAqoXC8BF3E47wZBIeH2rte11QU0LRl7eOZBHk7ZAVZCNpHcbmJtIKHkzLtrL4pYkmoKW9t1cjLxZAhqNqwjZBrLgZAlkwzcU4dSut8PtlRpeafXaZBv9YUyrs30MOEW4t1Tp381A7qqUpHCu4MmZCH4qmQfan",
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
    
    
    public function testFbSignupWorks()
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
                    "fb_token": "CAALQCVl1AkkBAJf22HHdjk7dCnj3qIEpO8QY1YkBusigQKZBZBefufpvZCEZBq7Cv1v7jPfIKO3r2ygEHa9MpuXqfuts0baceoYzIZACeF1icu3vOuF2ZBRxaIZBoZAipld6ZAlQlqmH9DjkEYSaJkeD9ElKPOZCTjPk1Nd8WWsyqWI4kIfHNbtwTXNZA0SBSqgFxAwmmQaaHLTeBEuqrN9EKn0",
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
