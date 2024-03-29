@extends('admin.master')


@section('content')

<h2>Pending Orders (last 100)</h2>

<table class="table table-striped">
    <thead>
      <tr>
        <th>p. id</th>
        <th>ord. id</th>
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
              <td>{{{ $row->fk_Order }}}</td>
              <td>{{{ $row->created_at }}}</td>
              <td>{{{ $row->updated_at }}}</td>
              <td>{{{ $row->deleted_at }}}</td>
              <td>{{{ $row->fk_User }}}</td>
              <td>
                <p class="collapse" id="viewJson-{{{ $row->pk_PendingOrder }}}">{{{ $row->order_json }}}</p>
                <p><a class="btn btn-default" data-toggle="collapse" data-target="#viewJson-{{{ $row->pk_PendingOrder }}}">View &raquo;</a></p>
              </td>
              <td><?php echo $delete ?></td>
            </tr>
            <?php
        });
        ?>
    </tbody>
</table>



@stop