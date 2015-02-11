<?php
namespace Bento\Auth;

use Hash;


class MainAuth implements AuthInterface {
    
        
    public function signup($data) {
        
    }
    
    
    public function login($data) {
        
    }
    
    
    public function makeApiToken($email) {
        // Make their secret token
        $toHash = $email . time();
        $api_token = Hash::make($toHash);
        
        return $api_token;
    }
    
    
}
