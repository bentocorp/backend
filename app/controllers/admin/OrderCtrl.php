<?php

namespace Bento\Admin\Ctrl;

use Bento\Model\OrderStatus;
use Redirect;


class OrderCtrl extends \BaseController {

    
    public function postSaveStatus($pk_Order) {
        
        #if ($data === NULL)
        $data = $_POST;
        
        OrderStatus::saveStatus($pk_Order, $data);
        
        return Redirect::back()->with('msg', 
            array('type' => 'success', 'txt' => 'Order status updated.'));
    }
    
    
    
}
