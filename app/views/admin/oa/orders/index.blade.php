
@extends('admin.master')

@section('content')


<?php
//var_dump($list);
?>

<h1>Upcoming Scheduled Orders<br>
<small>For {{$today}} and onwards</small></h1>

<table class="table">
    <!-- <caption>Upcoming paid-for scheduled orders!</caption> -->
    <thead>
        <tr>
            <th>Date</th>
            <th>Total Orders</th>
            <th>Total Bentos</th>
        </tr> 
    </thead> 
    <tbody>
        <?php
        foreach ($list as $row)
        {
            ?>
            <tr>
                <td>
                  <a href="/admin/oa/orders/for/{{$row->orderDate}}">{{$row->orderDate}}</a><br>
                  <small class="text-muted">{{$row->niceOrderDate}}</small>
                </td>
                <td>{{$row->order_qty}}</td>
                <td>{{$row->bentoCount}}</td>
            </tr> 
            <?php
        }
        ?>
    </tbody> 
</table>

  
@stop