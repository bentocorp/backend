<?php

namespace Bento\Admin\Model;

use Bento\Model\Status as ApiStatus;
use DB;


class Status {

        
    public static function open() {
        
        $setting = Settings::find('status');
        $setting->value = 'open';
        $setting->save();
    }
    
    
    public static function closed() {
        
        $setting = Settings::find('status');
        $setting->value = 'closed';
        $setting->save();
    }
    
    
    public static function soldout() {
        
        $setting = Settings::find('status');
        $setting->value = 'sold out';
        $setting->save();
    }
    
    
    public static function getClass() {
        
        $status = ApiStatus::overall()->value;

        $statusClass = '';

        switch ($status) {
            case 'open':
                $statusClass = 'success';
                break;
            case 'closed':
                $statusClass = 'danger';
                break;
            case 'sold out':
                $statusClass = 'warning';
                break;
        }
        
        return $statusClass;
    }
    
    
    public static function getMsg() {
        
        $status = ApiStatus::overall()->value;

        $statusMsg = '';

        switch ($status) {
            case 'open':
                $statusMsg = 'Restaurant Open!';
                break;
            case 'closed':
                $statusMsg = 'Restaurant Closed!';
                break;
            case 'sold out':
                $statusMsg = "We're Sold Out!";
                break;
        }
        
        return $statusMsg;
    }
    
    
     
}
