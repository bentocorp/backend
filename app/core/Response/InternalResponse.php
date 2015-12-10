<?php namespace Bento\core\Response;



class InternalResponse {

        
    private $statusCode;
    private $success;
    private $pubMsg;
    
    # Your bag of stuff.
    public  $bag;
    
    
    public function __construct() {
        $this->bag = new \stdClass();
    }
    
    
    public function getStatusCode() {
        return $this->statusCode;
    }
    
    public function setStatusCode($code) {
        $this->statusCode = $code;
    }
    
    
    public function getSuccess() {
        return $this->success;
    }
    
    public function setSuccess($success) {
        $this->success = $success;
    }
    
    
    public function getPubMsg() {
        return $this->pubMsg;
    }
    
    public function setPubMsg($pubMsg) {
        $this->pubMsg = $pubMsg;
    }
    
}
