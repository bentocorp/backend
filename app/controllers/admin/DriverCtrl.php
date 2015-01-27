<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Driver;
use Redirect;


class DriverCtrl extends AdminBaseController {

    public function postSaveInventory($pk_Driver) {
        
        $data = $_POST;
                
        // Save the new data
        Driver::overwriteInventory($pk_Driver, $data);
        
        return Redirect::back()->with('msg', array(
            'type' => 'success', 
            'txt' => 'Driver inventory <b>AND</b> Live Inventory updated.'));
    }
    
}
