<?php

namespace Bento\Ctrl;

use Bento\Model\Status;
use Response;

class InitCtrl extends \BaseController {

    /**
     * Init the application
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
