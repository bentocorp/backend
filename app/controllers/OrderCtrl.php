<?php namespace Bento\Ctrl;


use Bento\Model\Order;
use Bento\Admin\Model\Orders;
use Bento\Model\OrderStatus;
use Bento\Model\CustomerBentoBox;
use Bento\Model\LiveInventory;
use Bento\Model\Status;
use Bento\Tracking\Trak;
use Bento\app\Bento;
use Bento\Coupon\AppCoupon;
use User;
use Response;
use Request; use Route;
use Input;
use Mail;
use Queue;
use Stripe; use Stripe_Charge; use Stripe_Customer;

class OrderCtrl extends \BaseController {
    
    private $pendingOrder;
    private $user;
    
    private $stripeChargeObj = NULL;
    #private $stripeCustomer = NULL;

    
    public function __construct() {
        
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here https://dashboard.stripe.com/account
        Stripe::setApiKey($_ENV['Stripe_secret_key']);
        
        // Get the user
        $this->user = User::get();
    }
    
        
    /**
     * We don't need a two phase commit if we're processing payment on the backend.
     */
    public function postIndex() {
        
        // If the restaurant is not open, we're done!
        $status = Status::getOverall();
        
        if ($status != 'open' && !User::get()->is_admin) {
            
            $errorMsg = '';
            
            switch ($status) {
                case 'closed':
                    $errorMsg = "Whoops! It looks like we've just closed down for the night.";
                    break;
                case 'sold out':
                    $errorMsg = "Whoops! It looks like we've just sold out of everything! Check back soon and we might have more.";
                    break;
            }
            
            return Response::json(array("error" => $errorMsg), 423);
        }
        
        // Vars
        $stripeCharge = false;
        $couponFromOrder = NULL;
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Get the User
        $user = $this->user;
    
        // Assume that there is a payment to process
        $processPayment = true;
        
        
        // -- BEGIN DATA VALIDATIONS --
        try {
            // Reject API call if the OrderItems[] is empty. This has actually happened, 
            // and as of 2015-04-21, VJC and JL are not sure how.
            $orderItemsLength = count($data->OrderItems);
            
            if ($orderItemsLength <= 0) {
                Bento::alert(null, 'OrderItems[] was empty', '14c309c2-1cc6-47e5-93ba-51cddfdd52f1');
                return Response::json(
                        array("error" => "Something has gone wrong with your order. Bento has been notified."), 
                        400);
            }

            
            // Reject API call if amount is less than 50 cents, otherwise Stripe denies the charge.
            if ($data->OrderDetails->total_cents > 0 && $data->OrderDetails->total_cents < 50)
                return Response::json(
                        array("error" => "The amount must be at least $0.50."), 
                        400);

            // Don't process a $0 payment in the backend / with Stripe
            if ($data->OrderDetails->total_cents == 0)
                $processPayment = false;

            // If no Stripe token, AND no saved User data, error.
            if ($user->stripe_customer_obj == NULL && !$this->hasStripeToken($data) && $processPayment) {
                return Response::json(
                        array("error" => "No payment specified, and no payment on file."), 
                        402);
            }
            
            // IF coupon, make sure that they are using a valid coupon
            if ( isset($data->CouponCode) && $data->CouponCode !== NULL && $data->CouponCode != '' ) {
                
                // Call the same Controller function that we used to get the coupon's value, 
                // because it also includes all validation. If this method returns 200, then
                // we know we can safely apply the coupon.
                $request1 = Request::create("coupon/apply/$data->CouponCode", 'GET');
                $response1 = Route::dispatch($request1);
                $statusCode = $response1->getStatusCode();
                
                // Instantiate the coupon
                if ($statusCode == 200) {
                    $coupon = new AppCoupon;
                    $coupon->find($data->CouponCode);
                    $couponFromOrder = $coupon;
                }
                // Otherwise, return the error
                else {
                    return $response1;
                }
            }
        }
        catch(\Exception $e) {
            Bento::alert($e, 'Order Data Validation Error', 'd1f8330b-789e-4a6b-86c7-6c027340d8d2');
            return Response::json(array("error" => "Bad news bears. Something has gone wrong. Bento has been notified."), 460);
        }
        // -- END DATA VALIDATIONS -- 
        
        // Check the LiveInventory 
        $reserved = LiveInventory::reserve($data);
        
        // If inventory reservation failed. We are out of something.
        if ($reserved === false) {
            // Since the inventory is incorrect in the client, conveniently send it back to them
            $menuStatus = Status::menu();
            
            $response = array(
                'Error' => 'Some of our inventory in your order just sold out!',
                'MenuStatus' => $menuStatus
                );
            
            return Response::json($response, 410);
        }
        
        // Otherwise, everything's good. Keep going.
        $this->pendingOrder = $reserved;
        
        // ** Process payment
        
        // Only process if > than 50 cents
        if ($processPayment) {
            
            // A card token takes priority. This way a user can always 
            // change their card on file. And at this point in execution, we know they have one
            // or the other.
            if ($this->hasStripeToken($data))
                $stripeCharge = $this->stripeChargeFromToken($data);
            else
                $stripeCharge = $this->stripeChargeFromSaved($data);
        }
        // Otherwise, just simulate success
        else {
            $stripeCharge = array();
            $stripeCharge['status'] = true;
            $stripeCharge['body'] = NULL; // Set to NULL so that no Stripe info is attempted to be saved into the DB
        }
        
        
        // Payment Success
        if ($stripeCharge['status'] === true) {
            
            $this->paymentSuccess($data, $stripeCharge['body'], $couponFromOrder);
            
            return Response::json('', 200);
        }
        // Payment Failure
        else {
            // Order inventory rollback
            // This is a bit of a hack until we remove the PendingOrder architecture
            // that isn't really needed in the first place. But it's fine for now.
            $order = new Order(null, $this->pendingOrder->pk_PendingOrder);
            $order->rollback(true); // TRUE denotes we're rolling back a PendingOrder instead of an Order
            
            return Response::json(array("error" => $stripeCharge['body']['message']), 406);
        }
        
    }
    
