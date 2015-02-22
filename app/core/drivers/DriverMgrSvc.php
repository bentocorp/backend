<?php
namespace Bento\Drivers;

use Bento\Admin\Model\Driver;
use Bento\Model\LiveInventory;
use DB;


class DriverMgrSvc {

    private $cantRemove = array();
    
    public function __construct() {

    }
    
    
    public function updateShifts($data) {
        
        # if drivers are set, add those, and try to see who we can remove. Compile a list of those we can't.
        # if drivers  are not set, try to remove all. Compile a list of those we can't.
                
        // Update
        if (isset($data['drivers'])) 
        {  
            $in = implode(',', $data['drivers']);
            
            // 1. Update who is on shift
             DB::update("update Driver set on_shift = 1 where pk_Driver in ($in)", array());

            // 2. Get who is intended to be off shift
            $desiredOffShiftDrivers = DB::select("select * from Driver where pk_Driver NOT in ($in) AND on_shift");
            
            $this->removeDriverListFromShift($desiredOffShiftDrivers);
        }
        // Remove everyone
        else {
            $this->removeAll();
        }
                
        return $this->cantRemove;
    }
    
        
    private function removeAll() {

        // 1. Get everyone
        $desiredOffShiftDrivers = DB::select("select * from Driver");

        $this->removeDriverListFromShift($desiredOffShiftDrivers);

        // If we've asked to remove everyone, and it's safe to do so,
        // perform this double check that everything is reset. This probably
        // happens at the end of the night.
        if (count($this->cantRemove) == 0) {
            // Clear all driver inventories 
            DB::delete("delete from DriverInventory");
            
            // Set all drivers off shift
            DB::update('update Driver set on_shift = 0');

            // Recalculate LiveInventory
            LiveInventory::recalculate();
        }
    }
    
    
    private function removeDriverListFromShift($desiredOffShiftDrivers) {
        
        foreach ($desiredOffShiftDrivers as $row) {

            $driver = new Driver($row->pk_Driver);
            $result = $driver->removeFromShift();

            $success = $result['ok'];

            if (!$success)
                $this->addUpdateShiftError($result, $row);
        }
    }
    
    
    private function addUpdateShiftError($result, $row) {
                          
        if (!isset($this->cantRemove[$result['reason']])) {
            $this->cantRemove[ $result['reason'] ] = array();
            $this->cantRemove[ $result['reason'] ]['msg'] = $result['desc'];
            $this->cantRemove[ $result['reason'] ]['rows'] = array();
        }
        
        $this->cantRemove[ $result['reason'] ]['rows'][$row->pk_Driver] = "$row->firstname  $row->lastname";
    }
        
}