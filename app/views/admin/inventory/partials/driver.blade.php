<?php

use Bento\Admin\Model\Driver;

?>

<h1>Drivers on Shift</h1>

<?php
if (count($currentDrivers) > 0):
?>

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

    <button title="Toggle Dish Names" class="btn btn-default" type="submit" onclick="$('.dinv-item-fullname').toggleClass('hidden')"><i class="glyphicon glyphicon glyphicon-cutlery" aria-hidden="true"></i></button>

    <table class="table table-striped">
        <thead>
          <tr>
            <th>id</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <?php
            // Echo the dish labels
            $dishColumnStr = '';
            foreach($invItemKeys as $item) {
                $dishColumnStr .= "<th style='text-align:center;'><span title='$item->name'>$item->label</span><br>"
                   . "<span class='dinv-item-fullname hidden'><small>$item->name</small></span></th>";
            }
            echo $dishColumnStr;
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
                    // Now dynamically generate columns for the driver inventory; we can conveniently
                    // use the same hash order for from the <th> section (so everything lines up).
                    $inventoryColumnsStr  = '';
                    
                    foreach ($invItemKeys as $invItem) { // short names
                        $inventoryColumnsStr .= "<td style='text-align:center;'><input type='number' min='0' required name='newqty-{$invItem->pk_Dish}' value='";

                        // If a menu item is not in driver inventory, the amount is assumed to be 0
                        $origQty = 0;
                        if ( isset($driverDishes[$invItem->pk_Dish]) ) {
                            $origQty = $driverDishes[$invItem->pk_Dish]->qty;
                            $inventoryColumnsStr .= $origQty;
                        }
                        else
                            $inventoryColumnsStr .= "0";

                        $inventoryColumnsStr 
                            .= "' class='f_slim-input dinv-qty-input'><br>"
                            .  "<span class='dinv-qty-changeinfo small text-muted hidden'>"
                            .       "<span class='dinv-qty-orig'>$origQty</span> | <span class='dinv-qty-diff'>0</span>"
                            .  "</span></td>";
                    }
                    
                    echo $inventoryColumnsStr;
                    ?>
                    <td><button title="Save" type="submit" class="btn btn-default"><span class="glyphicon glyphicon-save"></span></button></td>
                  </form>
                </tr>
                <?php
            }
            ?>
        </tbody>
        
        <tfoot>
          <tr>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <?php echo $dishColumnStr; ?>
            <th>&nbsp;</th>
          </tr>
        </tfoot>
    </table>

    <?php
    else:
        echo "<div class='alert alert-warning'><b>Add a menu!</b> I can't build driver inventory UI if there's no current menu to know what "
        . "inventory they're supposed to have.</div>";
    endif;

else:
   echo '<div class="alert alert-info" role="alert">You\'ve got no pilots on shift, sir.</div>'; 
endif;
