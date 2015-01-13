<?php

namespace Bento\Filter;

use Session;
use Redirect;

class AdminFilter {

    public function filter() {
        
        if (Session::has('isAdminLoggedIn')){
            // Return nothing to execute the route
            #return Redirect::to('admin/');
        }
        else {
            return Redirect::to('admin/login');
        }
    }

}