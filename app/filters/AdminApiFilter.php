<?php namespace Bento\Filter;


use Bento\Admin\Model\AdminUser;
use Bento\app\Bento;
use Input;
use Response;


class AdminApiFilter {

    public function filter() {
        
        // We have a token
        if (Input::has('api_token')) {
            $api_token = Input::get('api_token');

            // Get the User
            $user = AdminUser::getAdminUserByApiToken($api_token);
            
            // Return if incorrect
            if ($user === NULL)
                return Response::make('Unauthorized', 401);
            // Otherwise, all good
            else {
                Bento::setAsAdminApiRequest();
                AdminUser::set($user);
            }
        }
        // Not logged in
        else {
            return Response::make('Unauthorized', 401);
        }
    }

}