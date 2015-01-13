<?php

namespace Bento\Admin\Ctrl;

use Input;
use DB;
use Redirect;
use Hash;
use Session;

class UserCtrl extends \BaseController {

    public function login() {
        
        // Get from form
        $username = Input::get('username');
        $password = Input::get('password');
        
        // Get from DB
        $sql = "select * from admin_User where username = ?";
        $user = DB::select($sql, array($username));
        
        // User not found
        if (count($user) != 1)
            return Redirect::to('admin/login');
        // User found
        else {
            $user = $user[0];

            // Good password
            if (Hash::check($password, $user->password)) {
                Session::put('isAdminLoggedIn', true);
                return Redirect::to('admin/');
            }
            // Bad password
            else {
                die('bad password');
                return Redirect::to('admin/login');
            }
        }
    }
    
}
