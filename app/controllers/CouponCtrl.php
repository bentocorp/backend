<?php

namespace Bento\Ctrl;

use Bento\Model\CouponRequest;
use Response;
use Input;
use User;


class CouponCtrl extends \BaseController {
    
    
    public function getApply($code) {
        
        if ($code == '1121113370998kkk7') 
            return Response::json(array('amountOff' => '12.00'), 200); 
        else
            return Response::json(array('error' => 'Invalid coupon.'), 400); 
    }
    
    
    public function postRequest() {
        
        $fk_User = NULL;
        $data = json_decode(Input::get('data'));
        
        // This is a logged in user
        if (Input::has('api_token')) {
            $api_token = Input::get('api_token');

            // Get the User
            $user = User::getUserByApiToken($api_token);

            // set fk
            if ($user !== NULL)
                $fk_User = $user->pk_User;
        }
        
        // Insert into DB
        $cr = new CouponRequest;
        
        $cr->fk_user = $fk_User;
        $cr->reason = $data->reason;
        $cr->email = $data->email;
        
        $cr->save();
        
        return Response::json('', 200);
    }
    
    
    
}
