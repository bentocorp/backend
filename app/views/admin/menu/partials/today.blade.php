
<?php
use Bento\Admin\Model\Menu;

$todaysLocalDate = Menu::getDateForTodaysMenu('Y-m-d');

$mealName = $mealMode->name;

// Is there anything?
$hasMenus = false;
$menu = false;

if (count($menusApi['menus']) > 0) {
    $hasMenus = true;

    // Get the menu out of the deep structure
    if ( isset($menusApi['menus'][$mealName]) ) {

        $menu = $menusApi['menus'][$mealName];

        unset($menusApi['menus'][$mealName]); // Remove it from the array so it isn't listed twice
    }
}

?>

<h1>Today's <cap>{{$mealName}}</cap> Menu <small>{{$todaysLocalDate}}</small></h1>

<b>Note:</b> Menus for the mobile app are cached for up to five minutes.<br>
<br>

<?php
// e.g. Do we have today's lunch menu?
if ($hasMenus) {
    ?>
    
    <div class="clearfix">
      <!-- Today's Menu -->
      <div style="float:left; margin-right:30px;">
        
        <?php
        // Do we have a menu for this meal type?
        if ( $menu !== false ) {
            ?>
            <table class="table" style="width:auto;">
                <thead>
                  <tr>
                    <th>type</th>
                    <th>Name</th>
                    <th>Price</th>
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

                    // Show the price
                    $price = '';
                    if ($row->price != '' && $row->price != NULL)
                        $price = "&nbsp; \${$row->price}";
                    
                    echo "<tr>";
                        echo "<td>$row->type</td>";
                        echo "<td><a href='/admin/dish/edit/$row->pk_Dish'><span title='pk: $row->pk_Dish'>$row->name</span></a></td>";
                        echo "<td><small>$price</small></td>";
                        echo "<td>$row->label &nbsp;</td>";
                        echo "<td>$hasDriverInventory</td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        <?php
        } else {
            echo '<div class="alert alert-danger" role="alert"><b>No '.$mealName.' menu</b> defined for today!!</div>';
        }
        ?>
        
        
      </div>
      
      <!-- Menu Image -->
      <?php if ( $menu !== false ): ?>
      <div style="float:left; margin-right:50px;"><img src="{{{$menu["Menu"]->bgimg}}}" style="max-height:350px"></div>
      <?php endif; ?>
      
      <!-- Other menus (breakfast/lunch/dinner) for today -->
      <div style="float:left;">
        <b>Other Meals for Today</b><br>
        <?php
        #var_dump($menusApi); die(); #0
        // Show remaining menus
        foreach ($menusApi['menus'] as $menu2) {
            #var_dump($menu); die(); #0
            echo "<div> &nbsp; <cap><a href='/admin/menu/edit/{$menu2['Menu']->pk_Menu}'>".$menu2['Menu']->meal_name.'</a></cap></div>';
        }
        
        if (count($menusApi['menus']) == 0)
            echo 'Nothing else for today.';
        ?>
      </div>
    </div>

<?php if ( $menu !== false ): ?>
<a class="btn btn-default" href="/admin/menu/edit/{{{$menu["Menu"]->pk_Menu}}}" role="button">Edit Today's <cap>{{$mealName}}</cap> Menu</a>
<?php
endif;

} // End if there's at least 1 menu to show
else 
    echo '<div class="alert alert-danger" role="alert"><b>No menus</b> defined for today!</div>';


