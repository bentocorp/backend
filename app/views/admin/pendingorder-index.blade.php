@extends('admin.master')


@section('content')

<h2>Pending Orders</h2>

<table class="table table-striped">
    <thead>
      <tr>
        <th>id</th>
        <th>Created</th>
        <th>Updated</th>
        <th>Deleted</th>
        <th>User Id</th>
        <th>order_json</th>
        <th>Actions</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        $pendingOrders->each(function($row) {
            
            $delete = $row->deleted_at === NULL 
                ? "<a href='/admin/pendingorder/delete/{$row->pk_PendingOrder}'>Delete</a>"
                : '' ;
            ?>
            <tr>
              <th scope="row">{{{ $row->pk_PendingOrder }}}</th>
              <td>{{{ $row->created_at }}}</td>
              <td>{{{ $row->updated_at }}}</td>
              <td>{{{ $row->deleted_at }}}</td>
              <td>{{{ $row->fk_User }}}</td>
              <td>{{{ $row->order_json }}}</td>
              <td><?php echo $delete ?></td>
            </tr>
            <?php
        });
        ?>
    </tbody>
</table>



@stop