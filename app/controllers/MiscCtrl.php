<?php

namespace Bento\Ctrl;

#use Bento\Admin\Model\Misc;
use DB;
use Response;
#use Bento\Admin\Model\Settings;


class MiscCtrl extends \BaseController {

    public function getIoscopy() {
               
        $iosCopy = DB::select('SELECT `key`, `value`, `type` FROM admin_ios_copy', array());
        
        return Response::json($iosCopy);
    }
    
    
    public function getServicearea() {
        
        $serviceArea = DB::select('select * from settings where `key` in (?,?)', array('serviceArea_lunch', 'serviceArea_dinner'));
        
        $serviceAreaAr = array();
        
        foreach ($serviceArea as $row) {
            $serviceAreaAr[$row->key] = $row;
        }
        
        return Response::json($serviceAreaAr);
    }
    
    
    
}
