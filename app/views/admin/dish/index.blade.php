

@extends('admin.master')

@section('content')


<!--
******************************************************************************
Dishes
******************************************************************************
-->
<h1>Dishes</h1>

@include('admin.dish.partials.list', array('list' => $dishes))
<br>




  
@stop