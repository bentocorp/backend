<?php

namespace Bento\Admin\Ctrl;

#use Bento\Admin\Model\Misc;
use View;
use DB;
use Input;
use Redirect;


class MiscCtrl extends \BaseController {

    public function getIoscopy() {
        
        $data = array();
        
        $iosCopy = DB::select('SELECT * FROM admin_ios_copy order by `key` asc', array());
        $data['iosCopy'] = $iosCopy;
        
        return View::make('admin.ioscopy', $data);
    }
    
    
    public function postIoscopy() {
        
        $data = Input::get('ioscopy');
        
        $whereIn = '';
        $i = 1;
        $n = count($data);
        
        // Do a multi-row update. (http://stackoverflow.com/questions/18802671/update-multiple-rows-in-a-single-mysql-query)
        $sql = "
        UPDATE admin_ios_copy
        SET `value` = (CASE `key` ";
        
        foreach ($data as $key => $val) {
            $val = DB::connection()->getPdo()->quote($val);
            $sql .= " WHEN '$key' THEN $val " ;
            $whereIn .= "'$key'";
            
            if ($i < $n)
                $whereIn .= ', '; // Add the comma to all except the last one
            
            $i++;
        }
        
        $sql .= " 
        END)
        WHERE `key` IN ($whereIn)
        ";
                
        DB::update($sql);
        
        return Redirect::back()->with('msg', array('type' => 'success', 'txt' => 'Copy saved.'));
    }
    
    
    
}
