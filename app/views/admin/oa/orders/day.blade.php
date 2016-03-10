
@extends('admin.master')


@section('content')


@include('admin.oa.orders.partials.list', array(
    'orders' => $monetizedOrders, 
    'pageTitle' => "Scheduled",
    'date' => $date,
    'calloutType' => 'info',
))


@include('admin.oa.orders.partials.list', array(
    'orders' => $cancelledOrders, 
    'pageTitle' => "Cancelled",
    'date' => $date,
    'calloutType' => 'danger',
))


  
@stop