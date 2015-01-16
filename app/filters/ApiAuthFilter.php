<?php

namespace Bento\Filter;

use Input;
use Response;
use DB;
use User;

class ApiAuthFilter {

    public function filter() {
        
        // We have a token
        if (Input::has('api_token')) {
            $api_token = Input::get('api_token');

            // Get the User
            $sql = 'SELECT * FROM User WHERE api_token = ?';
            $user = DB::select($sql, array($api_token));

            // Return if incorrect
            if (count($user) != 1)
                Response::make('Unauthorized', 401);
            else
                User::set($user[0]);
        }
        // Not logged in
        else {
            return Response::make('Unauthorized', 401);
        }
    }

}