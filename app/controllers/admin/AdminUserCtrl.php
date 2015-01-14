<?php

namespace Bento\Admin\Ctrl;

use Input;
use DB;
use Redirect;
use Hash;
use Session;

class AdminUserCtrl extends AdminBaseController {

    public function postLogin() {
        
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
                Session::put('adminUser', $user);
                return Redirect::to('admin/');
            }
            // Bad password
            else {
                die('bad password');
                return Redirect::to('admin/login');
            }
        }
    }
    
    
    public function getLogout() {
        Session::forget('isAdminLoggedIn');
        Session::forget('adminUser');
        return Redirect::to('admin/');
    }
    
}
