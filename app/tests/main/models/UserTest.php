<?php




class UserTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testMakeCouponCode() 
    {
        $user = new User;
        
        $this->assertTrue(true);
    }
    
        
    
}
