
@extends('admin.master')

@section('content')

<div class='alert alert-{{$statusClass}}' role='alert'><b>{{$statusMsg}}</b></div>

<a href="/admin/status/open" onclick="return confirm('Open things up? Make sure we aren\'t on sale!')" class="btn btn-success">Open Us</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/status/closed" onclick="return confirm('Shut it down, shut it all down?')" class="btn btn-danger">Shut it all down</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/status/soldout" onclick="return confirm('Food is all gone?')" class="btn btn-warning">We're sold out</a>

<hr>

<?php if ($status == 'closed'): ?>
<a href="/admin/status/reset" onclick="return confirm('Clear all drivers, and close all orders?')" class="btn btn-danger">Clear everything out</a>

<hr>
<?php endif; ?>

<!--
******************************************************************************
Edit some important copy on the dashboard
******************************************************************************
-->
<h1>Edit some copy <a class="btn btn-default" data-toggle="collapse" data-target="#viewSomeCopy">Show &raquo;</a></h1>
<form method="post" action="/admin/misc/ioscopy" class="collapse" id="viewSomeCopy">
    <?php
    $price = 0;
    $sale_price = 0;
    
    foreach ($iosCopy as $row) {
                
        if ($row->key == 'price') {
            $price = $row->value;
            continue;
        }
            
        if ($row->type == 'textarea') {
            ?>
            {{{$row->key}}}: <br>
            <textarea name="ioscopy[{{{$row->key}}}]" class="form-control">{{{$row->value}}}</textarea><br><br>
            <?php
        }
        else {
            ?>
            {{{$row->key}}}: <br>
            <input type="text" class="form-control" name="ioscopy[{{{$row->key}}}]" value="{{{$row->value}}}">
            <?php
            if ($row->key == 'sale_price') {
                echo "<i>(normal price: $price)</i><br>";
                $sale_price = $row->value;
            }
            
            echo '<br>';
        }
    }
    ?>
            
    <button type="submit" class="btn btn-success">Save Copy</button>
</form>

<?php
if ($price != $sale_price)
    echo "<h3><span class='label label-warning'>We're on sale for $$sale_price!</span></h3>";
?>

<hr>


<!--
******************************************************************************
Menu
******************************************************************************
-->
@include('admin.menu.partials.today')

<!--
******************************************************************************
Open Orders
******************************************************************************
-->
@include('admin.order.partials.open', array())


<!--
******************************************************************************
Driver Inventory
******************************************************************************
-->
@include('admin.inventory.partials.driver')


<!--
******************************************************************************
Live Inventory
******************************************************************************
-->
@include('admin.inventory.partials.live')


<!--
******************************************************************************
Not-open Orders
******************************************************************************
<h1>Not-open Orders</h1>
-->


  
@stop