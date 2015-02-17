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
        
        // Create recipient
        $recip = $this->createRecipient();
        
        $recipString = $recip === NULL ? '' : "\"$recip\""; 
        
        $url = $this->apiUrl . '/tasks';
        
        $orderStr = '';
        
        $boxCount = 1;

        foreach ($bentoBoxes as $box) {

            $orderStr .= "BENTO $boxCount: \\n ===== \\n";
            $orderStr .= "$box->main_name $box->main_label \\n";
            $orderStr .= "$box->side1_name $box->side1_label \\n"; 
            $orderStr .= "$box->side2_name $box->side2_label \\n";
            $orderStr .= "$box->side3_name $box->side3_label \\n";
            $orderStr .= "$box->side4_name $box->side4_label \\n ===== \\n";
            
            $boxCount++;
        }
        
        $payload = '
            {
                "merchant": "'.$this->organization.'",
                "executor": "'.$this->organization.'",
                "destination": "'.$destinationId.'",
                "recipients": ['.$recipString.'],
                "notes": "Order '.$order->pk_Order.': \\n'."$orderStr".'"
            }
        ';
        #echo $payload; #
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $response = curl_exec($ch);
        curl_close($ch);
        #echo $response; die(); #
        
        return $response;
    }
    
    
    private function addDestination($order) {
        
        $url = $this->apiUrl . '/destinations';
        
        $payload = '
            {
                "address": {
                    "number":"'.$order->number.'",
                    "street":"'.$order->street.'",
                    "apartment":"",
                    "city":"'.$order->city.'",
                    "state":"'.$order->state.'",
                    "country":"USA"
                }
            }
        ';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $response2 = json_decode($response);
        #var_dump($response); die(); #
        
        return $response2->id;
    }
    
    
    private function createRecipient() {
        
        $user = User::get();
        
        $url = $this->apiUrl . '/recipients';
        
        $phone = $user->phone;
        #$phone = '(310) 433 - 0839'; #
        
        $payload = '
            {
                "name": "'."$user->firstname $user->lastname".'",
                "phone": "'.$phone.'"
            }
        ';
        #var_dump($payload); die(); #
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . '');
        #curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        $response2 = json_decode($response);
        $code = $info['http_code'];
        #var_dump($info); var_dump($response2); #
        
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        $response2 = json_decode($response);
        $code = $info['http_code'];
        #var_dump($response); die();
        
        return $response2;
    }
        
}