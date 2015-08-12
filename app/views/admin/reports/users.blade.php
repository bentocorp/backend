@extends('admin.master')

@section('content')
<h2>Users</h2>

<a class="pull-right btn btn-default" href="/admin/reports/users?format=json&min_id={{{ $minId }}}">JSON</a>
<table class="table table-striped">
  <thead>
    <th>User ID</th>
    <th>Created at</th>
    <th>Email</th>
    <th>Name</th>
  </thead>
  <tbody>
    <?php
      foreach ($users as $row) {
    ?>
      <tr>
        <td>{{{ $row->user_id }}}</td>
        <td>{{{ $row->created_at }}}</td>
        <td>{{{ $row->email }}}</td>
        <td>
          <?php if ($row->fb_id) { ?>
            <a href="http://www.facebook.com/{{{ $row->fb_id}}}">{{{ $row->firstname }}} {{{ $row->lastname }}}</a>
          <?php } else { ?>
            {{{ $row->firstname }}} {{{ $row->lastname }}}
          <?php } ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<nav>
  <?php
    if (count($users) == 1000) {
      $nextUrl = "/admin/reports/users?min_id=" . (end($users)->user_id + 1);
      $nextText = "Next";
    } else {
      $nextUrl = "/admin/reports/users";
      $nextText = "First";
    }
  ?>
  <ul class="pager"><li><a href="{{{ $nextUrl}}}">{{{ $nextText }}}</a></li></ul>
</nav>
@stop