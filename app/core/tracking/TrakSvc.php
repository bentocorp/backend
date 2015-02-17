<?php
namespace Bento\Tracking;


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
        
        $url = $this->apiUrl . '/tasks';
        
        $orderStr = '';
        
        $boxCount = 1;

        foreach ($bentoBoxes as $box) {

            $orderStr .= "BENTO BOX $boxCount: ";
            $orderStr .= "$box->main_name $box->main_label, ";
            $orderStr .= "$box->side1_name $box->side1_label, "; 
            $orderStr .= "$box->side2_name $box->side2_label, ";
            $orderStr .= "$box->side3_name $box->side3_label, ";
            $orderStr .= "$box->side4_name $box->side4_label --; ";
            
            $boxCount++;
        }
        
        $payload = '
            {
                "merchant": "'.$this->organization.'",
                "executor": "'.$this->organization.'",
                "destination": "'.$destinationId.'",
                "recipients": [],
                "notes": "Order '.$order->pk_Order.': '."$orderStr".'"
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
        
        return $response;
    }
    
    
    public function addDestination($order) {
        
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
        #var_dump($response); die();
        
        return $response2->id;
    }
        
}