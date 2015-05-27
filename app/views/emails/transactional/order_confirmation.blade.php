<?php
use Bento\Admin\Model\Orders;

?>
<p>
  Hi {{$user->firstname}}. Thanks for ordering from Bento!<br>
  <br>
  Your delivery address is: <b>{{$order->number}} {{$order->street}}</b><br>
  Your phone number is: <b>{{$user->phone}}</b><br>
  <br>
  If anything is incorrect, please reply to this email or give us a call: 415-300-1332.<br>
  <br>
  <b>+++ Note, we're now serving lunch! :D +++</b><br>
  <br>
  Once your order is en route, we'll send you text message updates. 
  Enjoy your meal, and please feel free to reply directly to this email with any feedback you may have.<br>
  <br>
  =======<br>
  Give your friends $5 off their next Bento! Use your code: <b>{{$user->coupon_code}}</b>. It works once for you too.<br>
  =======
</p>


<h4>Your Order</h4>

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

<br>
    
<div>Sub total: ${{number_format($order->amount - $order->tax - $order->tip , 2)}}</div>
<div>Tax: ${{number_format($order->tax, 2)}}</div>
<div>Tip: ${{number_format($order->tip, 2)}}</div>
<div><b>Total: ${{number_format($order->amount, 2)}}</b></div>
