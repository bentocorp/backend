<?php

namespace Bento\Admin\Ctrl;

use View;
use User;
use Session;
use Redirect;


class UserCtrl extends AdminBaseController {

    public function getIndex() {
        
        $data = array();
        $users = User::all();
        
        $data['users'] = $users;
        
        return View::make('admin.user.index', $data);
    }
     
    
    public function getImpersonate($id) {
        $user = User::find($id);
        
        Session::put('api_token', $user->api_token);
        Session::put('api_impersonating', $user);
        
        $txt = "Impersonating $user->email";
        
        return Redirect::to('admin/user')->with('msg', array('type' => 'success', 'txt' => $txt));
    }

    
    public function getLogout() {
        Session::forget('api_token');
        Session::forget('api_impersonating');
        return Redirect::back();
    }
    
}
