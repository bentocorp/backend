<?php

namespace Bento\Ctrl;

use Validator;
use Input;
use Response;
use Hash;
use User;
use DB;


class UserCtrl extends \BaseController {

    // Common validtion fields and rules
    #private $cmnValFields;
    #private $cmnValRules;
    
    #private $data;
    
    
    /**
     * Regular signup.
     * 
     * @return httpStatusCode 200|400
     */
    public function postSignup() {
        // Get data
        $data = json_decode(Input::get('data'));
        
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
                'password' => 'required|min:8',
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
            $user->reg_type     = 'none';
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
        
        // Setup validation
        $valFields = 
            array(
                'firstname' => $data->firstname,
                'lastname' => $data->lastname,
                'email' => $data->email,
                'phone' => $data->phone,
                'fb_id' => $data->fb_id,
                'fb_profile_pic' => $data->fb_profile_pic,
                #'fb_gender' => $data->fb_gender,
                #'fb_age' => $data->fb_age,
            );
        
        $valRules =
            array(
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:User',
                'phone' => 'required',
                'fb_id' => 'required',
                'fb_profile_pic' => 'required',
            );
        
        // Validate the FB data
        $validator = Validator::make($valFields, $valRules);
        
        // Bad input, throw errors
        if ($validator->fails()) {
            $messages = $validator->messages();
            
            return Response::json($messages->all(), 400);
        }
        // Good input, save FB user to DB
        else {
            // Make their secret token
            $api_token = $this->makeApiToken($data->email);
            
            // Put user into DB
            $user = new User;
            $user->api_token    = $api_token;
            $user->firstname    = $data->firstname;
            $user->lastname     = $data->lastname;
            $user->email        = $data->email;
            $user->phone        = $data->phone;
            $user->reg_type     = 'Facebook';
            $user->fb_id        = $data->fb_id;
            $user->fb_profile_pic    = $data->fb_profile_pic;
            $user->save();
            
            $response = array('api_token' => $api_token);
            
            return Response::json($response, 200);
        }
    }


    public function postLogin() {
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Try to get user from DB
        $sql = "SELECT email, phone, api_token, password
                FROM User WHERE email = ?";
        $user = DB::select($sql, array($data->email));
        
        
        // User not found
        if (count($user) != 1)
            return Response::json('', 404);
        // User found
        else {
            $user = $user[0];
            
            // Good password
            if (Hash::check($data->password, $user->password)) {
                // Remove password from the return
                unset($user->password);
                return Response::json($user, 200);
            }
            // Bad password
            else {
                return Response::json('', 403);
            }
        }
    }
    
    
    public function postFblogin() {
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Try to get user from DB
        $sql = "SELECT email, phone, api_token, fb_id
                FROM User WHERE email = ?";
        $user = DB::select($sql, array($data->email));
        
        
        // User not found
        if (count($user) != 1)
            return Response::json('', 404);
        // User found
        else {
            $user = $user[0];
            
            // Good Facebook Id
            if ($data->fb_id == $user->fb_id) {
                // Remove FB Id from the return
                unset($user->fb_id);
                return Response::json($user, 200);
            }
            // Bad Facebook Id
            else {
                return Response::json('', 403);
            }
        }
    }
    
    
    private function makeApiToken($email) {
        // Make their secret token
        $toHash = $email . time();
        $api_token = Hash::make($toHash);
        
        return $api_token;
    }
    
    
/*
   public function setupCommonValidtion() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        $this->data = $data;
        
        $this->cmnValFields = 
            array(
                'firstname' => $data->name,
                'lasttname' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'password' => $data->password,
            );
        
        $this->cmnValRules =
            array(
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:User',
                'phone' => 'required',
                'password' => 'required|min:8',
            );
    }
 * 
 */
    
}