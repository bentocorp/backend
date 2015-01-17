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