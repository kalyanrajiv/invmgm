<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	if($this->request->query){
		$from_date = $this->request->query['from_date'];
		$to_date = $this->request->query['to_date'];
		$kiosk = $this->request->query['data']['kiosk'];
	}
?>
<div class="kioskOrders index">
	<strong><?php //pr($mobileResale);
	echo __('<span style="font-size: 17px;">Mobile Details:</span>'); ?></strong>
	<?php if(!$mobileResale){
		echo "<h4>No result found!</h4>";
	}else{?>
	<h4>Sale from <?=$from_date;?> to <?=$to_date;?> for <?=$kiosks[$kiosk];?></h4>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Resale Id</th>
		<th>Purchase Id</th>
		<th>Refunded</th>
		<th>Cost Price</th>
		<th>Date</th>
	</tr>
	</thead>
	<tbody>
		<?php
		$totalMobileCost = 0;
                $less = 0;
                $add = 0;
		foreach($mobileResale as $key=>$mobileData){
			if($mobileData['id'] == 0){
				continue;
			}
			if($mobileData['mobile_purchase_id'] == 0){
				continue;
			}
			if($mobileData['refund_status']>0){
				$refund = "Yes";
			}else{
				$refund = "No";
			}
			?>
		<tr>
			<td><?php echo $this->Html->link($mobileData['id'],array('controller'=>'mobile_re_sales','action'=>'view',$mobileData['id']));?></td>
			<td><?php echo $this->Html->link($mobileData['mobile_purchase_id'],array('controller'=>'mobile_purchases','action'=>'view',$mobileData['mobile_purchase_id']));?></td>
			<td><?php echo $refund;?></td>
			<td><?php echo $CURRENCY_TYPE.$costPrice[$mobileData['mobile_purchase_id']];?></td>
			<td><?php echo date('d-m-Y h:i A',strtotime($mobileData['created']));//$this->Time->format('d-m-Y h:i A', $mobileData['created'],null,null);?></td>
		</tr>
		<?php
			if($mobileData['refund_status']>0){
				$less+= $cost_price = -$costPrice[$mobileData['mobile_purchase_id']];
			}else{
				$add+= $cost_price = $costPrice[$mobileData['mobile_purchase_id']];
			}
			
			$totalMobileCost+=$cost_price;
		} ?>
		<tr>
			<td colspan='3'><strong>Total</strong></td>
			<td colspan='2'><?php echo "<strong>".$CURRENCY_TYPE.$totalMobileCost."(".$CURRENCY_TYPE.$add." - ".$CURRENCY_TYPE.-$less.")</strong>";?></td>
		</tr>
	</tbody>
	</table>
	<?php } ?>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Dispatched Products'), array('controller' => 'stock_transfer', 'action' => 'dispatched_products')); ?> </li>
		<li><?php echo $this->Html->link(__('Sale Summary'), array('controller' => 'stock_transfer', 'action' => 'summary_sale')); ?> </li>
	</ul>
</div>