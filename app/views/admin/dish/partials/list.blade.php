

<?php


if ($list !== NULL): ?>
<table class="table table-hover table-striped" id="menu-list">
    <thead>
      <tr>
        {{ isset($checked) ? '<th>&nbsp;</th>' : '' }}
        <th>Type</th>
        <th width="18%">Name</th>
        <th>Price</th>
        {{ isset($menuInv) ? '<th>Order Ahead<br>Qty</th>' : '' }}
        <th>Label</th>
        <th>Temp</th>
        <th>Max Per Bento</th>
      </tr>
    </thead>

    <tbody>
    <?php
    foreach ($list as $dish) {

        $class = '';
        
        // The checkbox for Menu List View
        if (isset($checked)) {
            $checkbox = Form::checkbox('dish[]', $dish->pk_Dish, isset($checked[$dish->pk_Dish]), array('class'=>'f-checkbox dish-onmenu'));
            $class = isset($checked[$dish->pk_Dish]) ? 'class="success"' : '';
        }
        
        // The qty for Menu List View
        if (isset($menuInv))
        {
            $dishOaQty = isset($menuInv[$dish->pk_Dish]) ? $menuInv[$dish->pk_Dish] : '';
            $dishOaQtyInput = "<input value='$dishOaQty' type='number' required tabindex='1' min='0' max='9999' class='f_slim-input nospin menu-oa-qty-in' name='oa_qty[$dish->pk_Dish]' id='menu-oa-qty-$dish->pk_Dish' />";
        }
        
        // Show price for mains
        $priceStr = '';
        if ($dish->price != '' || $dish->price != NULL)
            $priceStr = "\${$dish->price}";
        
        echo "<tr $class>";
            echo isset($checked) ? "<td>$checkbox</td>" : '';
            echo "<td>$dish->type</td>";
            echo "<td><b><a href='/admin/dish/edit/{$dish->pk_Dish}'>$dish->name</a></b></td>";
            echo "<td>$priceStr</td>";
            echo isset($menuInv) ? "<td>$dishOaQtyInput</td>" : '';
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
    
    