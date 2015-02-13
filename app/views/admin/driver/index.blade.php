@extends('admin.master')


@section('content')

<h2>Users</h2>

<form method="post">

<table class="table table-striped">
    <thead>
      <tr>
        <th>On Shift?</th>
        <th>id</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Cell</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        foreach($drivers as $driver) {
            ?>
            <tr>
              <td>{{ $driver->on_shift }}</td>
              <td>{{ $driver->pk_Driver }}</td>
              <td><a href="/admin/driver/edit/{{$driver->pk_Driver}}">{{ $driver->firstname }}</a></td>
              <td>{{ $driver->lastname }}</td>
              <td>{{ $driver->email }}</td>
              <td>{{ $driver->mobile_phone }}</td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<input class="btn btn-success" type="submit" value="Save Shift Status">
  
</form>


@stop