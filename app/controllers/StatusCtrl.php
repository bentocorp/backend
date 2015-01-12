<?php

namespace Bento\Ctrl;

use Bento\Model\Status;
use Response;

class StatusCtrl extends \BaseController {

    /**
     * Get current overall restaurant status
     * 
     * @return json 
     */
    public function getOverall() {
                
        $status = Status::overall();
        
        return Response::json($status);
    }
    
    
    /**
     * Get current status of each menu item
     * 
     * @return json 
     */
    public function getMenu() {
                
        $status = Status::menu();
        
        return Response::json($status);
    }

}
