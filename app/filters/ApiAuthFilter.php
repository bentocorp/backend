<?php

namespace Bento\Filter;

use Input;
use Response;
use User;

class ApiAuthFilter {

    public function filter() {
        
        // We have a token
        if (Input::has('api_token')) {
            $api_token = Input::get('api_token');

            // Get the User
            $user = User::getUserByApiToken($api_token);
            
            #var_dump($user->count()); die();

            // Return if incorrect
            if ($user->count() != 1)
                return Response::make('Unauthorized', 401);
            else
                User::set($user[0]);
        }
        // Not logged in
        else {
            return Response::make('Unauthorized', 401);
        }
    }

}