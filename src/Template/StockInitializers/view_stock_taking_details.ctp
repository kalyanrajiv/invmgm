<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php $final_gain = $total_gain - $total_loss;?>
<div class="stock_initializer form">
    <h2>Stock Taking Details</h2>
    <h4>Reference: <?php echo $reference;?>, Kiosk: <?=$kioskName;?></h4>
	<h4>Total Loss: <?php echo $total_loss;?> Total Gain: <?php echo $total_gain;?> Net Gain: <?php echo round($final_gain,2) ;?> </h4>
    <table>
        <tr>
            <th><?php echo $this->Paginator->sort('user_id');?></th>
            <th><?php echo $this->Paginator->sort('product_id');?></th>
            <th><?php echo $this->Paginator->sort('product_code');?></th>
            <th><?php echo $this->Paginator->sort('selling_price');?></th>
            <th><?php echo $this->Paginator->sort('quantity');?></th>
            <th><?php echo $this->Paginator->sort('difference');?></th>
            <th><?php echo $this->Paginator->sort('created');?></th>
        </tr>
<?php
	$lossAmt = 0;
	$gainAmt = 0;
	foreach($stockTakingDetail as $key => $stockTaking){
        if($stockTaking->quantity < 0){
            $qtt = -$stockTaking->quantity;
        }else{
            $qtt = $stockTaking->quantity;
        }
        
        if($stockTaking->difference == -1){
            $diff = 'Less';
			$lossAmt += (-1) * $stockTaking->selling_price * $stockTaking->quantity;
        }else{
            $diff = 'More';
			$gainAmt += $stockTaking->quantity * $stockTaking->selling_price;
        }
?>
        <tr>
            <td><?php echo $userName[$stockTaking->user_id];?></td>
            <td><?php echo $productName[$stockTaking->product_id];?></td>
            <td><?php echo $stockTaking->product_code;?></td>
            <td><?php echo $CURRENCY_TYPE.$stockTaking->selling_price;?></td>
            <td><?php echo $qtt;?></td>
            <td><?php echo $diff;?></td>
            <td><?php echo date("d-m-y g:i A",strtotime($stockTaking->created));
			
			//$this->Time->format('d-m-y g:i A',$stockTaking->created,null,null);?></td>
            
        </tr>
    <?php
        $netGain = $moreQttData[0]['moreQuantityData']+$lessQttData[0]['lessQuantityData'];//less is already in negative, so adding
    } ?>
		<tr>
            <th>Total Gain:</th>
            <td><?php echo "<b>".$CURRENCY_TYPE.$gainAmt."</b>" ;?></td>
            <th>Total Loss:</th>
            <td><?php echo "<b>".$CURRENCY_TYPE.$lossAmt."</b>" ;?></td>
            <th>Net Gain:</th>
            <td><?php echo "<b>".$CURRENCY_TYPE.($gainAmt - $lossAmt)."</b>" ;?></td>
        </tr>
    </table>
    <p>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Stock References'), array('action' => 'stock_taking_reference_list')); ?></li>
        <li><?php echo $this->Html->link(__('Stock Taking'), array('action' => 'stock_taking')); ?></li>
    </ul>
</div>
