<?php namespace Bento\Order;


use Bento\Model\PendingOrder;
use Bento\core\InternalResponse;
use Bento\Order\Cashier;
use Bento\app\Bento;
use DB;
use User;
use Illuminate\Database\QueryException;


class OrderReserver {

    private $orderJsonObj;
    private $pendingOrder;
    private $user;
    private $response;
    private $totalWaits = 0;
    
    
    public function __construct(\stdClass $orderJsonObj) 
    {
        // The Order JSON, already as an object
        $this->orderJsonObj = $orderJsonObj;
        
        // Create a new PendingOrder object
        $this->pendingOrder = new PendingOrder;
        
        // Get the user for this request
        $this->user = User::get();
        
        $this->response = new InternalResponse;
    }

    
    /**
     * Attempt to reserve the inventory for this order.
     * 
     * @return InternalResponse -- The PendingOrder will be attached to the bag data member.
     * @throws QueryException
     */
    public function reserve() {
        
        // If anything goes wrong, abort!
        try
        {
            // ## First, detect a duplicate order
            if ($this->isDuplicate())
                return $this->response;

            // ## At this point, we're sure that there isn't a duplicate order,
            // so let's continue trying to process the order.

            /* First calculate the totals */
            $cashier = new Cashier($this->orderJsonObj, $this->pendingOrder->pk_PendingOrder, $this->pendingOrder->fk_Order);
            $totals = $cashier->getTotalsHash();

            /* Next, try to reserve the totals
             * 
             * The DB is set to an unsigned int, so it cannot become negative.
             * We wrap each deduction in a transaction, and if any of them fail, we know
             * that we don't have enough inventory to complete this order.
             */
            try {
                DB::transaction(function() use ($totals)
                {
                    foreach ($totals as $itemId => $itemQty) {
                      DB::update("update LiveInventory set qty = qty - ? WHERE fk_item = ?", array($itemQty, $itemId));
                    }
                });
            }
            catch(QueryException $e) {
                // Hard delete the pending order. We don't need it.
                $this->fail();

                $this->response->setStatusCode(410);

                return $this->response;
            }

            // ## We had enough inventory for this order.

            // ## Success! Everything is good.
            $this->success();

            // Return the response, which contains the PendingOrder
            return $this->response;
        }
        catch(\Exception $e)
        {
            $this->fail();
            
            Bento::alert($e, '[HIGH] Uncaught Inventory Reservation Exception', 'db690417-5e9b-40ba-a6e7-f2441960e809', 
                    $this->orderJsonObj);
            
            return $this->response;
        }
    }
    
    
    public function isDuplicate() {
        
        // Base case for recursion
        // Will sleep 3 times, for 2 seconds each
        if ($this->totalWaits >= 2) 
        {
            Bento::alert(null, '[HIGH] Idempotent Processing Wait Limit Reached', '6c1bb462-569c-445f-b412-374e46a99904',
                    $this->orderJsonObj);
            
            $this->response->setSuccess(false);
            $this->response->setStatusCode(0);
            
            return true;
        }
            
        
        // Attempt to save the PendingOrder:
        
        $this->pendingOrder->fk_User = $this->user->pk_User;
        $this->pendingOrder->order_json = json_encode($this->orderJsonObj);
        
        // Try the idempotent token!
        $this->pendingOrder->idempotent_token = NULL;
        if (isset($this->orderJsonObj->IdempotentToken))
            $this->pendingOrder->idempotent_token = $this->orderJsonObj->IdempotentToken;
        
        try {
            $this->pendingOrder->save();
        } 
        catch (QueryException $e) {
            #var_dump($e->errorInfo[0]); die();
            // This is a duplicate order
            if ($e->errorInfo[0] == 23000) // SQLSTATE: 23000 (ER_DUP_KEY)
            {
                // IF the order is still processing, wait a few times
                $existingPendingOrder = DB::select('select * from PendingOrder where idempotent_token = ?', array($this->orderJsonObj->IdempotentToken))[0];
                
                if ($existingPendingOrder->is_processing) {
                    sleep(2); // Try to wait for it to finish
                    $this->totalWaits++;
                    
                    return $this->isDuplicate();
                }
                // OTHERWISE, we already have it, and it's all good
                else {
                    // Return an appropriate status object back to the OrderCtrl
                    $this->response->setSuccess(false);
                    $this->response->setStatusCode(23000);

                    return true;
                }
            }
            // Otherwise, something else has gone horribly wrong
            else
                throw new QueryException;
        }
        
        return false;
    }
    
    
    /**
     * Win the order reservation!
     */
    private function success() {
        $this->response->setSuccess(true);
        $this->response->setStatusCode(200);
        $this->response->bag->pendingOrder = $this->pendingOrder;
    }
    
    
    /**
     * Fail this order reservation
     */
    public function fail() {
        // Force delete the PendingOrder record
        $this->pendingOrder->forceDelete(); # Can safely be called multiple times
        
        // Set the response object's success to false
        $this->response->setSuccess(false);
    }
    
    
}
