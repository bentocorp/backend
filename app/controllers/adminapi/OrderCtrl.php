<?php namespace Bento\AdminApi\Ctrl;


use Bento\Admin\Ctrl\OrderCtrl as AdminOrderCtrl;
use Response;
use DB;


class OrderCtrl extends \BaseController {

    
    public function getAssign($pk_Order, $pk_Driver = 0, $after = 0) {
        
        #$pk_Order = Input::get('orderId');
        #$pk_Driver = Input::get('driverId');
        #$after = Input::get('after');
        
        DB::transaction(function() use ($pk_Order, $pk_Driver, $after)
        {
            # 1. Assign the order to the Driver
            
            $adminOrderCtrl = new AdminOrderCtrl;

            $_POST['pk_Driver'] = array('new' => $pk_Driver);

            $adminOrderCtrl->postSetDriver($pk_Order);
            
            # 2. Set the order_queue properly
            // ToDo: Solve duplicity / idempotency (make sure you don't set an order into their queue twice)
            
            $driver = DB::select('select * from Driver where pk_Driver = ? FOR UPDATE', array($pk_Driver))[0];
            
            $orderQueueAr = array();
            
            if ($driver->order_queue !== NULL)
                $orderQueueAr = explode(',' , $driver->order_queue);
            
            $orderQueueAr[] = $pk_Order;
            
            $newOrderQueue = implode(',' , $orderQueueAr);
            
            DB::update('update Driver set order_queue = ? where pk_Driver = ?', array($newOrderQueue, $pk_Driver));
        });
        
        return Response::json('', 200);
    }
    
}
