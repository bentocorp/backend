<?php

namespace Bento\Ctrl;

Use Hash;

class BootstrapCtrl extends \BaseController {


    public function do1() {
        
        $password = Hash::make('pass');
        echo $password;
        
        /*
        if (Hash::check('pass', 'encrypted'))
        {
            echo' The passwords match...';
        }
         * 
         */
    }

}
