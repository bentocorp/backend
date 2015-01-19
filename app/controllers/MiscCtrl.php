<?php

namespace Bento\Ctrl;

#use Bento\Admin\Model\Misc;
use DB;
use Response;


class MiscCtrl extends \BaseController {

    public function getIoscopy() {
               
        $iosCopy = DB::select('SELECT `key`, `value`, `type` FROM admin_ios_copy', array());
        
        return Response::json($iosCopy);
    }
    
    
    
}
