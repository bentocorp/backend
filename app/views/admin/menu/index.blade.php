

@extends('admin.master')

@section('content')


<!--
******************************************************************************
Today's Menu
******************************************************************************
-->
@include('admin.menu.partials.today')
<br><br>


<!--
******************************************************************************
Upcoming Menus
******************************************************************************
-->
<h1>Upcoming Menus <a class="btn btn-default" href="/admin/menu/create" role="button">+ New Menu</a></h1>

@include('admin.menu.partials.list', array('menuList' => $menuUpcoming))
<br>


<!--
******************************************************************************
Past Menus
******************************************************************************
-->
<h1>Past Menus</h1>

@include('admin.menu.partials.list', array('menuList' => $menuPast))
<br>

  
@stop