<?php

namespace Bento\Core;

use Facebook\FacebookSession;


class FacebookAuth {
    
    
    /**
     * The purpose of this function is to validate the FB access token from the
     * mobile app, thereby returning a long-term FB token.
     * 
     * @param string $token The token provided by the frontend native app.
     * @return string Long term FB token
     * @throws Exception Throws a FacebookRequestException, or some other exception, if the token does not validate.
     */
    public static function getFbToken($token, $fb_id) {
        
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
    
    
    
    
}
