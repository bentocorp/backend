
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
        <th>Action</th>
      </tr>
    </thead>

    <tbody>
    <?php
    foreach ($liveInventory as $row) {

        // Do live and driver inventories match?
        #var_dump($row); die(); #0
        
        $isMatch = '&nbsp;';
        $isMatchClass = '';
        
        // Counts matching UI
        if( (!$row->sold_out && $row->lqty == $row->dqty) || ($row->sold_out && $row->qty_saved == $row->dqty) ) {
            $isMatch = '<span class="glyphicon glyphicon-ok"></span>';
            $isMatchClass = 'success';
        }
        else {
            $isMatch = '<span class="glyphicon glyphicon-remove"></span>';
            $isMatchClass = 'danger';
        }
        
        // Sell-out or bring back UI
        $soldOut_btn_On = '<a title="Banish! (Mark as Sold Out)" class="btn btn-default" href="/admin/inventory/soldout/on/'.$row->fk_item.'"><span class="glyphicon glyphicon-fire" aria-hidden="true"></span></a>';
        $soldOut_btn_Off = '<a title="Resurrect. (Return saved (#) inventory)" class="btn btn-default" href="/admin/inventory/soldout/off/'.$row->fk_item.'"><span class="glyphicon glyphicon-leaf" aria-hidden="true"></span></a>';

        if ($row->sold_out) {
            $soldOut_btn = $soldOut_btn_Off;
            $soldOut_class = 'warning';
            $lqty = "$row->lqty ($row->qty_saved)";
        }
        else {
            $soldOut_btn = $soldOut_btn_On;
            $soldOut_class = '';
            $lqty = $row->lqty;
        }
        
        echo "<tr>";
            echo "<td>$row->type</td>";
            echo "<td><span title='pk: $row->fk_item'>$row->name</span></td>";
            echo "<td>$row->label</td>";
            echo "<td class='$soldOut_class'>$lqty</td>";
            echo "<td>$row->dqty</td>";
            echo "<td class='$isMatchClass'>$isMatch</td>";
            echo "<td>$soldOut_btn</td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>
