

@extends('admin.master')

@section('content')


<!--
******************************************************************************
Dishes
******************************************************************************
-->
<h1>Dishes <a class="btn btn-default pull-right" href="/admin/dish/create" role="button">+ New Dish</a></h1>

<hr>

<h2>Mains & Sides</h2>
@include('admin.dish.partials.list', array('list' => $dishes))

<hr>

<h2>Add-ons</h2>
@include('admin.dish.partials.list', array('list' => $addons))

<br>




  
@stop