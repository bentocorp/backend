<?php

namespace Bento\Admin\Ctrl;

use View;


class ApiTestCtrl extends \BaseController {

    private $data = array();
    
    
    public function __construct() {
        // Nav
        $this->data['nav8'] = true;
    }
    
    
    public function getIndex() {
        
        $data = $this->data;
        
        return View::make('admin.apitest.index', $data);
    }
    
    
    
}
