

@extends('admin.master')

@section('content')


<!--
******************************************************************************
Menu
******************************************************************************
-->
@include('admin.menu.partials.today')



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



  
@stop