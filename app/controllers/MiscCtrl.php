<?php

namespace Bento\Ctrl;

#use Bento\Admin\Model\Misc;
use DB;
use Response;
use Bento\Admin\Model\Settings;


class MiscCtrl extends \BaseController {

    public function getIoscopy() {
               
        $iosCopy = DB::select('SELECT `key`, `value`, `type` FROM admin_ios_copy', array());
        
        #var_dump($iosCopy); die();
        $iosCopy[34]->value = 'Please Upgrade for Deliciousness!';
        $iosCopy[33]->value = 'An update is available in the App Store.';
        
        return Response::json($iosCopy);
    }
    
    
    public function getServicearea() {
        
        $serviceArea = Settings::find('serviceArea');
        
        return Response::json($serviceArea);
    }
    
    
    
}
