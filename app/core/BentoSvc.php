<?php namespace Bento\app;

use User;
use App;
use Mail;
use Session;


class BentoSvc {
    
    private $isAdminApiRequest = false;
    
    
    public function __construct() {

    }
    
    
    public function alert($exception = NULL, $subject = NULL, $uuid = NULL, $msg = NULL) {
        
        $env = App::environment();
        
        $finalSubject = "[App.{$env}.err]: ";
        
        $adminUser = Session::get('adminUser');
        
        if ($subject === NULL)
            $finalSubject .= "Uncaught Exception";
        else
           $finalSubject .= $subject; 
        
        // Send some error emails
        Mail::send('emails.admin.error_exception', 
            array('e' => $exception, 'user' => User::get(), 'adminUser' => $adminUser, 'uuid' => $uuid, 
                'msg' => $msg, 'subject' => $finalSubject), 
        function($message) use ($finalSubject)
        {
            $message->to($_ENV['Mail_EngAlert'], 'Bento App')->subject($finalSubject);
        });
    }
    
    
    public function isAdminApiRequest() {
        return $this->isAdminApiRequest;
    }
    
    public function setAsAdminApiRequest() {
        $this->isAdminApiRequest = true;
    }
        
}