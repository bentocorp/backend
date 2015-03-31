<?php

namespace Bento\Ctrl;

Use Hash;
Use Crypt;

class BootstrapCtrl extends \BaseController {


    public function do1() {
        
        $password = Hash::make('mypass');
        #$password = Crypt::encrypt('myfbtoken');
        echo $password;
        
        /*
        if (Hash::check('pass', 'encrypted'))
        {
            echo' The passwords match...';
        }
         * 
         */
    }
    
    
    public function do2() {
        
$obj = <<<obj
{
    "id": "cus_5adBhCXqKooCud",
    "object": "customer",
    "created": 1422336585,
    "livemode": false,
    "description": "vcardillo+2@gmail.com",
    "email": null,
    "delinquent": false,
    "metadata": [],
    "subscriptions": {
        "object": "list",
        "total_count": 0,
        "has_more": false,
        "url": "\/v1\/customers\/cus_5adBhCXqKooCud\/subscriptions",
        "data": []
    },
    "discount": null,
    "account_balance": 0,
    "currency": null,
    "cards": {
        "object": "list",
        "total_count": 1,
        "has_more": false,
        "url": "\/v1\/customers\/cus_5adBhCXqKooCud\/cards",
        "data": [
            {
                "id": "card_15PRumEmZcPNENoGBE10Xn68",
                "object": "card",
                "last4": "7777",
                "brand": "Visa",
                "funding": "credit",
                "exp_month": 8,
                "exp_year": 2016,
                "fingerprint": "17q5Yg3Z9JISQiVd",
                "country": "US",
                "name": null,
                "address_line1": null,
                "address_line2": null,
                "address_city": null,
                "address_state": null,
                "address_zip": null,
                "address_country": null,
                "cvc_check": null,
                "address_line1_check": null,
                "address_zip_check": null,
                "dynamic_last4": null,
                "customer": "cus_5adBhCXqKooCud"
            }
        ]
    },
    "default_card": "card_15PRumEmZcPNENoGBE10Xn68"
}
obj;

    $obj2 = json_decode($obj);

    $encoded = base64_encode( serialize($obj2) );

    echo $encoded;
    
    #print_r( unserialize(base64_decode($encoded)) );
        
    }
    
    
    public function do3() {
        
        $string = '!@_#john ń Æ ._ 8!';
        
        #$str2 = preg_replace('~[^\p{L}\p{N}]++~u', '', $string);
        $userCouponCode = preg_replace('/[^a-zA-Z0-9]+/', '', $string);
        
        $length = strlen($userCouponCode);
        
        /*
         * Ld = length desired, La = length actual
         * min: 1 * 10^(Ld-La-1)
         * max: 1 * 10^(Ld-La) -1 
         * e.g.: 1*10^0 to 1*(10^1) - 1, gives us 1-9.
         */
        if ($length < 6) {
            // This many more numbers
            $min = 1 * 10**(6 - $length - 1);
            $max = 10**(6 - $length);
            
            $r = rand($min, $max);
            
            $userCouponCode .= "b$r";
        }
        
        echo $userCouponCode;
    }

}
