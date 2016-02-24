<?php

#use Bento\Admin\Model\Orders;
use Bento\Model\Order;
use Bento\Model\CustomerBentoBox;
use Bento\app\Bento;
use Bento\Model\OrderItem;
use Bento\core\Util\NumUtil; # This is actually used. The editor doesn't understand it in {{}}
use Bento\Order\OrderType;
use Carbon\Carbon;



try {
    
    $recentOrderQty = count($recentOrders);
?>

<h1>{{$pageTitle}} ({{$recentOrderQty}})</h1>
<p class="text-primary"><b>Bold Name</b> = Top customer</p>

<?php
if ($recentOrderQty > 0):
?>

<table class="table">
    <thead>
      <tr>
        <th>id</th>
        <th>Customer</th>
        <th>Address</th>
        <th style="width:157px;"><small>Created</small> <br><small>ETA Promise</small> <br><small>Completed</small> <br><small>Duration</small></th>
        <th style="text-align:center;">Status</th>
        <th>Driver</th>
      </tr>
    </thead>
    
    <tbody>
        <?php
        foreach ($recentOrders as $row) {
            try {
                
                $bentoBoxes = CustomerBentoBox::getBentoBoxesByOrder($row->pk_Order);
                $addons = OrderItem::getItemsByOrder($row->pk_Order, 'Addon');
                

                $order = new Order(null, $row->pk_Order);
                #$groupedDriversDropdown = $order->getDriversDropdown($driversDropdown);

                #$tableClass = count($groupedDriversDropdown['Possible Drivers']) > 1 ? 'info' : 'warning' ;

                // Set the user's name
                if ($row->is_top_customer)
                    $user_name = "<b class='text-primary'>$row->user_name</b>";
                else 
                    $user_name = $row->user_name;

                // Alert if Trak Error, and not in Trak!
                $trak_alert = '';
                //if ($row->trak_status != '200')
                    //$trak_alert = '<span class="label label-danger"><big>Onfleet Error!</big></span><br>';

                // OrderType
                $orderType = OrderType::getAbbrNameFromId($row->order_type);
                
                ?>
                <tr>
                    <th scope="row">{{ $row->pk_Order }}<br>{{$orderType}}</th>
                    <td><?php echo $trak_alert?>{{ $user_name }} <br>{{ $row->user_phone }} <br><small>${{$row->amount}} {{$row->fk_Coupon}}</small></td>
                    <td>{{{ $row->number }}} {{{ $row->street }}} {{{ $row->city }}}, {{{ $row->state }}} {{{ $row->zip }}}<br><small>{{ $row->user_email }}</small></td>
                    <td>
                        <?php
                        $created = Carbon::parse($row->order_created_at, 'UTC');
                        $completed = Carbon::parse($row->order_updated_at, 'UTC');
                        $durationDiff = $created->diff($completed);
                        $duration = $durationDiff->format('%hhrs %imins');
                        
                        $durationStyle = 'style="background-color:#5cb85c"';
                        
                        // Was it late?
                        $durationHours = $durationDiff->format('%h');
                        $durationMins = $durationDiff->format('%i');
                        $durationHoursAsMins = $durationHours*60;
                        $durationTotal = $durationHoursAsMins + $durationMins;
                        
                        if ($durationTotal > $row->eta_max)
                            $durationStyle = 'style="background-color:#d9534f"';
                        ?>
                        <small class="utcToLoc">{{$row->order_created_at}}</small> 
                        <br><small>{{$row->eta_min}}-{{$row->eta_max}}mins.</small> 
                        <br><small class="utcToLoc">{{$row->order_updated_at}}</small>
                        <br><small><span class="badge" {{$durationStyle}}>{{$duration}}</span></small>
                    </td>
                    <td align="center">
                        <?php echo $row->status;?><br>
                    </td>
                    <td><?php echo "$row->driver_name ($row->pk_Driver)";?></td>
                </tr>
                <tr>
                    <td colspan='7'>

                        <table class="table table-condensed">
                            <tbody>
                                <?php 
                                // Bentos
                                $boxCount = 1;
                                foreach ($bentoBoxes as $box) {
                                    ?>
                                    <tr>
                                      <th scope="row">Bento Box {{$boxCount}}: ${{$box->unit_price_paid}}</th>
                                      <td>{{$box->main_name}} - {{$box->main_label}}</td>
                                      <td>{{$box->side1_name}} - {{$box->side1_label}}</td>
                                      <td>{{$box->side2_name}} - {{$box->side2_label}}</td>
                                      <td>{{$box->side3_name}} - {{$box->side3_label}}</td>
                                      <td>{{$box->side4_name}} - {{$box->side4_label}}</td>
                                    </tr>
                                    <?php
                                    $boxCount++;
                                }
                                
                                // Addons
                                foreach ($addons as $addon) {
                                    ?>
                                    <tr>
                                      <td colspan="6">{{$addon->qty}}x {{$addon->name}}: ${{NumUtil::formatPriceForEmail($addon->qty * $addon->unit_price_paid)}} <small>({{$addon->qty}} @ ${{NumUtil::formatPriceForEmail($addon->unit_price_paid)}})</small></td>
                                    </tr>
                                    <?php
                                    $boxCount++;
                                }
                                ?>
                                    
                                <!-- // Totals -->
                                <tr><td colspan="6"><small>
                                    <b>Items Total:</b> ${{$row->items_total}} &nbsp;&nbsp;
                                    <b>Delivery fee:</b> ${{$row->delivery_price}} &nbsp;&nbsp;
                                    <b>Coupon discount:</b> -${{$row->coupon_discount}} &nbsp;&nbsp;
                                    <b>Tax:</b> ${{$row->tax}} ({{(float) $row->tax_percentage}}%) &nbsp;&nbsp;
                                    <b>Tip:</b> ${{$row->tip}} ({{(float) $row->tip_percentage}}%) &nbsp;&nbsp;
                                    <b>Total:</b> ${{$row->amount}} &nbsp;&nbsp;
                                    <i><b>Pre-Promo Total:</b> ${{NumUtil::formatPriceFromCents($row->total_cents_without_coupon)}}</i> &nbsp;&nbsp;
                                    <b>OS:</b> {{$row->platform}} &nbsp;&nbsp;
                                </small></td></tr>
                            </tbody>
                        </table>
                    </td>
                    <!-- <td></td><td></td> -->
                </tr>
                <?php
            }
            catch(\Exception $e) {
                echo "<div><span class='label label-danger'><big>Error with order #$row->pk_Order. Admin is alerted.</big></span><div></tr>";
                Bento::alert($e, "[HIGH] Corrupted Order: #$row->pk_Order", 'efe1c6d7-1085-4855-951b-7e45885f0d41');
            }
        }
        ?>
    </tbody>
</table>

<?php
else:
    echo '<div class="alert alert-info" role="alert">Nothing here yet.</div>';
endif;

}
catch(\Exception $e) {
    Bento::alert($e, '[CRITICAL] DASHBOARD MAIN IS DOWN', '70367898-ef11-40f7-a81c-ad3e3ea8a0ed');
    echo '<div class="alert alert-danger" role="alert">'
    . '<b>[CRITICAL ERROR]</b> '
    . 'The dashboard has encountered an Exception. It may be partially broken. The Admin has been paged!</div>';
}

?>