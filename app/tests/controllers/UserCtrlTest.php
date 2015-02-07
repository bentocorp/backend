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
    
    

}
