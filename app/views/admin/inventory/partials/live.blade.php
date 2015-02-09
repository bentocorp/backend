
<h1>Live Inventory</h1>
<table class="table table-striped" style="width:auto;">
    <thead>
      <tr>
        <th>Type</th>
        <th>Name</th>
        <th>&nbsp;</th>
        <th>Live Inv.</th>
        <th>Driver Inv.</th>
        <th>Match?</th>
      </tr>
    </thead>

    <tbody>
    <?php
    foreach ($liveInventory as $row) {

        // Do live and driver inventories match?
        
        $isMatch = '&nbsp;';
        $isMatchClass = '';
        
        if($row->lqty == $row->dqty) {
            $isMatch = '<span class="glyphicon glyphicon-ok"></span>';
            $isMatchClass = 'success';
        }
        else {
            $isMatch = '<span class="glyphicon glyphicon-remove"></span>';
            $isMatchClass = 'danger';
        }

        echo "<tr>";
            echo "<td>$row->type</td>";
            echo "<td><span title='pk: $row->fk_item'>$row->name</span></td>";
            echo "<td>$row->short_name</td>";
            echo "<td>$row->lqty</td>";
            echo "<td>$row->dqty</td>";
            echo "<td class='$isMatchClass'>$isMatch</td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>
