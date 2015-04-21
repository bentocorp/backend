<?php
namespace Bento\Payment;


class StripeMgrSvc {
    
    
    public function addStripeInfo($user) {
        
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