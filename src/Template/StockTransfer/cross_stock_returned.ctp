<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$req_type = 'dynamic';
	if($this->request->query){
		$from_date = $this->request->query['from_date'];
		$to_date = $this->request->query['to_date'];
		$kiosk = $this->request->query['data']['kiosk'];
		if(array_key_exists('req_type',$this->request->query)){
			$req_type = $this->request->query['req_type'];
		}
	}
?>
<div class="kioskOrders index">
	
	<strong><span style="font-size: 17px;">Kiosk to Warehouse:</span></strong>
	<?php if(!$transferredByKiosk){
		echo "<h4>No result found!</h4>";
	}else{?>
	<h4>Sale from <?=$from_date;?> to <?=$to_date;?> for <?=$kiosks[$kiosk];?></h4>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Product Code</th>
		<th>Product</th>
		<th>Category</th>
		<th>Cost Price</th>
		<th>Quantity</th>
		<th>Amount</th>
		<th>Return Date</th>
	</tr>
	</thead>
	<tbody>
		<?php
		$kskProductCode = $kskProduct = '--';
		$kskCostPrice = $sumCostPrice = $totalCostPrice = 0;
		foreach($transferredByKiosk as $key=>$kioskData){
            //pr($kioskData);die;
			$product_cat_name=  $cat_name[$product_cats[$kioskData['product_id']]];
			if($kioskData['quantity']>0){
			if(array_key_exists($kioskData['product_id'],$productArr)){
				$kskProductCode = $productArr[$kioskData['product_id']]['product_code'];
				$kskProduct = $productArr[$kioskData['product_id']]['product'];
				if($req_type == 'fixed'){
                    $kskCostPrice = $kioskData['cost_price'];
					$sumCostPrice = $kioskData['quantity']*$kioskData['cost_price'];;
				}else{
					$kskCostPrice = $productArr[$kioskData['product_id']]['cost_price'];
					$sumCostPrice = $kioskData['quantity']*$kskCostPrice;
				}
				$totalCostPrice+=$sumCostPrice;
			}	
		?>
		<tr>
			<td><?php echo $kskProductCode;?></td>
			<td><?php echo $kskProduct;?></td>
			<td><?php echo $product_cat_name;?></td>
			<td><?php echo $CURRENCY_TYPE.$kskCostPrice;?></td>
		
			<td><?php echo $kioskData['quantity'];?></td>
			<td><?php echo $CURRENCY_TYPE.$sumCostPrice;?></td>
			<td><?php echo date('d-m-Y h:i A',strtotime($kioskData['created']));//$this->Time->format('d-m-Y h:i A', $kioskData['created'],null,null);?></td>
		</tr>
		<?php }
		}?>
		<tr>
			<td>&nbsp;</td>
			<td colspan="3"><strong>Total</strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalCostPrice;?></strong></td>
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
<script>
	$(function() {
		$( "#datepicker_1" ).datepicker({ dateFormat: "dd-mm-yy" })
	});
	
	$(function() {
		$( "#datepicker_2" ).datepicker({ dateFormat: "dd-mm-yy" })
	});
</script>
<script>

</script>