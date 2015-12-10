<?php
namespace Bento\Auth;

use Bento\Payment\StripeMgr;
use Validator;
use Response;
use User;
use Hash;


class MainAuthSvc implements AuthInterface {
    
        
    public function signup($data) {
        
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
            $api_token = $this->makeApiToken($data->email);
            
            
            // Get the name
            $name = trim($data->name);
            
            // Slice apart the single `name` field
            $nameAr = explode(' ', $name);
            
            if ( count($nameAr) > 1 ) {
            // Assume that the last chunk is their last name
                $lastname = array_pop($nameAr);
                $firstname = implode(' ', $nameAr);
            }
            else {
            // Otherwise there's only one thing typed, and assume it as their first name
                $firstname = $name;
                $lastname = '';
            }
            
            
            // Put user into DB
            $user = new User;
            $user->api_token    = $api_token;
            $user->firstname    = $firstname;
            $user->lastname     = $lastname;
            $user->email        = $data->email;
            $user->phone        = $data->phone;
            $user->password     = Hash::make($data->password);
            $user->save();
            
            // Good candidate for async queue
            $user->coupon_code  = $user->makeCouponCode();
            $user->save();
            
            $response = array('api_token' => $api_token);
            
            return Response::json($response, 200);
        }
    }
    
    
    public function login($data) {
        
        // Try to get user from DB
        $userSingleton = User::getUserForLogin($data->email);
        $user = unserialize(serialize($userSingleton)); // clone
                
        // User not found
        if ($user == NULL)
            return Response::json(array('error' => "We don't have your email on file."), 404);
        // User found
        else { // <-- Refactor this crazy thing
            $user = $user[0];
            #$this->sentUser = $user;
            
            // Good password
            if (Hash::check($data->password, $user->password)) {
                // Remove things from the return!
                unset($user->password, $user->pk_User);
                
                // Set new api_token
                $api_token = $this->makeApiToken($user->email);
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
    
    
    public function makeApiToken($email) {
        // Make their secret token
        $toHash = $email . time() .  mt_rand(10000 , 99999);
        $api_token = Hash::make($toHash);
        
        return $api_token;
    }
    
    
}
