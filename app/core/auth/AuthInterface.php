<?php

namespace Bento\Auth;


interface AuthInterface {
    
    public static function signup($data);
    
    public static function signin($data);
    
}

