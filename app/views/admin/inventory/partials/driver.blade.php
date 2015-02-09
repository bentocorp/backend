<?php

use Bento\Admin\Model\Driver;

?>

<h1>Drivers with Inventory</h1>

<p><b>Note:</b> Live Inventory is automatically recalculated <i>every time</i> you
  manually update the Driver Inventory. <br>This is computationally expensive, so do so wisely!</p>

<?php

if ($menu !== NULL):

/*
 * The purpose of all of this hashing is to build dynamic columns based on the menu of the day,
 * and to be able to line up the headers with the driver rows.
 */

// Assume that the inventory that everyone is supposed to have is equal to the current menu

$invItemKeys = array();

// Get everything into a consistent lookup table
foreach($menu['MenuItems'] as $inv) {
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
            echo "<th><span title='$item->name'>$item->short_name</span></th>";
        }
        ?>
        <th>&nbsp;</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        // For each Driver
        foreach ($currentDrivers as $row) {
            
            $driverInventory = Driver::getDriverInventory($row->pk_Driver);
            $driverDishes = array();
            
            // Hash the inventory (an integer indexed array is not helpful here)
            foreach ($driverInventory as $dInvRow) { // $dInvRow = DriverInventory row
                $driverDishes[$dInvRow->fk_item] = $dInvRow;
            }
            #var_dump($driverInventoryH); die();
            
            ?>
            <tr>
              <form action="/admin/driver/save-inventory/{{{$row->pk_Driver}}}" method="post">
                <th scope="row">{{{ $row->pk_Driver }}}</th>
                <td>{{{ $row->firstname }}} {{{ $row->lastname }}}</td>
                <td>{{{ $row->mobile_phone }}}</td>
                <td>{{{ $row->email }}}</td>
                <?php
                // Now dynamically generate columns for the driver inventory, that we can conveniently
                // use the same hash order for from the <th> section (so everything lines up).
                foreach ($invItemKeys as $invItem) { // short names
                    echo "<td><input type='text' name='{$invItem->pk_Dish}' value='";

                    // If a menu item is not in driver inventory, the amount is assumed to be 0
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
    echo "<span class='label label-warning'>Warning</span> I can't build driver inventory UI if there's no current menu to know what "
    . "inventory they're supposed to have.";
endif;
?>