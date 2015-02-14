
<?php

?>

@extends('admin.master')

@section('content')

<div class='alert alert-{{$statusClass}}' role='alert'><b>{{$statusMsg}}</b></div>

<a href="/admin/status/open" onclick="return confirm('Open things up?')" class="btn btn-success">Open Us</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/status/closed" onclick="return confirm('Shut it down, shut it all down?')" class="btn btn-danger">Shut it all down</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/status/soldout" onclick="return confirm('Food is all gone?')" class="btn btn-warning">We're sold out</a>

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
-->
<h1>Not-open Orders</h1>



  
@stop