<?php namespace Bento\Order\ItemList;


use Bento\Model\CustomerBentoBox;
use Bento\Model\OrderItem;
use Bento\core\Util\NumUtil;


class CustomerBentoBoxList implements OrderItemListInterface {

    
    private $pk_Order;
    
    # The items as JSON objects
    private $itemsJson = array();
    
    # Raw DB rows. Not Eloquent models.
    private $isRowsWarm = false;
    private $rows = NULL;
    
    
    public function __construct($pk_Order) 
    {
        $this->pk_Order = $pk_Order;
    }
    
    
    public function writeItems() 
    {
        #var_dump($this->itemsJson); die(); #0;
        
        // For each CustomerBentoBox
        foreach ($this->itemsJson as $cbb) 
        {
            ## Insert the CustomerBentoBox
            
            // Make a CustomerBentoBox
            $box = new CustomerBentoBox;
            $box->fk_Order = $this->pk_Order;
            
            // Now for each thing in the box
            foreach($cbb->items as $item) {
                $fk = "fk_$item->type";
                $box->{$fk} = $item->id;
                $box->unit_price_paid = $cbb->unit_price;
            }
            
            // Save the box
            $box->save();
            
            ## Insert into OrderItem
            
            $oi = new OrderItem;
            
            $oi->fk_Order = $this->pk_Order;
            $oi->fk_item = $box->pk_CustomerBentoBox;
            $oi->item_type = 'CustomerBentoBox';
            $oi->unit_price_paid = $cbb->unit_price;
            
            $oi->save();
        }
    }
    
    
    public function addItem(\stdClass $item)
    {
        #var_dump($item); die(); #0;
        $this->itemsJson[] = $item;
        #var_dump($this->itemsJson); #0;
    }
    
    
    public function printEmailReceipt() 
    {
        // Warm up the rows
        $this->rowsInit();
        
        foreach ($this->rows as $row)
        {
            $sidesStr = '';
            
            $row->side1_name != '' || $row->side1_name != NULL ? $sidesStr .= $row->side1_name : '';
            $row->side2_name != '' || $row->side2_name != NULL ? $sidesStr .= ", $row->side2_name" : '';
            $row->side3_name != '' || $row->side3_name != NULL ? $sidesStr .= ", $row->side3_name" : '';
            $row->side4_name != '' || $row->side4_name != NULL ? $sidesStr .= ", $row->side4_name" : '';
            
            ?>
            <table width="100%" cellpadding="0" cellspacing="0" style="padding-bottom:10px;">
                <tr>
                    <td mc:edit="block-12" class="text-01" style="font: 28px/25px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;">
                            <?php echo $row->main_name ?><br>
                            <?php if ($sidesStr != ''): ?>
                            <span style="font-size:16px;"><?php echo $sidesStr ?></span>
                            <?php endif; ?>
                    </td>
                    <td width="10"></td>
                    <td mc:edit="block-13" valign="top" class="text-01" style="font: 28px/25px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;" align="right">
                      $<?php echo NumUtil::formatPriceForEmail($row->unit_price_paid) ?>
                    </td>
                </tr>
            </table>
            <?php
        }
    }
    
    
    private function rowsInit()
    {
        // Done if already warm from DB
        if ($this->isRowsWarm)
            return;
        
        $this->isRowsWarm = true;
        
        $boxes = CustomerBentoBox::getBentoBoxesByOrder($this->pk_Order);
        
        $this->rows = $boxes;
    }
    
}
