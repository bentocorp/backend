<?php

namespace Bento\Ctrl;

use Bento\Coupon\AppCoupon;
use Bento\Model\CouponRequest;
use Response;
use Input;
use User;


class CouponCtrl extends \BaseController {
    
    /**
     * Apply a coupon on the frontend.
     * 
     * @param string $code The coupon code
     * @return json, HTTP status code
     */
    public function getApply($code) {
        
        $coupon = new AppCoupon;
        
        $isValidCoupon = $coupon->find($code);
        
        // If this coupon doesn't exist, we're done
        if ($isValidCoupon === false)
            return Response::json(array('error' => 'Invalid coupon.'), 400);
        
        // So it's at least a valid code... Is it valid for this user?
        
        // If the code is valid for the user, return the amount off
        if ($coupon->isValidForUser()) {
            
            $coupon->redeem();
            
            return Response::json(array('amountOff' => $coupon->getGiveAmount()), 200);
        }
        // if the code isn't valid for the user, return an error
        else
            return Response::json(array('error' => 'Invalid coupon for you.'), 400);
    }
    
    
    /**
     * Let the user request a coupon
     * 
     * @return json, HTTP status code
     */
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
