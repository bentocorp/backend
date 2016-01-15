
<!--
******************************************************************************
Today's Orders
******************************************************************************
-->
<?php
use Bento\Admin\Model\Orders;

$todaysOrders = Orders::getTodaysOrders();
?>
@include('admin.order.partials.recent', array('recentOrders' => $todaysOrders, 'pageTitle' => "Today's Orders"))

