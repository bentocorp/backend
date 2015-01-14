<?php

namespace Bento\Admin\Ctrl;

use View;


class ApiTestCtrl extends \BaseController {

    public function getIndex() {
        return View::make('admin.apitest.index');
    }
    
    
    
}
