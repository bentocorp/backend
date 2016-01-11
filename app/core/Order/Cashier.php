<?php namespace Bento\Order;


use User;


/**
 * The Cashier knows everything about an Order, and can manage all desired actions on it.
 */
class Cashier {

    private $orderJsonObj = NULL;
    private $pk_PendingOrder;
    private $pk_Order;
    
    private $isListsInit = false;
    private $Lists = NULL; # A hash of list types.
    
    private $user;
    
    /**
     * 
     * @param \stdClass $orderJsonObj Required. The JSON is the easiest way to manipulate and parse an Order.
     * @param int $pk_PendingOrder
     * @param int $pk_Order
     */
    public function __construct(\stdClass $orderJsonObj, $pk_PendingOrder = NULL, $pk_Order = NULL) 
    {
        $this->orderJsonObj = $orderJsonObj;
        $this->pk_PendingOrder = $pk_PendingOrder;
        $this->pk_Order = $pk_Order;
        
        // Get the user
        $this->user = User::get();
    }
    
    
    /**
     * Build the lists from the JSON 
     * 
     * Turn the JSON objects into proper code objects.
     * Add each unique type into a {type}List to hold them.
     * 
     * @return none
     */
    private function listInit()
    {
        // Base case
        if ($this->isListsInit)
            return;
        
        $this->isListsInit = true;
        
        // Put the objects into the lists
        foreach ($this->orderJsonObj->OrderItems as $orderItem)
        {
            $listName = $orderItem->item_type.'List';
            
            // If list doesn't exist, make the list
            if (!isset($this->Lists[$listName])) {
                $classname = "Bento\\Order\\ItemList\\$listName";
                $this->Lists[$listName] = new $classname($this->pk_Order);
            }
            
            // Add the item
            $this->Lists[$listName]->addItem($orderItem);
        }
        #var_dump($this->Lists);
        #die('done listInit'); #0;
    }
    
    
    public function getTotalsHash()
    {
        $totals = array();

        // For each OrderItem (it could be a CBB, an AddonList, etc.)
        foreach ($this->orderJsonObj->OrderItems as $orderItem) {
            
            // Make the object
            $classname = "Bento\\Order\\Item\\$orderItem->item_type";
            
            $item = new $classname($orderItem);
            
            // Calculate its totals
            $item->calculateTotals($totals);
        }
        
        return $totals;
    }
    
    
    public function writeItems() 
    {
        $this->listInit();
        
        foreach ($this->Lists as $list) {
            $list->writeItems();
        }
    }
    
    
    /**
     * Get the order string, for the Driver app
     * @return string
     */
    public function getOrderString()
    {
        $orderStr = '';
        
        // Order Number
        $orderStr .= "Order #{$this->pk_Order}: \\n\\n";
        
        // Summary
        $orderStr .= "Checklist: \\n %summary% \\n";

        // If this is a Top Customer, tell the driver!
        $topCustomerStr = ">> ࿉∥(⋆‿⋆)࿉∥ Top Customer! << \\n\\n";
        if ($this->user->is_top_customer) {
            $orderStr .= $topCustomerStr;
        }

        // If this is NEW customer, tell the driver!
        $newCustomerStr = ">> *∥(◕‿◕)∥* 1st customer order!! << \\n\\n";
        if (!$this->user->has_ordered) {
            $orderStr .= $newCustomerStr;
        }
        
        $checklist = '';
        
        // Print Bentos, Addons, etc.
        foreach ($this->Lists as $name => $list) {
            if ($name != 'AddonListList') { # Addons last
                $list->getOrderString($orderStr); # Add to str by reference
                
                // Checklist
                $total = $list->getTotalQty();
                $name = $list->getContentsNamePlural();
                $checklist .= "[ ] {$total} $name \\n";
            }
        }
        
        # Addons last
        if (isset($this->Lists['AddonListList'])) {
            $this->Lists['AddonListList']->getOrderString($orderStr);
            
            // Checklist
            $total = $this->Lists['AddonListList']->getTotalQty();
            $name  = $this->Lists['AddonListList']->getContentsNamePlural();
            $checklist .= "[ ] {$total} $name \\n";
        }
        
        
        // If this is NEW customer, tell the driver!
        if (!$this->user->has_ordered) {
            $orderStr .= $newCustomerStr;
        }
        
        // TOP: Insert order summary
        $orderStr = str_replace('%summary%', $checklist, $orderStr);
        
        // BOTTOM: Remind the drivers about accuracy, mochi, soy sauce, and chopsticks
        $orderStr .= "Checklist: \\n";
        $orderStr .= $checklist;
        $orderStr .= "[ ] Accuracy \\n";
        $orderStr .= "[ ] Mochi, ask which type of soy sauce, wasabi, offer utensils \\n";
        
        $orderStr .= "\\nArigatō!";
        
        // Finally, return the string
        return $orderStr;
    }

        
    public function printEmailItems()
    {
        foreach ($this->Lists as $name => $list) {
            if ($name != 'AddonListList') # Addons last
             $list->printEmailReceipt();
        }
        
        # Addons last
        if (isset($this->Lists['AddonListList']))
            $this->Lists['AddonListList']->printEmailReceipt();
    }
    
    
    public function printEmailTotals($order)
    {
        // Tip Percent
        $tip_percent = '';
        if ($order->tip_percentage != 0)
            $tip_percent = "($order->tip_percentage%)";
        
        // Promo code
        $promo_amount = '';
        if ($order->coupon_discount > 0)
            $promo_amount = "($$order->coupon_discount off)";
        
        ?>
        <table width="100%" cellpadding="0" cellspacing="0">
                <!-- row -->
                <tr mc:repeatable="repeatable-05">
                        <td class="textblock-01" style="padding: 25px 35px 28px;"> <!-- border-top: 1px solid #d7dbdb; -->
                                <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                                <td mc:edit="block-12" class="text-01" style="font: 28px/35px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;">
                                                        Delivery fee
                                                </td>
                                                <td width="10"></td>
                                                <td mc:edit="block-13" class="text-01" style="font: 28px/35px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;" align="right">
                                                        $<?php echo number_format($order->delivery_price, 2)?>
                                                </td>
                                        </tr>
                                </table>
                        </td>
                </tr>
                <!-- row -->
                <tr mc:repeatable="repeatable-06">
                        <td class="textblock-01" style="padding: 25px 35px 28px; border-top: 1px solid #d7dbdb;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                                <td mc:edit="block-14" class="text-01" style="font: 28px/35px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;">
                                                        Tax
                                                </td>
                                                <td width="10"></td>
                                                <td mc:edit="block-15" class="text-01" style="font: 28px/35px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;" align="right">
                                                        $<?php echo number_format($order->tax, 2)?>
                                                </td>
                                        </tr>
                                </table>
                        </td>
                </tr>
                <!-- row -->
                <tr mc:repeatable="repeatable-07">
                        <td class="textblock-01" style="padding: 25px 35px 28px; border-top: 1px solid #d7dbdb;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                                <td mc:edit="block-16" class="text-01" style="font: 28px/35px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;">
                                                        Tip <span style="font-size:16px;"><?php echo $tip_percent ?></span>
                                                </td>
                                                <td width="10"></td>
                                                <td mc:edit="block-17" class="text-01" style="font: 28px/35px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;" align="right">
                                                        $<?php echo number_format($order->tip, 2)?>
                                                </td>
                                        </tr>
                                </table>
                        </td>
                </tr>
                <!-- row -->
                <tr mc:repeatable="repeatable-08">
                        <td class="textblock-01" style="padding: 25px 35px 28px; border-top: 1px solid #d7dbdb;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                                <td mc:edit="block-18" class="text-01" style="font: bold 28px/35px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;">
                                                        Total <span style="font-size:16px;"><?php echo $promo_amount ?></span>
                                                </td>
                                                <td width="10"></td>
                                                <td mc:edit="block-19" class="text-01" style="font: bold 28px/35px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;" align="right">
                                                        $<?php echo number_format($order->amount, 2)?>
                                                </td>
                                        </tr>
                                </table>
                        </td>
                </tr>
        </table>
        <?php
    }
    
        
}
