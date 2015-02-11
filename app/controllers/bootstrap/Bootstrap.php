<?php

namespace Bento\Ctrl;

Use Hash;
Use Crypt;

class BootstrapCtrl extends \BaseController {


    public function do1() {
        
        #$password = Hash::make('pass');
        $password = Crypt::encrypt('myfbtoken');
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
