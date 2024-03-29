<?php

use Bento\Admin\Model\Driver;

$driverOsQty = count($currentDrivers);

?>

<h1>Drivers on Shift ({{$driverOsQty}})</h1>

<?php
if ($driverOsQty > 0):
?>

    <p><b>Note:</b> Only one person should be editing this at a time. Another admin must always refresh first.</p>

    <?php
    if ($mealMenu !== NULL):
    ?>    
        
    <!-- 
    --- Modal Windows! ---
    -->
    
    <!-- Driver Merge Modal -->
    <div class="modal fade" id="dinv-win-merge" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="glyphicon glyphicon-random" aria-hidden="true"></i> &nbsp Merge Drivers</h4>
          </div>
          <div class="modal-body">
            Which driver should get everything?<br>
            <select id="dinv-win-merge-select" class="form-control"></select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="bt.DInv.Merge.do()">Merge!</button>
          </div>
        </div>
      </div>
    </div>
        
    <?php
    /*
     * The purpose of all of this hashing is to build dynamic columns based on the menu of the day,
     * and to be able to line up the headers with the driver rows.
     */

    // Assume that the inventory that everyone is supposed to have is equal to the current menu

    $invItemKeys = array();

    // Get everything into a consistent lookup table
    foreach($mealMenu['MenuItems'] as $inv) {
        $invItemKeys[$inv->pk_Dish] = $inv; // Hash it
    }
    ?>

    <button title="Toggle Dish Names" class="btn btn-default" type="submit" onclick="$('.dinv-item-fullname').toggleClass('hidden')"><i class="glyphicon glyphicon-cutlery" aria-hidden="true"></i></button>
    <button id="dinv-btn-merge" title="Merge Drivers" class="btn btn-default" type="submit" onclick="bt.DInv.Merge.modal()" disabled="disabled"><i class="glyphicon glyphicon-random" aria-hidden="true"></i></button>

    <table id="dinv-table" class="table table-striped">
        <thead>
          <tr>
            <th width="1">&nbsp;</th>
            <th>id</th>
            <th>Name / Email</th>
            <th>Phone</th>
            <?php
            // Echo the dish header labels
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
                <tr id="dinv-table-tr-{{$row->pk_Driver}}" class="dinv-table-tr" dinv-table-tr-id="{{$row->pk_Driver}}">
                  <form action="/admin/driver/save-inventory/{{$row->pk_Driver}}" method="post">
                    <td><input type="checkbox" class="dinv-array-item" name="drivers[{{$row->pk_Driver}}]" value="{{$row->pk_Driver}}" driver-name="{{{ $row->firstname }}} {{{ $row->lastname }}}"></td>
                    <th scope="row">{{{ $row->pk_Driver }}}</th>
                    <td>{{{ $row->firstname }}} {{{ $row->lastname }}}<br><small>{{{ $row->email }}}</small></td>
                    <td>{{{ $row->mobile_phone }}}</td>
                    <?php
                    // Now dynamically generate columns for the driver inventory; we can conveniently
                    // use the same hash order for from the <th> section (so everything lines up).
                    $inventoryColumnsStr  = '';
                    
                    foreach ($invItemKeys as $invItem) { // short names

                        $inventoryColumnsStr .= "<td style='text-align:center;'><input type='number' min='0' required name='newqty[{$invItem->pk_Dish}]' value='";

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
                            .       "<input type='hidden' name='dinv-qty-diff-input' value='0'>"
                            .  "</span>"
                            .  "</td>";
                    }
                    
                    echo $inventoryColumnsStr;
                    ?>
                    <td>
                      <button title="Save" type="submit" class="btn btn-default dinv-btn-save"><span class="glyphicon glyphicon-save"></span></button>
                      <input type='hidden' name='zeroArray' value=''>
                    </td>
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
