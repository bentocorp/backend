<?php

namespace Bento\Filter;

use Input;
use Response;
use DB;

class ExtApiFilter {

    public function filter() {
        #return;
        // We have a token
        if (Input::has('api_username') && Input::has('api_password')) {
            
            $api_username = Input::get('api_username');
            $api_password = Input::get('api_password');

            // Get the User
            $user = DB::select('select * from api_User where api_username = ? AND api_password = ?', 
                    array($api_username, $api_password));
            #var_dump($user); die();
            
            // Return if incorrect
            if (count($user) == 0)
                return Response::make('Unauthorized User', 401);
        }
        // Not logged in
        else {
            return Response::make('Unauthorized', 401);
        }
    }

}