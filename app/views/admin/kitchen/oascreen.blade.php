
<?php
use Bento\Admin\Model\Dish;
?>

@extends('admin.master')

@section('content')

<meta http-equiv="refresh" content="10">

<!--
******************************************************************************
Dishes
******************************************************************************
-->
<h1>Scheduled Orders for {{$today}}</h1>

<hr>

<h2>Lunch ({{count($lunchOrders)}})</h2>
<?php
printQtys($lunchQtys);
?>

<hr>

<h2>Dinner ({{count($dinnerOrders)}})</h2>
<?php
printQtys($dinnerQtys);
?>

<br>

<?php
function printQtys($qtyHash)
{
    // If nothing
    if (count($qtyHash) == 0) {
        echo '<div class="alert alert-info" role="alert">Nothing yet, sorry.</div>';
        return;
    }
    
    ?>
    <table class="table table-striped" style="width:auto; font-size:200%;">
        <thead>
          <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Qty</th>
          </tr>
        </thead>

        <tbody>
        <?php
    
    foreach($qtyHash as $dishId => $qty) 
    {
        $dish = Dish::find($dishId);
        ?>
        <tr>
          <td>{{$dish->name}}</td>
          <td>{{$dish->label}}</td>
          <td>{{$qty}}</td>
        </tr>
        <?php
    }
    
        ?>
        </tbody>
    </table>
    <?php
}
?>


  
@stop