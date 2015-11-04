

<?php


if ($list !== NULL): ?>
<table class="table table-hover table-striped">
    <thead>
      <tr>
        {{ isset($checked) ? '<th>&nbsp;</th>' : '' }}
        <th width="18%">Name</th>
        <th>Price</th>
        <th>Label</th>
        <th>Type</th>
        <th>Temp</th>
        <th>Max Per Bento</th>
      </tr>
    </thead>

    <tbody>
    <?php
    foreach ($list as $dish) {

        $class = '';
        
        if (isset($checked)) {
            $checkbox = Form::checkbox('dish[]', $dish->pk_Dish, isset($checked[$dish->pk_Dish]), array('class'=>'f-checkbox'));
            $class = isset($checked[$dish->pk_Dish]) ? 'class="success"' : '';
        }
        
        // Show price for mains
        $priceStr = '';
        if ($dish->price != '' || $dish->price != NULL)
            $priceStr = "\${$dish->price}";
        
        echo "<tr $class>";
            echo isset($checked) ? "<td>$checkbox</td>" : '';
            echo "<td><b><a href='/admin/dish/edit/{$dish->pk_Dish}'>$dish->name</a></b></td>";
            echo "<td>$priceStr</td>";
            echo "<td>$dish->label</td>";
            echo "<td>$dish->type</td>";
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
    
    