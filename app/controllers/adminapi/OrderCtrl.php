<?php namespace Bento\AdminApi\Ctrl;


use Bento\Admin\Ctrl\OrderCtrl as AdminOrderCtrl;
use Response;
use DB;


class OrderCtrl extends \BaseController {

    /**
     * 
     * @param string $globalTaskId Prefixed with the task type. 
     *      Either o for an `Order`, or g for a Generic Task (`generic_Order`)
     *      e.g.: o-12345 vs g-6789
     * @param int $pk_Driver
     * @param string $insertAt Same format as $globalTaskId
     * @return JSON status
     */
    public function getAssign($globalTaskId, $pk_Driver = 0, $insertAt = 0) {
        
        #$pk_Order = Input::get('orderId');
        #$pk_Driver = Input::get('driverId');
        #$insertAt = Input::get('after');
        
        /*
        $foo = array('a', 'c', 'd', 'e', 'f');
        var_dump($foo);
        #unset($foo[0]); // This doesn't re-index
        array_splice($foo, 0, 1);
        var_dump($foo);
        array_splice($foo, 0, 0, 'b'); // This appears to re-index before executing, breaking any prior-saved indices
        var_dump($foo);
        #$str = implode(',', $foo);
        #echo $str;
        die();
         * 
         */
        
        $count = 0;
        $pk_Order = $this->getPk($globalTaskId, $count);
        
         ## Check for some errors:
        
        // A valid orderId was not passed
        if ($count == 0)
            return Response::json(array('error' => "A valid orderId was not passed. Must begin with 'o-'."), 
                    400);
        
        // A valid insertAtId was not passed
        if (is_numeric($insertAt) && $insertAt > 0)
            return Response::json(array('error' => "A valid insertAtId was not passed. Must be int 0, or a properly prefixed global task ID."), 
                    400);
        
        
        ## Assign the order to the Driver using the existing framework
        
        DB::transaction(function() use ($pk_Order, $pk_Driver, $insertAt)
        {
            $adminOrderCtrl = new AdminOrderCtrl;

            $_POST['pk_Driver'] = array('new' => $pk_Driver);

            $adminOrderCtrl->postSetDriver($pk_Order, $insertAt);
        });
        
        return Response::json('', 200);
    }
    
    
    private function getPk($globalTaskId, &$count) {
        return str_replace('o-', '', $globalTaskId, $count);
    }
    
    
}
