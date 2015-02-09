

@extends('admin.master')

@section('content')


<!--
******************************************************************************
Dishes
******************************************************************************
-->
<h1>Dishes</h1>

<hr>
<a class="btn btn-default" href="/admin/dish/create" role="button">+ New Dish</a>
<hr>

@include('admin.dish.partials.list', array('list' => $dishes))
<br>




  
@stop