    /**
     * Determine if there is a Stripe token present. Try to account for a lot
     * of different things an overseas iOS developer might do.
     * 
     * @param obj $data The json_decoded order data.
     * @return boolean 
     */
    private function hasStripeToken($data) {
               
        try {
            $token = $data->Stripe->stripeToken;
        }
        catch (\Exception $e) {
            return false;
        }
        
        if ($token == NULL || $token == 'null' || $token == 'NULL' || $token == '')
            return false;
        else
            return true;
    }
    
    
    private function stripeCharge($fn) {
        
        // Reset for certainty
        $this->stripeChargeObj = NULL;
        
        // Setup return
        $return = array(
            'status' => false,
            'body' => null
        );
        
        // Execute the Stripe code
        try {
            $fn();
        }
        // Errors
        catch (\Exception $e) {
            $body = $e->getJsonBody();
            $err  = $body['error'];
            $return['body'] = $err;
            
            // Send some error emails
            Mail::send('emails.admin.error_stripe', array('e' => $e, 'user' => $this->user), function($message)
            {
                $env = \App::environment();
                $message->to($_ENV['Mail_EngAlert'], 'Bento App')->subject("[App.{$env}.err]: Stripe Failure");
            });
            
            // Return with errors
            return $return;
        }
        
        // No errors. Return okay
        $return['status'] = true;
        $return['body'] = $this->stripeChargeObj;
        
        return $return;
    }
    
    
    /**
     * 
     * @param obj $order
     * @return boolean False or stripeCharge object
     */
    private function stripeChargeFromToken($order) {
                 
        $customer = NULL;
                 
        // Get the User
        $user = $this->user;
         
        // Get the credit card details submitted by the form
        $token = $order->Stripe->stripeToken;

        // Define Stripe charge function. This is wrapped in a try/catch.
        $fn = function() use ($user, $token, $order, &$customer) {
            
            // If we don't have a customer object on file:
            if ($user->stripe_customer_obj == NULL) {
            
                // Create a Customer
                $customer = Stripe_Customer::create(array(
                    "card" => $token,
                    "description" => "$user->firstname $user->lastname",
                    "email" => $user->email,
                    "metadata" => array(
                        "firstname" => $user->firstname,
                        "lastname" => $user->lastname, 
                     )
                ));

                // If ok (no exception thrown), Save the customer ID in our database so we can use it later
                $user->stripe_customer_obj = $customer;
                $user->save();
            }
            // Otherwise we have them on file, and they're updating their card
            else {
                // Get our customer on file
                $customer = $user->stripe_customer_obj;
                
                // Update with stripe
                $cu = Stripe_Customer::retrieve($customer->id);
                $cu->card = $token;
                $cu->save();
                
                // Update in our DB
                $cu2 = Stripe_Customer::retrieve($customer->id);
                $user->stripe_customer_obj = $cu2;
                $user->save();
            }
                        
            // Charge the Customer instead of the card
            $this->stripeChargeObj = Stripe_Charge::create(array(
              "amount" => $order->OrderDetails->total_cents, # amount in cents, again
              "currency" => "usd",
              "customer" => $customer->id)
            );
        };
        
        $stripeChargeResult = $this->stripeCharge($fn);
                                
        return $stripeChargeResult;
    }
    
    
    private function stripeChargeFromSaved($order) {
        
        $user = $this->user;
        
        $customerId = $user->stripe_customer_obj->id;
        
        // Define Stripe charge function
        $fn = function() use ($order, $customerId) {
            $this->stripeChargeObj = Stripe_Charge::create(array(
              "amount"   => $order->OrderDetails->total_cents, # amount in cents, again
              "currency" => "usd",
              "customer" => $customerId)
            );
        };
        
        $stripeChargeResult = $this->stripeCharge($fn);
        
        return $stripeChargeResult;
    }
    
    
    private function paymentSuccess($orderJson, $stripeCharge, $coupon) {
        
        // Get the user
        $user = $this->user;

        // Insert into Order
        $order = new Order;
        
        // VJC: Because some people are on an OLD VERSION
        #try {
        $order->number = $orderJson->OrderDetails->address->number;
        #} catch (\Exception $ex) { }
        $order->street = $orderJson->OrderDetails->address->street;
        $order->city = $orderJson->OrderDetails->address->city;
        $order->state = $orderJson->OrderDetails->address->state;
        $order->zip = $orderJson->OrderDetails->address->zip;
        $order->lat = $orderJson->OrderDetails->coords->lat;
        $order->long = $orderJson->OrderDetails->coords->long;
        
        $order->fk_User = $user->pk_User;
        $order->amount = $orderJson->OrderDetails->total_cents / 100;
        $order->tax = $orderJson->OrderDetails->tax_cents / 100;
        $order->tip = $orderJson->OrderDetails->tip_cents / 100;
        $order->fk_PendingOrder = $this->pendingOrder->pk_PendingOrder;
        
        // Save Stripe things only if a Stripe charge was made.
        // This happens when a coupon is used for a free bento, since in that case we don't send $0 to Stripe
        if ($stripeCharge !== NULL) {
            $order->stripe_charge_id = $stripeCharge->id;
        }
        
        // Store the coupon
        if ( $coupon !== NULL )
            $order->fk_Coupon = $coupon->getCode();
        
        $order->save(); // Finally, insert into the Order table
                
        // Insert into OrderStatus
        $orderStatus = new OrderStatus;
        $orderStatus->fk_Order = $order->pk_Order;
        $orderStatus->save();
        
        // Insert into CustomerBentoBox
        $this->insertCustomerBentoBoxes($orderJson, $order->pk_Order);
        
        // Bind the completed Order to the PendingOrder
        $this->pendingOrder->fk_Order = $order->pk_Order;
        $this->pendingOrder->save();
        
        // Soft-delete pending order
        $this->pendingOrder->delete();
        
        // Redeem the coupon
        if ( $coupon !== NULL )
            $coupon->redeem($order->pk_Order);
        
        // --- Do something stupidly expensive until we can fix it 
        /* The issue was that we didn't have the *name* of the item, so we had
         * to then perform yet another DB lookup. This was subsequently fixed in the order API
         * request sent by Ri, on request of Vincent. However, the fix has not been implemented in the code yet.
         * 
         * Given the fact that we HAVE to do this lookup in order to get the `label` 
         * (as that can't be sent through to the API, [unless we send it as part of the dish info]),
         * I thought that it made sense to wait to fix this until it becomes a problem due to scale, as
         * at that point, we might as well just switch to an async queue architecture rather than wasting
         * time with this sort of micro-optimization.
         */
        $bentoBoxes = Orders::getBentoBoxesByOrder($order->pk_Order); 
                
        // Put into Trak
        try {
            $trkResponse = Trak::addTask($order, $orderJson, $bentoBoxes);
            #Trak::test(); #0
            #print_r($trkResponse); die(); #0
            
            // Log the Trak (Onfleet) response, if it was bad
            $trkStatus = $trkResponse['info']['http_code'];

            // Trak worked, it's ok
            if ($trkStatus == 200) {
                $orderStatus->trak_status = 200;
                $orderStatus->save();
            }
            // Trak failed, log some errors
            else {
                $orderStatus->trak_status = $trkStatus;
                $orderStatus->trak_error_payload = $trkResponse['payload'];
                $orderStatus->trak_error_response = $trkResponse['response'];
                $orderStatus->save();
            }
            
        } catch (\Exception $e) {
        // Catch any other exceptions, and safely return
            $orderStatus->trak_status = 'Exception';
            $orderStatus->save();
            
            Bento::alert($e, 'Onfleet Exception', '1be04240-c860-4bf7-b205-f362e832ba85');
        }
        
        // Put into the order queue for async processing
        try {
            $orderJson->pk_Order = $order->pk_Order;
            $orderJson->User = User::get();
            Queue::push('Bento\Jobs\DoNothing', json_encode($orderJson));
        } catch (\Exception $e) {
            Bento::alert($e, 'Queue Insertion Exception', 'af30d4ea-6f7c-4f80-89ca-972f8541ee2f');
        }
        
        
        // Send an order confirmation email
        Mail::send('emails.transactional.order_confirmation', array(
            'order' => $order, 
            'orderJson' => $orderJson, 
            'user' => $user,
            'bentoBoxes' => $bentoBoxes,
            ), 
            function($message) use ($user)
            {
                $message->from('help@bentonow.com', 'Bento');
                $message->to($user->email)->subject("Your Bento Order");
            });
        
        
        // Set the user as now having ordered, if they haven't
        if (!$user->has_ordered) {
            $user->has_ordered = 1;
            $user->save();
        }
    }
    
        
    private function insertCustomerBentoBoxes($orderJson, $pk_Order) {
        
        // For each CustomerBentoBox
        foreach ($orderJson->OrderItems as $orderItem) {
            
            // Make a CustomerBentoBox
            $box = new CustomerBentoBox;
            $box->fk_Order = $pk_Order;
            
            // Now for each thing in the box
            foreach($orderItem->items as $item) {
                $fk = "fk_$item->type";
                $box->{$fk} = $item->id;
            }
            
            // Save the box
            $box->save();
        }
    }
    
}
