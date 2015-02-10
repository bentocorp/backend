<?php

namespace Bento\Ctrl;

use Bento\Facades\FacebookAuth;
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
        // Good input
        else {
            // Verify FB token. To validate the session:
            try {
                $fb_token = FacebookAuth::getFbToken($data->fb_token, $this->sentUser->fb_id);
            }
            catch (\Exception $ex) {
                // Session not valid, Graph API returned an exception with the reason. OR:
                // Graph API returned info, but it may mismatch the current app or have expired.
                #echo $ex->getMessage();
                return Response::json(array('error' => $ex->getMessage()), 403);
            }
            
            // Everything good, save FB user to DB
            
            // Make their secret token
            $api_token = $this->makeApiToken($data->email);
            
            // Put user into DB
            $user = new User;
            $user->api_token        = $api_token;
            $user->firstname        = $data->firstname;
            $user->lastname         = $data->lastname;
            $user->email            = $data->email;
            $user->phone            = $data->phone;
            $user->reg_type         = 'Facebook';
            $user->fb_id            = $data->fb_id;
            $user->fb_token         = $fb_token; // This is treated as a password
            $user->fb_profile_pic   = $data->fb_profile_pic;
            $user->fb_age_range     = $data->fb_age_range;
            $user->fb_gender        = $data->fb_gender;
            $user->save();
            
            $response = array(
                'api_token' => $api_token,
                'fb_token'  => $fb_token,
            );
            
            return Response::json($response, 200);
        }
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
                $api_token = $this->makeApiToken($user->email);
                User::setNewApiToken($api_token, $user->email); // set to db
                $user->api_token = $api_token; // set to return object
                
                // Add Stripe info if it's there
                $user = $this->addStripeInfo($user);
                
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
        
        // Try to get user from DB
        $userSingleton = User::getFbUserForLogin($data->email);
        $user = unserialize(serialize($userSingleton)); // clone
        
        // User not found
        if ($user == NULL)
            return Response::json(array('error' => "We don't have your email on file."), 404);
        // User found
        else {
            $this->sentUser = $user[0];
            
            return $this->fbProcessLoginUser($this->sentUser, $data);
        }
    }
    
    
    /**
     * Process the user that we've found in the DB for FB login
     */
    private function fbProcessLoginUser($user, $data) {
        
        // Good Existing Facebook Token
        if ($data->fb_token == $user->fb_token) {

            $user = $this->getFbLoginSuccessUser($user);

            return Response::json($user, 200);
        }
        // Bad Existing Facebook Token
        else {
            #die('bad token');
            // Last resort: Try to get a new token from FB
            try {
              $fb_token = FacebookAuth::getFbToken($data->fb_token, $this->sentUser->fb_id);
            } catch (\Exception $ex) {
              return Response::json(array('error' => $ex->getMessage(), 'source' => 'Facebook'), 403);
            }
            
            // All good at this point. Last resort worked.
            
            $user = $this->getFbLoginSuccessUser($user);
            
            // Set the new fb_token to the return obj
            $user->fb_token = $fb_token;
            
            // Encrypt and save the new fb_token to the DB.
            // We get a separate user obj so as not to muck with the return.
            $dbuser = User::get();
            #var_dump($dbuser); die('here');
            $dbuser->fb_token = $fb_token; // This is treated as a password
            $dbuser->save();

            return Response::json($user, 200);
        }
    }
    
    
    /**
     * FB login is successful, so prep the DB and user obj accordingly.
     * 
     * @param obj $user
     * @return obj User
     */
    private function getFbLoginSuccessUser($user) {
        
        // Remove unwanted items from the return
        unset($user->pk_User, $user->fb_id, $user->fb_token);

        // Set new api_token
        $api_token = $this->makeApiToken($user->email);
        User::setNewApiToken($api_token, $user->email); // set to db
        $user->api_token = $api_token; // set to return object

        // Add Stripe info if it's there
        $user2 = $this->addStripeInfo($user);
        
        return $user2;
    }
    
    
    private function makeApiToken($email) {
        // Make their secret token
        $toHash = $email . time();
        $api_token = Hash::make($toHash);
        
        return $api_token;
    }
        
    
    public function getLogout() {
        
        try {
            User::logout();
        }   catch (\Exception $e) {
            return Response::json(array('error' => 'User not found.'), 404);
        }
        
        return Response::json('', 200);
    }
    
    
    private function addStripeInfo($user) {
        
        if ($user->stripe_customer_obj !== false) {
            $cu = $user->stripe_customer_obj;
            #var_dump($user); die();
            $card = $cu->cards->data[0];
            
            $user->card = new \stdClass();
            $user->card->brand = $card->brand;
            $user->card->last4 = $card->last4;
        }
        else {
            $user->card = NULL;
        }
        
        unset($user->stripe_customer_obj);
        
        return $user;
    }
    

        
}
