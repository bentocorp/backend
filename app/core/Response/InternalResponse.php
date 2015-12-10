<?php namespace Bento\core\Response;


use Response;


class InternalResponse {

        
    private $statusCode;
    private $success;   #boolean
    private $pubMsg;    #string
    
   // Your bag of stuff.
    public $bag; # stdClass
    
    
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
    
    
    public function getDerivedStatus()
    {
        return $this->getSuccess() ? 'success' : 'error' ;
    }
    
    
    public function getDerivedStatusClass()
    {
        return $this->getSuccess() ? 'success' : 'danger' ;
    }
    
    
    public function formatForRest()
    {
        $body = array (
            'status' => $this->getDerivedStatus(),
            'msg' => $this->getPubMsg(),
        );
        
        return Response::json($body, $this->getStatusCode());
    }
    
}
