<?php


class DriverCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
    }
    
    
    public function testDriversCannotBeTakenOffShiftWhenAppropriate() 
    {   
        // Given some inventory
        
        // When I try to take the driver off shift, and it would cause LI to be negative
        
        // Then the driver is still on shift
        
        // And, Then the inventory remains the same
    }
    
    
    public function testDriversCanBeTakenOffShiftWhenPossible() 
    {
        // Given some inventory
        
        // When I try to take the driver off shift, and it's ok
        
        // Then the driver is off shift
        
        // And, Then the inventory has been accurately deducted
    }
    
    
    public function testDriverMerging()
    {
        // Given the inventories of three drivers
        
        // When I merge them

        // Then the merged driver has accurate counts
        
        // And, Then the other drivers have zero counts
        
        // And, The LiveInventory is accurate
    }
    
    
    public function testDriverInventoryAdjustment() 
    {
        // Given a LI and DI
        
        // When I make a live adjustment to the driver's inventory

        // Then the driver's inventory is accurate
        
        // And, then the LiveInventory isn't overwritten, and is accurate
    }
    
    
    
}
