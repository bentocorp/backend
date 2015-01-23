<?php
use Bento\Admin\Model\Orders;
?>

@extends('admin.master')

@section('content')


<!--
******************************************************************************
Menu
******************************************************************************
-->
<h1>Today's Menu</h1>
<?php

#var_dump($menu); die();

if ($menu !== NULL) {
    foreach ($menu['MenuItems'] as $row) {
        echo "$row->type: $row->name - $row->short_name <br>";
    }
}
else 
    echo '<div class="alert alert-danger" role="alert">No menu defined today.</div>';
?>

<!--
******************************************************************************
Orders
******************************************************************************
-->
<h1>Open Orders</h1>

<table class="table">
    <thead>
      <tr>
        <th>id</th>
        <th>Customer</th>
        <th>Address</th>
        <th>Phone</th>
        <th>Created</th>
        <th>Driver</th>
        <th>Status</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        foreach ($openOrders as $row) {
            
            $bentoBoxes = Orders::getBentoBoxesByOrder($row->pk_Order);
            
            ?>
            <tr class="info">
              <th scope="row">{{{ $row->pk_Order }}}</th>
              <td>{{{ $row->user_name }}}</td>
              <td>{{{ $row->street }}} {{{ $row->city }}}, {{{ $row->state }}} {{{ $row->zip }}}</td>
              <td>{{{ $row->user_phone }}}</td>
              <td>{{{ $row->order_created_at }}}</td>
              <td>{{{ $row->driver_name }}}</td>
              <td>{{{ $row->status }}}</td>
            </tr>
            <tr>
                <td colspan='7'>
                  
                    <table class="table table-condensed">
                      
                        <tbody>
                            <?php 
                            $boxCount = 1;
                            foreach ($bentoBoxes as $box) {
                                ?>
                                <tr>
                                  <th scope="row">Bento Box {{{$boxCount}}}</th>
                                  <td>{{{$box->main_name}}}</td>
                                  <td>{{{$box->side1_name}}}</td>
                                  <td>{{{$box->side2_name}}}</td>
                                  <td>{{{$box->side3_name}}}</td>
                                  <td>{{{$box->side4_name}}}</td>
                                </tr>
                                <?php
                                $boxCount++;
                            }
                            ?>
                        </tbody>
                    </table>
                </td>    
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>






<!--
******************************************************************************
Not-open Orders
******************************************************************************
-->
<h1>Not-open Orders</h1>



  
@stop