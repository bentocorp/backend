<?php

namespace Bento\Admin\Ctrl;

use Bento\Admin\Model\Status;
use Redirect;



class StatusCtrl extends \BaseController {

        
    public function getOpen() {
        
        Status::open();
        
        return Redirect::back();
            #->with('msg', 
            #array('type' => 'success', 'txt' => '<b>Restaurant Open!</b>'));
    }
    
    
    public function getClosed() {
        
        Status::closed();
        
        return Redirect::back();
            #->with('msg', 
            #array('type' => 'danger', 'txt' => '<b>Restaurant Closed!</b>'));
    }
    
    
    public function getSoldout() {
        
        Status::soldout();
        
        return Redirect::back();
            #->with('msg', 
            #array('type' => 'warning', 'txt' => '<b>Restaurant Sold Out!</b>'));
    }
    
    
    
}
