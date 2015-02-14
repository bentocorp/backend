<?php
use Bento\Admin\Model\Orders;
?>

<h1>Open Orders</h1>

<?php
if (count($openOrders) > 0):
?>

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
        <th>&nbsp;</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        foreach ($openOrders as $row) {
            
            $bentoBoxes = Orders::getBentoBoxesByOrder($row->pk_Order);
            
            ?>
            <tr class="info">
              <form action="/admin/order/save-status/{{{$row->pk_Order}}}" method="post">
                <th scope="row">{{{ $row->pk_Order }}}</th>
                <td>{{{ $row->user_name }}}</td>
                <td>{{{ $row->street }}} {{{ $row->city }}}, {{{ $row->state }}} {{{ $row->zip }}}</td>
                <td>{{{ $row->user_phone }}}</td>
                <td>{{{ $row->order_created_at }}}</td>
                <td><?php echo Form::select('fk_Driver', $driversDropdown, $row->pk_Driver)?></td>
                <td><?php echo Form::select('status', $orderStatusDropdown, $row->status)?></td>
                <td><button title="Save" type="submit" class="btn btn-default"><span class="glyphicon glyphicon-save"></span></button></td>
              </form>
            </tr>
            <tr>
                <td colspan='6'>
                  
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
                <td></td><td></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php
else:
    echo '<div class="alert alert-info" role="alert">All clear sir. No open orders.</div>';
endif;
?>