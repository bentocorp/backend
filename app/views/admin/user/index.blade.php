@extends('admin.master')


@section('content')

<h2>Users</h2>

<table class="table table-striped">
    <thead>
      <tr>
        <th>id</th>
        <th>Email</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Stripe Obj</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        $users->each(function($user) {
            ?>
            <tr>
              <th scope="row">{{{ $user->pk_User }}}</th>
              <td>{{{ $user->email }}}</td>
              <td>{{{ $user->firstname }}}</td>
              <td>{{{ $user->lastname }}}</td>
              <td>
                <?php if ($user->stripe_customer_obj): ?>
                <pre class="collapse" id="viewJson-User-stripe-{{{ $user->pk_User }}}">
<?php 
try {
    echo $user->stripe_customer_obj; 
} catch (Exception $ex) {
     print_r($user->stripe_customer_obj);
} 
?>
                </pre>
                <p><a class="btn btn-default" data-toggle="collapse" data-target="#viewJson-User-stripe-{{{ $user->pk_User }}}">View &raquo;</a></p>
                <?php else: echo "&nbsp;"; endif; ?>
              </td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td colspan="4"><a href="/admin/user/edit/{{{$user->pk_User}}}">Edit</a> | 
                <a href="/admin/user/impersonate/{{{$user->pk_User}}}">Impersonate</a>
              </td>
            </tr>
            <?php
        });
        ?>
    </tbody>
</table>



@stop