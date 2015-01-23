<?php

namespace Bento\Admin\Ctrl;

#use Bento\Admin\Model\Misc;
use View;
use DB;


class MiscCtrl extends \BaseController {

    public function getIoscopy() {
        
        $data = array();
        
        $iosCopy = DB::select('SELECT * FROM admin_ios_copy order by `key` asc', array());
        $data['iosCopy'] = $iosCopy;
        
        return View::make('admin.ioscopy', $data);
    }
    
    
    
}
