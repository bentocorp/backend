<?php namespace Bento\Tracking;


use User;


class TrakSvc {
    
    private $apiUrl;
    private $apiKey;
    private $organization;
    private $user;
    
    
    public function __construct() {
        
        $this->apiUrl = $_ENV['Trak_API'];
        $this->apiKey = $_ENV['Trak_key'];
        $this->organization = $_ENV['Trak_organization'];
        
        // Get the user
        $this->user = User::get();
    }
    
    
    public function test() {
        
        $url = $this->apiUrl . '/auth/test';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        #curl_setopt($ch, CURLOPT_POST, 1);
        #curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadName);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch);
        curl_close($ch);
        
        echo ($response); die();
    }
    
    
    /**
     * Encode a string for Trak
     * @param string $str
     * @return string Encoded string.
     */
    private function encodeStr($str) {
        
        if ($str === NULL || $str == '')
            return $str;
        
        // "Eggplant" results in "\"Eggplant\""
        $str2 = json_encode($str);
        
        // Remove the outer quotes
        $n = strlen($str2);
        
        return substr($str2, 1, $n-2);
    }
    
    
    /**
     * 
     * @deprecated
     */
    public function makeOrderString($bentoBoxes)
    {
        $orderStr = '';
        
        $boxCount = 1;
        
        $n = count($bentoBoxes);
        
        // If this is a Top Customer, tell the driver!
        $topCustomerStr = ">> ࿉∥(⋆‿⋆)࿉∥ Top Customer! << \\n\\n";
        if ($this->user->is_top_customer) {
            $orderStr .= $topCustomerStr;
        }

        // If this is NEW customer, tell the driver!
        $newCustomerStr = ">> *∥(◕‿◕)∥* 1st customer order!! << \\n\\n";
        if (!$this->user->has_ordered) {
            $orderStr .= $newCustomerStr;
        }

        foreach ($bentoBoxes as $box) {

            $main_name  = $this->encodeStr($box->main_name);
            $side1_name = $this->encodeStr($box->side1_name);
            $side2_name = $this->encodeStr($box->side2_name);
            $side3_name = $this->encodeStr($box->side3_name);
            $side4_name = $this->encodeStr($box->side4_name);
            
            $orderStr .= "旦 BENTO $boxCount of $n: \\n ===== \\n";
            $orderStr .= "$box->main_label - $main_name \\n";
            $orderStr .= "$box->side1_label - $side1_name \\n"; 
            $orderStr .= "$box->side2_label - $side2_name \\n";
            $orderStr .= "$box->side3_label - $side3_name \\n";
            $orderStr .= "$box->side4_label - $side4_name \\n ===== \\n\\n";
            
            $boxCount++;
        }
        
        // If this is a Top Customer, tell the driver!
        if ($this->user->is_top_customer) {
            $orderStr .= $topCustomerStr;
        }
        
        // If this is NEW customer, tell the driver!
        if (!$this->user->has_ordered) {
            $orderStr .= $newCustomerStr;
        }
        
        // Remind the drivers about accuracy, mochi, soy sauce, and chopsticks
        $orderStr .= ">> Is everything accurate? \\n\\n";
        $orderStr .= ">> Don't forget:\\n + mochi!\\n + to ask which type of soy sauce\\n + to offer utensils \\n\\n";
        
        return $orderStr;
    }
    
    
    public function addTask($order, $orderJson, $orderString) {
        
        // Add destination first
        $destinationId = $this->addDestination($order);
        #$destinationId = '';
        
        // Create recipient
        $recip = $this->createRecipient();
        
        $recipString = $recip === NULL ? '' : "\"$recip\""; 
        
        $url = $this->apiUrl . '/tasks';
                
        $payload = '
            {
                "merchant": "'.$this->organization.'",
                "executor": "'.$this->organization.'",
                "destination": "'.$destinationId.'",
                "recipients": ['.$recipString.'],
                "notes": "Order #'.$order->pk_Order.': \\n\\n'."$orderString".'"
            }
        ';
        #echo $payload; #
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . '');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        #curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        #echo $response; die(); #0
        
