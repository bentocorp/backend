<?php
use Bento\Admin\Model\Orders;

?>
<p>
  Hi {{$user->firstname}}. Thanks for ordering from Bento! Once your order is en route, we'll send you a text message. 
  Enjoy your meal, and please feel free to reply directly to this email with any feedback you may have.<br>
  <br>
  Give your friends $5 off their next Bento! Use your code: <b>{{$user->coupon_code}}</b>. It works once for you too.
</p>


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

<div>Sub total: ${{number_format($order->amount - $order->tax - $order->tip , 2)}}</div>
<div>Tax: ${{number_format($order->tax, 2)}}</div>
<div>Tip: ${{number_format($order->tip, 2)}}</div>
<div><b>Total: ${{number_format($order->amount, 2)}}</b></div>
