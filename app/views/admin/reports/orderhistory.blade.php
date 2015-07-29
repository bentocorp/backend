@extends('admin.master')

@section('content')
<h2>Order History</h2>

<table class="table table-striped">
  <thead>
    <th>Order ID</th>
    <th>Ordered at</th>
    <th>Amount</th>
    <th>Coupon</th>
    <th>Email</th>
  </thead>
  <tbody>
    <?php
      foreach ($orders as $row) {
    ?>
      <tr>
        <td>{{{ $row->order_id }}}</td>
        <td>{{{ $row->order_created_at }}}</td>
        <td>${{{ $row->amount }}}</td>
        <td>{{{ $row->coupon_id }}}</td>
        <td>{{{ $row->email }}}</td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<nav>
  <?php
    if (count($orders) > 0) {
      $nextUrl = "/admin/reports/orderhistory?min_id=" . (end($orders)->order_id + 1);
      $nextText = "Next";
    } else {
      $nextUrl = "/admin/reports/orderhistory";
      $nextText = "First";
    }
  ?>
  <ul class="pager"><li><a href="{{{ $nextUrl}}}">{{{ $nextText }}}</a></li></ul>
</nav>
@stop