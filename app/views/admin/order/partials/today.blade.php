
<!--
******************************************************************************
Today's Orders
******************************************************************************
-->
<?php
use Bento\Admin\Model\Orders;

$todaysOrders = Orders::getTodaysOrders();
?>
<h1>Today's Orders</h1>
@include('admin.order.partials.recent', array('recentOrders' => $todaysOrders))

