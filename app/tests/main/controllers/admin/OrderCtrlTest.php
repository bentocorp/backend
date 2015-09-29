<?php

use Bento\core\Util\DbUtil;


class OrderCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
        
        Session::start();
        $this->session(['isAdminLoggedIn' => true]);
    }
    
    
    public function testCancellingOrderWithNoAssignedDriver() 
    {
        // Given an open order
        
        // And some inventory
        
        // When I cancel it without an assigned driver
        
        // Then the inventory is added back into the LI
    }
    
    
    public function testCancellingOrderWithAssignedDriver() 
    {
        // Given an open order
        
        // And some inventory
        
        // And the order is assigned to a driver
        
        // When I cancel it
        
        // Then the inventory is added back into the LI and the DI
    }
    
    
    public function testCancellingOrderWithSoldOutItems() 
    {
        // Given an open order
        
        // And some inventory
        
        // And the order is assigned to a driver
        
        // And one of the order items is marked as sold out
        
        // When I cancel it
        
        // Then the LI qty is still 0
        
        // And the qty_saved is accurate
        
        // And, the order is added back into the DI
        
    }
    
    
    
}
