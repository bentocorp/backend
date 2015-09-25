<?php

use Bento\core\Util\DbUtil;


class DriverCtrlTest extends TestCase {


    public function setUp() {
        
        parent::setUp();
        Route::enableFilters();
        
        Session::start();
        #Session::put('isAdminLoggedIn', true);
        $this->session(['isAdminLoggedIn' => true]);
    }
    
    
    public function testInventoryCanBeAddedWhenLIandDIareEmpty() {
                
        // Given no inventory
        DB::table('LiveInventory')->truncate();
        DB::table('DriverInventory')->truncate();
        
        // When I try to add a driver to shift,
        DB::update('update Driver set on_shift=1 where pk_Driver = ?', array(1));
        
        // and give him inventory
        $inv = array(
            10 => '20', 
            7 =>  '21',
            8 =>  '22',
        );
        $parameters = array(
            'newqty' => $inv,
            'zeroArray' => '' 
        );
        
        $response = $this->call('POST', '/admin/driver/save-inventory/1', $parameters, [], ['HTTP_REFERER' => route('index')]);
        #var_dump($response); die();
        
        // Then the inventory is added in DI and LI
        $di = DB::select('select * from DriverInventory');
        $diIdx = DbUtil::makeIndexFromResults($di, 'fk_item');
        
        $li = DB::select('select * from LiveInventory');
        $liIdx = DbUtil::makeIndexFromResults($li, 'fk_item');
        
        $this->assertEquals($inv[10], $diIdx[10]->qty);
        $this->assertEquals($inv[7],  $diIdx[7]->qty);
        $this->assertEquals($inv[8],  $diIdx[8]->qty);
        
        $this->assertEquals($inv[10], $liIdx[10]->qty);
        $this->assertEquals($inv[7],  $liIdx[7]->qty);
        $this->assertEquals($inv[8],  $liIdx[8]->qty);
        
        // Reset
        DB::table('LiveInventory')->truncate();
        DB::table('DriverInventory')->truncate();
    }
    
    
    public function testDriverInventoryAdjustment() 
    {
        // (Ensure it's clean)
        DB::table('LiveInventory')->truncate();
        DB::table('DriverInventory')->truncate();
        
        // Given a LI and DI
        
        // (Drivers are on shift)
        DB::update('update Driver set on_shift=1 where pk_Driver = ?', array(1));
        DB::update('update Driver set on_shift=1 where pk_Driver = ?', array(2));
                
        // Driver 1 DI
        DB::insert('insert into DriverInventory (fk_Driver, fk_item, item_type, qty) values (?,?,?,?)',
            array(1,10,"Dish",20));
        DB::insert('insert into DriverInventory (fk_Driver, fk_item, item_type, qty) values (?,?,?,?)',
            array(1,7,"Dish",21));
        DB::insert('insert into DriverInventory (fk_Driver, fk_item, item_type, qty) values (?,?,?,?)',
            array(1,8,"Dish",22));
        
        // Driver 2 DI
        DB::insert('insert into DriverInventory (fk_Driver, fk_item, item_type, qty) values (?,?,?,?)',
            array(2,10,"Dish",1));
        DB::insert('insert into DriverInventory (fk_Driver, fk_item, item_type, qty) values (?,?,?,?)',
            array(2,7,"Dish",2));
        DB::insert('insert into DriverInventory (fk_Driver, fk_item, item_type, qty) values (?,?,?,?)',
            array(2,8,"Dish",10));
        
        // LI
        DB::insert('insert into LiveInventory (fk_item, item_type, qty) values (?,?,?)',
            array(10,"Dish",21));
        DB::insert('insert into LiveInventory (fk_item, item_type, qty) values (?,?,?)',
            array(7,"Dish",23));
        DB::insert('insert into LiveInventory (fk_item, item_type, qty) values (?,?,?)',
            array(8,"Dish",27)); // Instead of 32, let's say we've had 5 orders come in
        
        
        // When I make a live subtraction (-) adjustment to driver 1's inventory
        
        // Some new qty
        $inv = array(
            10 => '20', // no change
            7 =>  '21', // nc
            8 =>  '10', // Oops, I miscounted by 12. LI item 8 should be 15 after this.
        );
        $parameters = array(
            'newqty' => $inv,
            'zeroArray' => '' 
        );
        
        $response = $this->call('POST', '/admin/driver/save-inventory/1', $parameters, [], ['HTTP_REFERER' => route('index')]);

        // Then the driver's inventory is accurate
        $di = DB::select('select * from DriverInventory where fk_Driver = 1');
        $diIdx = DbUtil::makeIndexFromResults($di, 'fk_item');
        
        $this->assertEquals($inv[10], $diIdx[10]->qty);
        $this->assertEquals($inv[7],  $diIdx[7]->qty);
        $this->assertEquals($inv[8],  $diIdx[8]->qty);
        
        
        // And, then the LiveInventory isn't overwritten, and is accurate
        $li = DB::select('select * from LiveInventory');
        $liIdx = DbUtil::makeIndexFromResults($li, 'fk_item');
        
        $this->assertEquals(21, $liIdx[10]->qty);
        $this->assertEquals(23, $liIdx[7]->qty);
        $this->assertEquals(15, $liIdx[8]->qty);
        
        /***/
        
        
        // And When I make a live addition (+) adjustment to driver 2's inventory
        
        // Some new qty
        $inv = array(
            10 => '1', // no change
            7 =>  '5', // Let's add 3. LI item 7 should be 26 after this.
            8 =>  '12', // Let's add 2. LI item 8 should be 17 after this.
        );
        $parameters = array(
            'newqty' => $inv,
            'zeroArray' => '' 
        );
        
        $response = $this->call('POST', '/admin/driver/save-inventory/2', $parameters, [], ['HTTP_REFERER' => route('index')]);

        // Then the driver's inventory is accurate
        $di = DB::select('select * from DriverInventory where fk_Driver = 2');
        $diIdx = DbUtil::makeIndexFromResults($di, 'fk_item');
        
        $this->assertEquals($inv[10], $diIdx[10]->qty);
        $this->assertEquals($inv[7],  $diIdx[7]->qty);
        $this->assertEquals($inv[8],  $diIdx[8]->qty);
        
        
        // And, then the LiveInventory isn't overwritten, and is accurate
        $li = DB::select('select * from LiveInventory');
        $liIdx = DbUtil::makeIndexFromResults($li, 'fk_item');
        
        $this->assertEquals(21, $liIdx[10]->qty);
        $this->assertEquals(26, $liIdx[7]->qty);
        $this->assertEquals(17, $liIdx[8]->qty);
        
        /***/
        
        
        // And if I mark an item as sold out
        $this->call('GET', '/admin/inventory/soldout/on/10', [], [], ['HTTP_REFERER' => route('index')]);

        // Then it's saved correctly
        $liSave = DB::select('select * from LiveInventory where fk_item = 10')[0];
        $this->assertEquals(0, $liSave->qty);
        $this->assertEquals(21, $liSave->qty_saved);
        $this->assertEquals(1, $liSave->sold_out);
        
        // And if I update inventory while sold out
        
        $inv = array(
            10 => '5', // +4
        );
        $parameters = array(
            'newqty' => $inv,
            'zeroArray' => '' 
        );
        
        $this->call('POST', '/admin/driver/save-inventory/2', $parameters, [], ['HTTP_REFERER' => route('index')]);

        // Then the qty is still 0 and qty_saved is the one that's accurate
        $liSave = DB::select('select * from LiveInventory where fk_item = 10')[0];
        $this->assertEquals(0, $liSave->qty);
        $this->assertEquals(25, $liSave->qty_saved);
        $this->assertEquals(1, $liSave->sold_out);
        
        // And if I un-sell-out the item
        $this->call('GET', '/admin/inventory/soldout/off/10', [], [], ['HTTP_REFERER' => route('index')]);
        
        // Then the qty is back to being accurate
        $liSave = DB::select('select * from LiveInventory where fk_item = 10')[0];
        $this->assertEquals(25, $liSave->qty);
        $this->assertEquals(0, $liSave->sold_out);
        
        /***/
        
        // Reset
        DB::table('LiveInventory')->truncate();
        DB::table('DriverInventory')->truncate();
    }
    
        
    public function testDriverMerging()
    {
        // (Ensure it's clean)
        DB::table('LiveInventory')->truncate();
        DB::table('DriverInventory')->truncate();
               
        // (Drivers are on shift)
        DB::update('update Driver set on_shift=1 where pk_Driver = ?', array(1));
        DB::update('update Driver set on_shift=1 where pk_Driver = ?', array(2));
        DB::update('update Driver set on_shift=1 where pk_Driver = ?', array(3));
        
        // Given the inventories of 4 drivers
        $inv1 = array(
            10 => '5', 
            7 =>  '10',
            8 =>  '15',
        );
        $parameters1 = array(
            'newqty' => $inv1,
            'zeroArray' => '' 
        );
        
        $inv2 = array(
            10 => '3', 
            7 =>  '6',
            8 =>  '9',
        );
        $parameters2 = array(
            'newqty' => $inv2,
            'zeroArray' => '' 
        );
        
        $inv3 = array(
            10 => '4', 
            7 =>  '8',
            8 =>  '12',
        );
        $parameters3 = array(
            'newqty' => $inv3,
            'zeroArray' => '' 
        );
        
        $inv4 = array(
            10 => '6', 
            7 =>  '10',
            8 =>  '18',
        );
        $parameters4 = array(
            'newqty' => $inv4,
            'zeroArray' => '' 
        );
        
        $response1 = $this->call('POST', '/admin/driver/save-inventory/1', $parameters1, [], ['HTTP_REFERER' => route('index')]);
        $response2 = $this->call('POST', '/admin/driver/save-inventory/2', $parameters2, [], ['HTTP_REFERER' => route('index')]);
        $response3 = $this->call('POST', '/admin/driver/save-inventory/3', $parameters3, [], ['HTTP_REFERER' => route('index')]);
        $response4 = $this->call('POST', '/admin/driver/save-inventory/4', $parameters4, [], ['HTTP_REFERER' => route('index')]);

        
        // When I merge 3 of them
        $inv1_1 = array(
            10 => '14', # +2 correction from 12 
            7 =>  '21', # -3 correction from 24
            8 =>  '36',
        );
        $parameters1_1 = array(
            'newqty' => $inv1_1,
            'zeroArray' => '2,3,' 
        );
        
        $this->call('POST', '/admin/driver/save-inventory/1', $parameters1_1, [], ['HTTP_REFERER' => route('index')]);


        // Then the merged driver has accurate counts
        $di1 = DB::select('select * from DriverInventory where fk_Driver = 1');
        $diIdx1 = DbUtil::makeIndexFromResults($di1, 'fk_item');
        
        $this->assertEquals($inv1_1[10], $diIdx1[10]->qty);
        $this->assertEquals($inv1_1[7],  $diIdx1[7]->qty);
        $this->assertEquals($inv1_1[8],  $diIdx1[8]->qty);
        
        // And, Then the other drivers have zero counts
        $di2 = DB::select('select * from DriverInventory where fk_Driver = 2');
        $di3 = DB::select('select * from DriverInventory where fk_Driver = 3');
        $this->assertEquals(0, count($di2));
        $this->assertEquals(0, count($di3));
        
        // And, Then the LiveInventory is accurate
        $li = DB::select('select * from LiveInventory');
        $liIdx = DbUtil::makeIndexFromResults($li, 'fk_item');
        
        $this->assertEquals(20, $liIdx[10]->qty);
        $this->assertEquals(31, $liIdx[7]->qty);
        $this->assertEquals(54, $liIdx[8]->qty);
        
        
        // Reset
        DB::table('LiveInventory')->truncate();
        DB::table('DriverInventory')->truncate();
    }

    
    public function testDriversCannotBeTakenOffShiftWhenNotAppropriate() 
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
    
    
    
}
