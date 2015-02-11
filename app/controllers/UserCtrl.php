<?php

namespace Bento\Ctrl;

use Bento\Facades\FacebookAuth;
use Bento\Facades\MainAuth;
use Bento\Facades\StripeMgr;
use Validator;
use Input;
use Response;
use Hash;
use User;


class UserCtrl extends \BaseController {

    
    private $sentUser;
    private $newUser;
    
    
    public function __construct(User $newUser) {
        
        $this->newUser = $newUser;
    }
    
    
    private function checkExists() {
        
        return User::exists($this->sentUser->email);
    }
    
    
    /**
     * Regular signup.
     * 
     * @return httpStatusCode 200|400
     */
    public function postSignup() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        $this->sentUser = $data;
        
        // Check user doesn't already exist
        $existingUser = $this->checkExists();
        
        if ($existingUser)
            return Response::json(array('error' => 'This email is already registered.'), 409);
        
        // Setup validation
        $valFields = 
            array(
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'password' => $data->password,
            );
        
        $valRules =
            array(
                'name' => 'required',
                'email' => 'required|email|unique:User',
                'phone' => 'required',
                'password' => 'required|min:6',
            );
        
        
        // Validate the data
        $validator = Validator::make($valFields, $valRules);
        
        // Bad input, throw errors
        if ($validator->fails()) {
            $messages = $validator->messages();
            
            return Response::json($messages->all(), 400);
        }
        // Good input, save to DB
        else {
            // Make their secret token
            $api_token = MainAuth::makeApiToken($data->email);
            
            // Slice apart the single `name` field
            // Assume that the last chunk is their last name
            $name = $data->name;
            $nameAr = explode(' ', $name);
            $lastname = array_pop($nameAr);
            $firstname = implode(' ', $nameAr);
            
            
            // Put user into DB
            $user = new User;
            $user->api_token    = $api_token;
            $user->firstname    = $firstname;
            $user->lastname     = $lastname;
            $user->email        = $data->email;
            $user->phone        = $data->phone;
            $user->password     = Hash::make($data->password);
            $user->save();
            
            $response = array('api_token' => $api_token);
            
            return Response::json($response, 200);
        }
    }
    
    
    /**
     * FB Signup.
     * 
     * @return httpStatusCode 200|400
     */
    public function postFbsignup() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        $this->sentUser = $data;
        
        // Check user doesn't already exist
        $existingUser = $this->checkExists();
        
        if ($existingUser)
            return Response::json(array('error' => 'This email is already registered.'), 409);
        
        // Do it
        return FacebookAuth::signup($data);
    }


    public function postLogin() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Try to get user from DB
        $userSingleton = User::getUserForLogin($data->email);
        $user = unserialize(serialize($userSingleton)); // clone
                
        // User not found
        if ($user == NULL)
            return Response::json(array('error' => "We don't have your email on file."), 404);
        // User found
        else { // <-- Refactor this crazy thing
            $user = $user[0];
            $this->sentUser = $user;
            
            // Good password
            if (Hash::check($data->password, $user->password)) {
                // Remove password from the return!
                unset($user->password);
                
                // Set new api_token
                $api_token = MainAuth::makeApiToken($user->email);
                User::setNewApiToken($api_token, $user->email); // set to db
                $user->api_token = $api_token; // set to return object
                
                // Add Stripe info if it's there
                $user = StripeMgr::addStripeInfo($user);
                
                return Response::json($user, 200);
            }
            // Bad password
            else {
                return Response::json(array('error' => 'Your password is incorrect.'), 403);
            }
        }
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
    
        
}
