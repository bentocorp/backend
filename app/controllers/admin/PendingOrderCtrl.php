<?php

namespace Bento\Admin\Ctrl;

use Bento\Model\PendingOrder;
use Redirect;
use View;


class PendingOrderCtrl extends AdminBaseController {

    public function getIndex() {
        
        $data = array();
        $pendingOrders = PendingOrder::withTrashed()->orderBy('created_at', 'desc')->take(100)->get();
        
        $data['pendingOrders'] = $pendingOrders;
        
        return View::make('admin.pendingorder-index', $data);
    }
     
    
    public function getDelete($pk) {
        $pendingOrder = PendingOrder::find($pk);
        $pendingOrder->delete();
        
        return Redirect::back()->with('msg', array('type' => 'success', 'txt' => 'Pending Order marked as deleted.'));
    }
    
}
