<?php
use Bento\Admin\Model\Orders;
?>


<p>Thanks for ordering from Bento! Once your order is en route, we'll send you a text message. 
  Enjoy your meal, and please feel to reply directly to this email with any feedback you may have.</p>


<h4>Order</h4>

<?php 

$boxCount = 1;

foreach ($bentoBoxes as $box) {
    ?>
    <tr>
      Bento Box {{$boxCount}}: 
      {{{$box->main_name}}}, 
      {{{$box->side1_name}}}, 
      {{{$box->side2_name}}}, 
      {{{$box->side3_name}}}, 
      {{{$box->side4_name}}}
    </tr>
    <?php
    $boxCount++;
}
?>


<h4>Total</h4>

<div>Sub total: ${{$order->amount - $order->tax - $order->tip}}</div>
<div>Tax: ${{$order->tax}}</div>
<div>Tip: ${{$order->tip}}</div>
<div><b>Total: ${{$order->amount}}</b></div>
