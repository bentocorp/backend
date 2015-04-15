<?php
namespace Bento\Tracking;

use User;


class TrakSvc {
    
    private $apiUrl;
    private $apiKey;
    private $organization;
    
    
    public function __construct() {
        
        $this->apiUrl = $_ENV['Trak_API'];
        $this->apiKey = $_ENV['Trak_key'];
        $this->organization = $_ENV['Trak_organization'];
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
    
    
    public function addTask($order, $orderJson, $bentoBoxes) {
        
        // Add destination first
        $destinationId = $this->addDestination($order);
        #$destinationId = '';
        
        // Create recipient
        $recip = $this->createRecipient();
        
        $recipString = $recip === NULL ? '' : "\"$recip\""; 
        
        $url = $this->apiUrl . '/tasks';
        
        $orderStr = '';
        
        $boxCount = 1;
        
        $n = count($bentoBoxes);

        foreach ($bentoBoxes as $box) {

            $orderStr .= "BENTO $boxCount of $n: \\n ===== \\n";
            $orderStr .= "$box->main_name  - $box->main_label \\n";
            $orderStr .= "$box->side1_name - $box->side1_label \\n"; 
            $orderStr .= "$box->side2_name - $box->side2_label \\n";
            $orderStr .= "$box->side3_name - $box->side3_label \\n";
            $orderStr .= "$box->side4_name - $box->side4_label \\n ===== \\n";
            
            $boxCount++;
        }
        
        $payload = '
            {
                "merchant": "'.$this->organization.'",
                "executor": "'.$this->organization.'",
                "destination": "'.$destinationId.'",
                "recipients": ['.$recipString.'],
                "notes": "Order #'.$order->pk_Order.': \\n'."$orderStr".'"
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
        
        $user = User::get();
        
        $url = $this->apiUrl . '/recipients';
        
        $phone = $user->phone;
        #$phone = '(310) 433 - 0839'; #0
        
        $payload = '
            {
                "name": "'."$user->firstname $user->lastname".'",
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
        #var_dump($info); var_dump($response2); #0
        
        // Malformed phone number
        if ($code == 500) 
            return NULL;
        else if ($code == 200)
            return $response2->id;
        // Otherwise, the user already exists, but Trak's crappy API won't
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