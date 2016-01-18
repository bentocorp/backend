<?php

namespace Bento\Ctrl;

use Bento\Auth\FacebookAuth;
use Bento\Auth\MainAuth;
use Input;
use Response;
use User;


class UserCtrl extends \BaseController {

    
    #private $sentUser;
    #private $newUser;
    
    
    #public function __construct(User $newUser) {
        
    #    $this->newUser = $newUser;
    #}
    
    
    private function checkExists($user) {
        
        return User::exists($user->email);
    }
    
    
    /**
     * Regular signup.
     * 
     * @return httpStatusCode 200|400
     */
    public function postSignup() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        #$this->sentUser = $data;
        
        // Check user doesn't already exist
        $existingUser = $this->checkExists($data);
        
        if ($existingUser)
            return Response::json(array('error' => 'This email is already registered.'), 409);
        
        // Otherwise, Do it
        return MainAuth::signup($data);
    }
    
    
    /**
     * FB Signup.
     * 
     * @return httpStatusCode 200|400
     */
    public function postFbsignup() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Check user doesn't already exist
        $existingUser = $this->checkExists($data);
        
        if ($existingUser)
            return Response::json(array('error' => 'This email is already registered.'), 409);
        
        // Otherwise, Do it
        return FacebookAuth::signup($data);
    }


    public function postLogin() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Do it
        return MainAuth::login($data);
    }
    
    
    public function postFblogin() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Do it
        return FacebookAuth::login($data);
    }
    
    
    public function getLogout() {
        
        try {
            User::logout();
        }   catch (\Exception $e) {
            return Response::json(array('error' => 'User not found.'), 404);
        }
        
        return Response::json('', 200);
    }
    
    
    public function getInfo() {
        
        // As this is an authenticated route, if we're here, we already have the user in memory
        $user = User::get();
        
        $return = array(
            "firstname" => $user->firstname,
            "lastname" => $user->lastname,
            "email" => $user->email,
            "phone" => $user->phone,
            "coupon_code" => $user->coupon_code,
        );
        
        return Response::json($return, 200);
    }
    
    
    /**
     * Update the user's phone number
     * @param phone $newPhone
     * @return httpStatus
     */
    public function postPhone() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        $newPhone = $data->new_phone;
        
        $user = User::get();
        $user->phone = $newPhone;
        $user->save();
        
        return Response::json('', 200);
    }
    
    
    /*
     * Get a user's upcoming orders, and their last 10 completed orders
     */
    public function getOrderhistory()
    {
        
    }
    
        
}