        $responseAr = array('response' => $response, 'info' => $info, 'payload' => $payload);
        
        return $responseAr;
    }
    
    
    private function addDestination($order) {
        
        $url = $this->apiUrl . '/destinations';
        
        /*
         * We are sending BOTH the user-entered address, AND the lat/long. Why? See below.
         * 
         * Email with Trak (Onfleet)
         * Subject: Onfleet Support Request Updated Re: Problem with geocoding
         * 
         * Trak:
            Regarding your other questions:

            Something must indeed still be provided for the required address fields 
            (Street, Number, City, etc.) but it can be anything if you provide a lat/lng 
            alongside it, as we would use that as the authority. In that case, we 
            don't try to geocode the address.

            We don't geocode if both are provided - we simply trust the lat/lng 
            you provide and use the address fields in the frontend only (the strings 
            will still show up in the driver app and dashboard as they are entered by the user).

            Hope this helps clarify! Let me know if any of this is unclear.
         
         * Me: 
            So, just to be clear, in that case (2), you'll use the lat/long as the 
            coordinates for the driver's destination. The user-entered address becomes for display only.
          
         * Trak:
            Precisely.

            One thing to note, however, is that when we pass the coordinates from 
            the driver app into Google Maps, or whichever navigation app your driver 
            is using, the app will reverse geocode to determine the address so it
            won't display the address you provided as the task destination, but 
            rather whatever their own reverse geocode comes up with. As such, 
            it's recommended that you just inform your drivers to go by the 
            address in the Onfleet driver app as the authority, but their navigation 
            app will direct them to the coordinate you provide.


         */
        
        $number = $order->number ? $order->number : '0';
        $street = $order->street ? $order->street : '0';
        $city = $order->city ? $order->city : '0';
        $state = $order->state ? $order->state : '0';
        
        $payload = '
            {
                "address": {
                    "number":"'.$number.'",
                    "street":"'.$street.'",
                    "apartment":"",
                    "city":"'.$city.'",
                    "state":"'.$state.'",
                    "country":"USA"
                },
                "location": ['.$order->long.', '.$order->lat.']
            }
        ';
        
        $ch = curl_init($url);
        #curl_setopt($ch, CURLOPT_HEADER, 1); #0
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . '');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch); #0
        curl_close($ch);
        
        $jsonObj = json_decode($response);
        $code = $info['http_code'];
        #var_dump($info); print_r($response); die(); #0
        
        return $jsonObj->id;
    }
    
    
    private function createRecipient() {
                
        $url = $this->apiUrl . '/recipients';
        
        $phone = $this->user->phone;
        #$phone = '(310) 433 - 0839'; #0
        
        $payload = '
            {
                "name": "'."{$this->user->firstname} {$this->user->lastname}".'",
                "phone": "'.$phone.'"
            }
        ';
        #var_dump($payload); die(); #0
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . '');
        #curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        $response2 = json_decode($response);
        $code = $info['http_code'];
        #var_dump($payload); var_dump($info); var_dump($response2); die(); #0
        
        // Malformed phone number
        if ($code == 500) 
            return NULL;
        else if ($code == 200)
            return $response2->id;
        // Otherwise, the user already exists, but Trak's non-optimal API won't
        // actually return the user, so we need to do Yet Another API Call to get the user
        else if ($code == 400 && $response2->message->cause->type == 'duplicateKey') {
            $recip = $this->findRecipientByPhone($phone);
            #var_dump($recip); #
            return $recip->id;
        }
        else
            return NULL;
        
        #die(); #
    }
    
    
    private function findRecipientByPhone($phone) {
        
        $url = $this->apiUrl . '/recipients/phone/'.$phone;
                
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . '');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        $response2 = json_decode($response);
        $code = $info['http_code'];
        #var_dump($response); die();
        
        return $response2;
        #return NULL; #0
    }
        
}