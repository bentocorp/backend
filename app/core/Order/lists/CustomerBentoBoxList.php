<?php namespace Bento\Order\ItemList;


use Bento\Model\CustomerBentoBox;
use Bento\Model\OrderItem;
use Bento\core\Util\NumUtil;
use Bento\core\Util\StrUtil;


class CustomerBentoBoxList implements OrderItemListInterface {

    
    private $pk_Order;
    
    # The items as JSON objects
    private $itemsJson = array();
    
    # Raw DB rows. Not Eloquent models.
    private $isRowsWarm = false;
    private $rows = NULL;
    
    private $totalQty = 0;
    
    
    public function __construct($pk_Order) 
    {
        $this->pk_Order = $pk_Order;
    }
    
    
    public function getTotalQty() {
        return $this->totalQty;
    }
    
    public function getContentsName() {
        return "Bento";
    }
    
    public function getContentsNamePlural() {
        return "Bentos";
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
    
    
    public function getOrderString(& $orderStr)
    {
        // Warm up the rows
        $this->rowsInit();
        
        $boxCount = 1;
        $bentoBoxes = $this->rows;
        $n = count($bentoBoxes);
        
        $this->totalQty = $n;
        
        foreach ($bentoBoxes as $box) {

            $main_name  = StrUtil::encodeForHouston($box->main_name);
            $side1_name = StrUtil::encodeForHouston($box->side1_name);
            $side2_name = StrUtil::encodeForHouston($box->side2_name);
            $side3_name = StrUtil::encodeForHouston($box->side3_name);
            $side4_name = StrUtil::encodeForHouston($box->side4_name);
            
            $orderStr .= "旦 BENTO $boxCount of $n: \\n ===== \\n";
            $orderStr .= "$box->main_label - $main_name \\n";
            $orderStr .= "$box->side1_label - $side1_name \\n"; 
            $orderStr .= "$box->side2_label - $side2_name \\n";
            $orderStr .= "$box->side3_label - $side3_name \\n";
            $orderStr .= "$box->side4_label - $side4_name \\n ===== \\n\\n";
            
            $boxCount++;
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
