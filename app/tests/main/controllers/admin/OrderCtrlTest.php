<?php

#use Bento\core\Util\DbUtil;


class AdminOrderCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
        
        Session::start();
        $this->session(['isAdminLoggedIn' => true]);
    }
    
    
    public function testIdempotency()
    {
        // Given an open unassigned order
        
        // And some inventory
        
        // When I assign a driver to the order
        
        // The DI is deducted properly
        
        // And if I assign it again,
        
        // Then the DB is used as the master record, the operation is idempotent,
        // and the order isn't subtracted twice.
    }
    
    
    public function testIdempotency2()
    {
        // Given an order that is already assigned to a driver
        
        // And some inventory
        
        // When I assign a different driver to the order
        
        // Then the DI is deducted properly from the new driver
        
        // And the DI is added properly to the old driver
        
        // And if I assign it again,
        
        // Then the DB is used as the master record, the operation is idempotent,
        // and the order isn't subtracted again.
    }
    
    
    public function testIdempotency3()
    {
        // Given an order that is already assigned to a driver
        
        // And some inventory
        
        // When I assign the order to no driver
        
        // Then the DI is added properly to the old driver
        
        // And if I assign it again,
        
        // Then the DB is used as the master record, the operation is idempotent,
        // and the order isn't added again.
    }
    
    
    public function testCancellingOrderWithNoAssignedDriver() 
    {
        // Given an open order
        
        // And some inventory
        
        // When I cancel it without an assigned driver
        
        // Then the inventory is added back into the LI
        
        
        // And if I cancel it again,
        
        // Then the DB is used as the master record, the operation is idempotent,
        // and the order isn't subtracted twice.
    }
    
    
    public function testCancellingOrderWithAssignedDriver() 
    {
        // Given an open order
        
        // And some inventory
        
        // And the order is assigned to a driver
        
        // When I cancel it
        
        // Then the inventory is added back into the LI and the DI
        
        
        // And if I cancel it again,
        
        // Then the DB is used as the master record, the operation is idempotent,
        // and the order isn't subtracted twice.
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
        
        
        // And if I cancel it again,
        
        // Then the DB is used as the master record, the operation is idempotent,
        // and the order isn't subtracted twice.
    }
    
    
    public function testCantCancelDeliveredOrder()
    {
        // Given a Delivered order
        
        // When I try to cancel it
        
        // I get 400
        
        // And the order is still set as Delivered
    }
    
    
    
}
