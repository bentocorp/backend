

<?php


if ($list !== NULL): ?>
<table class="table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Short Name</th>
        <th>Type</th>
        <th>Label</th>
        <th>Temp</th>
        <th>Max Per Order</th>
      </tr>
    </thead>

    <tbody>
    <?php
    foreach ($list as $dish) {

        echo "<tr>";
            echo "<th><a href='/admin/dish/edit/{$dish->pk_Dish}'>$dish->name</a></th>";
            echo "<td>$dish->short_name</td>";
            echo "<td>$dish->type</td>";
            echo "<td>$dish->label</td>";
            echo "<td>$dish->temp</td>";
            echo "<td>$dish->max_per_order</td>";
        echo "</tr>";
    }?>
    </tbody>
</table>
<?php
else:
   echo '<div class="alert alert-danger" role="alert">No dishes defined.</div>';
endif;
    
    