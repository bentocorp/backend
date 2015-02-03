
<h1>Today's Menu</h1>

<b>Note:</b> Menus for the mobile app are cached for up to five minutes.<br>
<br>

<?php
#var_dump($menu); die();
#die();
#echo Carbon::createFromFormat('Y-m-d H', time())->toDateTimeString(); 

if ($menu !== NULL) {
    ?>
    
    <div class="clearfix">
      <div style="float:left; margin-right:30px;">
        <table class="table" style="width:auto;">
            <thead>
              <tr>
                <th>type</th>
                <th>Name</th>
                <th>&nbsp;</th>
                <th>D. Inv.</th>
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
                    echo "<td><span title='pk: $row->pk_Dish'>$row->name</span></td>";
                    echo "<td>$row->short_name</td>";
                    echo "<td>$hasDriverInventory</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
      </div>
      <div style="float:left;"><img src="{{{$menu["Menu"]->bgimg}}}" style="max-height:350px"></div>
    </div>

    <a class="btn btn-default" href="/admin/menu/view/{{{$menu["Menu"]->pk_Menu}}}" role="button">View Details</a>
<?php
}
else 
    echo '<div class="alert alert-danger" role="alert">No menu defined today.</div>';
?>
