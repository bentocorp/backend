<?php



class ApiAuthFilterTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testProtectionFromPublic()
    {
        // Given a non-authenticated user
        #$mock = Mockery::mock('Input[has]', ['laracon']);
        Input::shouldReceive('has')->once()->andReturn(false);
        
        // When I attempt to access something private
        $this->client->request('POST', '/order');

        // Then I get unauthorized
        $this->assertResponseStatus(401);
    }
    
    

}
