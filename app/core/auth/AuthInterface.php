<?php

namespace Bento\Auth;


interface AuthInterface {
    
    public function signup($data);
    
    public function login($data);
    
}

