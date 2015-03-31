<?php
namespace Bento\Auth;

use Bento\Facades\StripeMgr;
use Bento\Facades\MainAuth as MainAuthFcd;
use Facebook\FacebookSession;
use Validator;
use Response;
use User;


class FacebookAuth implements AuthInterface {
    
    private $sentUser;
    
    
    public function signup($data) {
        
        // Set instance vars
        $this->sentUser = $data;
        
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
                $fb_token = $this->getFbToken($data->fb_token, $this->sentUser->fb_id);
            }
            catch (\Exception $ex) {
                // Session not valid, Graph API returned an exception with the reason. OR:
                // Graph API returned info, but it may mismatch the current app or have expired.
                #echo $ex->getMessage();
                return Response::json(array('error' => $ex->getMessage()), 403);
            }
            
            // Everything good, save FB user to DB
            
            // Make their secret token
            $api_token = MainAuthFcd::makeApiToken($data->email);
            
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
            
            // Good candidate for async queue
            $user->coupon_code  = $user->makeCouponCode();
            $user->save();
            
            $response = array(
                'api_token' => $api_token,
                'fb_token'  => $fb_token,
            );
            
            return Response::json($response, 200);
        }
    }
    
    
    public function login($data) {
        
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
     * The purpose of this function is to validate the FB access token from the
     * mobile app, thereby returning a long-term FB token.
     * 
     * @param string $token The token provided by the frontend native app.
     * @return string Long term FB token
     * @throws Exception Throws a FacebookRequestException, or some other exception, if the token does not validate.
     */
    public function getFbToken($token, $fb_id) {
        
        FacebookSession::setDefaultApplication($_ENV['FB_app_id'], $_ENV['FB_app_secret']);
        
        // We have an access token from the mobile app
        $session = new FacebookSession($token);
        
        // This might throw an exception
        $session->validate();
        
        // Check if token matches the user
        $sessionInfo = $session->getSessionInfo();
        
        if ($fb_id != $sessionInfo->getId())
            throw new \FbMismatchedIdException("The fb_id for this fb_token and the provided fb_id don't match.");
        
        return $session->getToken();
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
            // Last resort: Try to get a new token from FB
            try {
              $fb_token = $this->getFbToken($data->fb_token, $this->sentUser->fb_id);
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
        $api_token = MainAuthFcd::makeApiToken($user->email);
        User::setNewApiToken($api_token, $user->email); // set to db
        $user->api_token = $api_token; // set to return object

        // Add Stripe info if it's there
        $user2 = StripeMgr::addStripeInfo($user);
        
        return $user2;
    }
    
    
}
