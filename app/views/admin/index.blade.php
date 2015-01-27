
<?php

use Bento\Model\LiveInventory;
use Bento\Admin\Model\Orders;
use Bento\Admin\Model\Drivers;

?>

@extends('admin.master')

@section('content')


<!--
******************************************************************************
Menu
******************************************************************************
-->
<h1>Today's Menu</h1>
<?php

#var_dump($menu); die();
#die();
#echo Carbon::createFromFormat('Y-m-d H', time())->toDateTimeString(); 

if ($menu !== NULL) {
    
    ?>
    <table class="table" style="width:auto;">
        <thead>
          <tr>
            <th>type</th>
            <th>Name</th>
            <th>&nbsp;</th>
            <th>Inv.</th>
          </tr>
        </thead>

        <tbody>
        <?php
        foreach ($menu['MenuItems'] as $row) {
            
            // Is there DriverInventory for this item?
            $hasDriverInventory = $row->DriverInventoryTotal !== NULL ?
                    $row->DriverInventoryTotal
                  : '<span class="glyphicon glyphicon-remove"></span>';
            
            echo "<tr>";
                echo "<td>$row->type</td>";
                echo "<td>$row->name</td>";
                echo "<td>$row->short_name</td>";
                echo "<td>$hasDriverInventory</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
<?php
}
else 
    echo '<div class="alert alert-danger" role="alert">No menu defined today.</div>';
?>

<!--
******************************************************************************
Orders
******************************************************************************
-->
<h1>Open Orders</h1>

<table class="table">
    <thead>
      <tr>
        <th>id</th>
        <th>Customer</th>
        <th>Address</th>
        <th>Phone</th>
        <th>Created</th>
        <th>Driver</th>
        <th>Status</th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        foreach ($openOrders as $row) {
            
            $bentoBoxes = Orders::getBentoBoxesByOrder($row->pk_Order);
            
            ?>
            <tr class="info">
              <form action="/admin/orders/save-status/{{{$row->pk_Order}}}" method="post">
                <th scope="row">{{{ $row->pk_Order }}}</th>
                <td>{{{ $row->user_name }}}</td>
                <td>{{{ $row->street }}} {{{ $row->city }}}, {{{ $row->state }}} {{{ $row->zip }}}</td>
                <td>{{{ $row->user_phone }}}</td>
                <td>{{{ $row->order_created_at }}}</td>
                <td><?php echo Form::select('fk_Driver', $driversDropdown, $row->pk_Driver)?></td>
                <td><?php echo Form::select('status', $orderStatusDropdown, $row->status)?></td>
                <td><button title="Save" type="submit" class="btn btn-default"><span class="glyphicon glyphicon-save"></span></button></td>
              </form>
            </tr>
            <tr>
                <td colspan='6'>
                  
                    <table class="table table-condensed">
                      
                        <tbody>
                            <?php 
                            $boxCount = 1;
                            foreach ($bentoBoxes as $box) {
                                ?>
                                <tr>
                                  <th scope="row">Bento Box {{{$boxCount}}}</th>
                                  <td>{{{$box->main_name}}}</td>
                                  <td>{{{$box->side1_name}}}</td>
                                  <td>{{{$box->side2_name}}}</td>
                                  <td>{{{$box->side3_name}}}</td>
                                  <td>{{{$box->side4_name}}}</td>
                                </tr>
                                <?php
                                $boxCount++;
                            }
                            ?>
                        </tbody>
                    </table>
                </td>
                <td></td><td></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>


<!--
******************************************************************************
Drivers
******************************************************************************
-->
<h1>Drivers with Inventory</h1>

<?php

if ($menu !== NULL):

/*
 * The purpose of all of this hashing is to build dynamic columns based on the inventory of the day,
 * and to be able to line up the headers with the driver rows.
 */

// Get the inventory that everyone is supposed to have
#$invItemNames = LiveInventory::getItemNames();

#$invItemNamesH = array();
$invItemKeys = array();

// Get everything into a consistent lookup table
foreach($menu['MenuItems'] as $inv) {
    #$invItemNamesH[$inv->short_name] = $inv; // Hash it
    $invItemKeys[$inv->pk_Dish] = $inv; // Hash it
}

?>



<table class="table table-striped">
    <thead>
      <tr>
        <th>id</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Email</th>
        <?php
        foreach($invItemKeys as $item) {
            echo "<th>$item->short_name</th>";
        }
        ?>
        <th>&nbsp;</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        // For each Driver
        foreach ($currentDrivers as $row) {
            
            $driverInventory = Drivers::getDriverInventory($row->pk_Driver);
            $driverDishes = array();
            
            // Hash the inventory (an integer indexed array is not helpful here)
            foreach ($driverInventory as $dInvRow) { // $dInvRow = DriverInventory row
                $driverDishes[$dInvRow->fk_item] = $dInvRow;
            }
            #var_dump($driverInventoryH); die();
            
            ?>
            <tr>
              <form action="/admin/drivers/save-inventory/{{{$row->pk_Driver}}}" method="post">
                <th scope="row">{{{ $row->pk_Driver }}}</th>
                <td>{{{ $row->firstname }}} {{{ $row->lastname }}}</td>
                <td>{{{ $row->mobile_phone }}}</td>
                <td>{{{ $row->email }}}</td>
                <?php
                // Now dynamically generate columns for the driver inventory, that we can conveniently
                // use the same hash order for from the <th> section (so everything lines up).
                foreach ($invItemKeys as $invItem) { // short names
                    echo "<td><input type='text' name='{$invItem->pk_Dish}' value='";

                    if ( isset($driverDishes[$invItem->pk_Dish]) )
                      echo $driverDishes[$invItem->pk_Dish]->qty;
                    else
                      echo "0";

                    echo "' class='f_slim-input'></td>";
                }
                ?>
                <td><button title="Save" type="submit" class="btn btn-default"><span class="glyphicon glyphicon-save"></span></button></td>
              </form>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php
else:
    echo "<span class='label label-warning'>Warning</span> I can't pull driver inventory if there's no current menu to know what "
    . "inventory they're supposed to have.";
endif;
?>



<!--
******************************************************************************
Not-open Orders
******************************************************************************
-->
<h1>Not-open Orders</h1>



  
@stop