<?php

namespace Bento\Ctrl;

use Bento\Model\Order;
use Bento\Admin\Model\Orders;
use Bento\Model\OrderStatus;
use Bento\Model\CustomerBentoBox;
use Bento\Model\LiveInventory;
use Bento\Model\Status;
use Bento\Tracking\Trak;
use User;
use Response;
use Input;
use Mail;
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
     * Phase 1:
     * Store a new pending order.
     * Hold the inventory in good faith by updating LiveInventory
     * 
     * 
     * @return json 
     */
    /*
    public function phase1() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Make sure this user doesn't already have a pending order
        $pendingOrder = PendingOrder::checkUser();
        
        if ($pendingOrder)
            return Response::json(array('Error' => 'A pending order already exists for you.'), 400);
                
        // Update LiveInventory and store into PendingOrder
        // (Phase 2 makes it final)
        $reserved = LiveInventory::reserve($data);
        
        // Inventory reservation went ok.
        if ($reserved !== false)
            return Response::json(array('reserveId' => $reserved));
        // Inventory reservation failed. We are out of something.
        else {
            // Since the inventory is incorrect in the client, conveniently send it back to them
            $menuStatus = Status::menu();
            
            $response = array(
                'Error' => 'Some of our inventory in your order just sold out!',
                'MenuStatus' => $menuStatus,
                );
            
            return Response::json($response, 410);
        }
    }
     * 
     */
    
    
    /**
     * Phase 2:
     * Commit a new order for real. Dispatch the driver, etc.
     * 
     * @return json 
     */
    /*
    public function phase2() {
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Get the PendingOrder
        $pendingOrder = PendingOrder::getUserPendingOrder();
                
        // Return 404 if PendingOrder not found
        if ($pendingOrder === NULL)
            return Response::json('', 404);
        
        // Perform payment verification with Stripe
        try {
            Stripe::setApiKey($_ENV['Stripe_sk_test']);
            $stripeChargeId = $data->stripe_chargeId;
            $ch = Stripe_Charge::retrieve($stripeChargeId);
        }
        catch (\Exception $e) {
            return Response::json(array('Error' => 'Invalid stripe_chargeId.'), 400);
        }
        
        // Everything's good at this point.
        $this->processOrder($pendingOrder, $ch);
    }
    
    
    private function processOrder($pendingOrder, $ch) {
        
        // Get the user
        $user = User::get();

        // Insert into Order
        $order = new Order;
        
        $orderJson = json_decode($pendingOrder->order_json);
        
        $order->street = $orderJson->OrderDetails->address->street;
        $order->city = $orderJson->OrderDetails->address->city;
        $order->state = $orderJson->OrderDetails->address->state;
        $order->zip = $orderJson->OrderDetails->address->zip;
        $order->lat = $orderJson->OrderDetails->coords->lat;
        $order->long = $orderJson->OrderDetails->coords->long;
        
        $order->fk_User = $user->pk_User;
        $order->amount = $ch->amount / 100;
        $order->tax = $orderJson->OrderDetails->tax;
        $order->tip = $orderJson->OrderDetails->tip;
        $order->stripe_charge_id = $ch->id;
        $order->fk_PendingOrder = $pendingOrder->pk_PendingOrder;
        
        $order->save();
        
        // Insert into OrderStatus
        $orderStatus = new OrderStatus;
        $orderStatus->fk_Order = $order->pk_Order;
        $orderStatus->save();
        
        // Insert into CustomerBentoBox
        $this->insertCustomerBentoBoxes($orderJson, $order->pk_Order);
        
        // Soft-delete pending order
        $pendingOrder->delete();
        
        // Dispatch the driver
        # do stuff
        
        return Response::json('', 200);
    }
     * 
     */
    
    
    /**
     * We don't need a two phase commit if we're processing payment on the backend.
     */
    public function postIndex() {
        
        // If the restaurant is not open, we're done!
        $status = Status::getOverall();
        
        if ($status != 'open') {
            
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
        
        // Get data
        $data = json_decode(Input::get('data'));
        
        // Get the User
        $user = $this->user;
        
        
        // If no Stripe token, AND no saved User data, error.
        if ($user->stripe_customer_obj == NULL && !$this->hasStripeToken($data)) {
            return Response::json(
                    array("error" => "No payment specified, and no payment on file."), 
                    402);
        }
        
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
        
        // A card token takes priority. This way a user can always 
        // change their card on file. And at this point in execution, we know they have one
        // or the other.
        if ($this->hasStripeToken($data))
            $stripeCharge = $this->stripeChargeFromToken($data);
        else
            $stripeCharge = $this->stripeChargeFromSaved($data);
        
        
        // Payment Success
        if ($stripeCharge['status'] === true) {
            
            $this->paymentSuccess($data, $stripeCharge['body']);
            
            return Response::json('', 200);
        }
        // Payment Failure
        else {
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
                $message->to('engalert@bentonow.com', 'Bento App')->subject("[App.{$env}.err]: Stripe Failure");
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
                  "description" => $user->email)
                );

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
    
    
    private function paymentSuccess($orderJson, $stripeCharge) {
        
        // Get the user
        $user = $this->user;

        // Insert into Order
        $order = new Order;
        
        $order->number = $orderJson->OrderDetails->address->number;
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
        $order->stripe_charge_id = $stripeCharge->id;
        $order->fk_PendingOrder = $this->pendingOrder->pk_PendingOrder;
        
        $order->save();
        
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
        
        // --- Do something stupidly expensive until we can fix it
        $bentoBoxes = Orders::getBentoBoxesByOrder($order->pk_Order); 
        
        // Put into Trak
        $response = Trak::addTask($order, $orderJson, $bentoBoxes);
        #Trak::test();
        
        
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
