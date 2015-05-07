

<?php

#var_dump($menuList); die();

if ($menuList !== NULL): ?>
<table class="table">
    <thead>
      <tr>
        <th>For Date</th>
        <th>Name</th>
        <th>Meal</th>
        <th>Type</th>
        <th>Created</th>
        <th>Modified</th>
      </tr>
    </thead>

    <tbody>
    <?php
    foreach ($menuList as $compoundMenu) {

        $menu = $compoundMenu['Menu'];
        
        $menuName = $menu->name == '' ? $menu->for_date : $menu->name;

        echo "<tr>";
            echo "<th>$menu->for_date</th>";
            echo "<td><a href='/admin/menu/edit/{$menu->pk_Menu}'>$menuName</a></td>";
            echo "<td>$menu->meal_name</td>";
            echo "<td>$menu->menu_type</td>";
            echo "<td>$menu->created_at</td>";
            echo "<td>$menu->updated_at</td>";
        echo "</tr>";
        echo "<tr><td colspan='6' style='padding-left:40px;'>";
            foreach ($compoundMenu['MenuItems'] as $menuItem) {
                echo "<i>$menuItem->type:</i> $menuItem->name  &nbsp;|&nbsp; ";
            }
            if (count($compoundMenu['MenuItems']) == 0)
                echo "<span class='label label-warning'>Warning</span> <b>No menu items for this menu!</b>";
            echo '<br><br>';
        echo "</td></tr>";
    }?>
    </tbody>
</table>
<?php
else:
   echo '<div class="alert alert-danger" role="alert">No menus for this period.</div>';
endif;
    
    