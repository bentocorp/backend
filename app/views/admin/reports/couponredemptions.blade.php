@extends('admin.master')

@section('content')
<h2>Coupon Redemptions</h2>

<a class="pull-right btn btn-default" href="/admin/reports/couponredemptions?format=json&min_id={{{ $minId }}}">JSON</a>
<table class="table table-striped">
  <thead>
    <th>Redemption ID</th>
    <th>Coupon ID</th>
    <th>User</th>
    <th>Created at</th>
  </thead>
  <tbody>
    <?php
      foreach ($redemptions as $row) {
    ?>
      <tr>
        <td>{{{ $row->coupon_redemption_id }}}</td>
        <td>{{{ $row->coupon_id }}}</td>
        <td>{{{ $row->email }}} ({{{ $row->user_id }}})</td>
        <td>{{{ $row->created_at }}}</td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<nav>
  <?php
    if (count($redemptions) == 1000) {
      $nextUrl = "/admin/reports/couponredemptions?min_id=" . (end($redemptions)->user_id + 1);
      $nextText = "Next";
    } else {
      $nextUrl = "/admin/reports/couponredemptions";
      $nextText = "First";
    }
  ?>
  <ul class="pager"><li><a href="{{{ $nextUrl}}}">{{{ $nextText }}}</a></li></ul>
</nav>
@stop