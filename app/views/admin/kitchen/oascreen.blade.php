
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

<div class="clearfix">
    <div style="float:left; width:49%;">
      <h2 style="margin-top:0;">Lunch ({{count($lunchOrders)}}o/{{$lunchBQty}}b)</h2>
        <?php printQtys($lunchQtys, $sushiBucket);?>
    </div>
    <div style="float:right; width:49%;">
        <h2 style="margin-top:0;">Sushi Station</h2>
        <?php 
        //var_dump($sushiBucket);
        printTableStart();
        printBucket($sushiBucket);
        printTableEnd();
        ?>
        <h1 style="font-size:150px;"><span class="label label-info" style="padding:.2em .2em .3em .2em"><span id="countdown-lunch"></span></span></h1>
    </div>
</div>
  
<hr>

<h2>Dinner ({{count($dinnerOrders)}}o/{{$dinnerBQty}}b)</h2>
<?php
//printQtys($dinnerQtys);
// To do: Don't hardcode this. Re-use this template again.
?>

<br>

<?php

function printTableStart()
{
    ?>
    <table class="table table-striped" style="width:auto; font-size:200%;">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>Name</th>
            <th>Code</th>
            <th>Qty</th>
          </tr>
        </thead>

        <tbody>
        <?php
}


function printTableEnd()
{
        ?>
        </tbody>
    </table>
    <?php
}


function printQtys($qtyHash, & $sushiBucket)
{   
    // If nothing
    if (count($qtyHash) == 0) {
        echo '<div class="alert alert-info" role="alert">Nothing yet, sorry.</div>';
        return;
    }
    
    // Open the table
    printTableStart();
    
    // Setup in the order you want
    $buckets = array();
    $buckets['mainBucket'] = array();
    $buckets['sideBucket'] = array();
    $buckets['addonBucket'] = array();
    $buckets['miscBucket'] = array();
        
    // Put in buckets for sorting
    foreach($qtyHash as $dishId => $qty) 
    {
        $agDish = new \stdClass();
        $dish = Dish::find($dishId);
        
        $agDish->dish = $dish;
        $agDish->qty = $qty;
        
        // Special sushi bucket
        // VJC: And remove it from the master list, which is what they said they wanted! (I don't like it)
        if ($dish->is_sushi) {
            #die("it's sushi");
            $sushiBucket[] = $agDish;
            //var_dump($sushiBucket);
            
            // Return, so nothing else happens
            continue;
        }
        
        // Group by type
        if ($dish->type == 'main')
            $buckets['mainBucket'][] = $agDish;
        else if ($dish->type == 'side')
            $buckets['sideBucket'][] = $agDish;
        else if ($dish->type == 'addon')
            $buckets['addonBucket'][] = $agDish;
        else
            $buckets['miscBucket'][] = $agDish;
    }
    
    foreach($buckets as $bucket)
    {
        printBucket($bucket);
    }
    
    // Close the table
    printTableEnd();
}


function printBucket($bucket)
{
    foreach ($bucket as $agDish)
    {
        ?>
        <tr>
          <td>{{substr($agDish->dish->type, 0, 1)}}</td>
          <td>{{$agDish->dish->name}}</td>
          <td>{{$agDish->dish->label}}</td>
          <td>{{$agDish->qty}}</td>
        </tr>
        <?php        
    }
}
?>

  
@stop