

@extends('admin.master')

@section('content')


<!--
******************************************************************************
Dishes
******************************************************************************
-->
<h1>Dishes <a class="btn btn-default pull-right" href="/admin/dish/create" role="button">+ New Dish</a></h1>

<hr>

@include('admin.dish.partials.list', array('list' => $dishes))
<br>




  
@stop