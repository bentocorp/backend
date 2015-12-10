<?php namespace Bento\Order\ItemList;


use Bento\Model\OrderItem;
use Bento\core\Util\NumUtil;


class AddonListList implements OrderItemListInterface {

    
    private $pk_Order;
    
    # The list as a JSON object. There should only be one.
    private $itemJson = NULL;
    
    # Raw DB rows. Not Eloquent models.
    private $isRowsWarm = false;
    private $rows = NULL;
    
    
    public function __construct($pk_Order) 
    {
        $this->pk_Order = $pk_Order;
    }
    
    
    public function writeItems() 
    {
        // For the only AddonList
        // For the items in the AddonList
        foreach ($this->itemJson->items as $addon) 
        {
            ## Insert into OrderItem

            $oi = new OrderItem;

            $oi->fk_Order = $this->pk_Order;
            $oi->fk_item = $addon->id;
            $oi->item_type = 'Addon';
            $oi->unit_price_paid = $addon->unit_price;
            $oi->qty = $addon->qty;

            $oi->save();
        }
    }
    
    
    public function addItem(\stdClass $item)
    {
        // There should only be one.
        $this->itemJson = $item;
    }
    
    
    public function printEmailReceipt()
    {
        // Warm up the rows
        $this->rowsInit();
        
        foreach ($this->rows as $row)
        {
            $quantity = '';
            
            $unitPrice  = NumUtil::formatPriceForEmail($row->unit_price_paid);
            $totalPrice = NumUtil::formatPriceForEmail($row->unit_price_paid * $row->qty);
            
            if ($row->qty > 1)
                $quantity = " ($row->qty @ $$unitPrice)";
            
            ?>
            <table width="100%" cellpadding="0" cellspacing="0" style="padding-bottom:15px;">
                <tr>
                    <td mc:edit="block-12" class="text-01" style="font: 28px/25px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;">
                            <?php echo $row->name ?><span style="font-size:16px;"><?php echo $quantity ?></span>
                            <!-- <br><span style="font-size:16px;">&nbsp;</span> -->
                    </td>
                    <td width="10"></td>
                    <td mc:edit="block-13" valign="top" class="text-01" style="font: 28px/25px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;" align="right">
                            $<?php echo $totalPrice ?>
                    </td>
                </tr>
            </table>
            <?php
        }
    }
    
    
    public function getOrderString(& $orderStr)
    {
        // Warm up the rows
        $this->rowsInit();
        
        // Exit if empty
        if (count($this->rows) == 0)
            return;
        
        $totalQty = 0;
        $orderStrTmp = '';
        
        // Loop first to get the master total, then assemble 
        foreach ($this->rows as $row)
        {
            $orderStrTmp .= "({$row->qty}x) $row->name \\n";
            $totalQty += $row->qty;
        }
        
        // Assemble
        
        $orderStr .= "余分 {$totalQty}x Add-ons: \\n ===== \\n";
        
        $orderStr .= $orderStrTmp;
        
        $orderStr .= "===== \\n\\n";
    }
    
    
    private function rowsInit()
    {
        // Done if already warm from DB
        if ($this->isRowsWarm)
            return;
        
        $this->isRowsWarm = true;
        
        $items = OrderItem::getItemsByOrder($this->pk_Order, 'Addon');
        
        $this->rows = $items;
    }
    
}
