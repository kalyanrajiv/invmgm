<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$from_date = $to_date = $kiosk = '';
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
	
	<strong><?php #print_r($kioskOrders);
	$rootURL = "";//$this->html->url('/', true);
	$queryStr = "";
	//pr($this->request);
	if( isset($this->request->query['from_date']) ){
			$queryStr.="from_date=".$this->request->query['from_date'];
	}
	if( isset($this->request->query['to_date']) ){
			$queryStr.="&to_date=".$this->request->query['to_date'];
	}
	if( isset($this->request->query['req_type']) ){
			$queryStr.="&req_type=".$this->request->query['req_type'];
	}
	if( isset($this->request->query['data']) ){
		if(isset($this->request->query['data']['kiosk'])){
			$queryStr.="&kiosk=".$this->request->query['data']['kiosk'];
		}
	}
	echo __('<span style="font-size: 17px;">Warehouse to Kiosk:</span>'); ?></strong>&nbsp;<a href="<?php echo $rootURL;?>export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2>
	<?php if(!$transferredByWarehouse){
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
		<th>Transfer Date</th>
		
		
	</tr>
	</thead>
	<tbody>
		<?php
		//pr($transferredByWarehouse);die;
		$whCostPrice = $sumCostPrice = $totalCostPrice = 0;
		$whProductCode = $whProduct = '--';
		foreach($transferredByWarehouse as $key => $warehouseData){
            //pr($warehouseData);die;
			$prodQty = $warehouseData['quantity'];
			$product_cat_name=  $cat_name[$product_cats[$warehouseData['product_id']]];
			if($prodQty > 0){
				if(array_key_exists($warehouseData['product_id'],$productArr)){
					$whProductCode = $productArr[$warehouseData['product_id']]['product_code'];
					$whProduct = $productArr[$warehouseData['product_id']]['product'];
					if($req_type == 'fixed'){
						$whCostPrice = $warehouseData['cost_price'];
						$sumCostPrice = $prodQty * $warehouseData['cost_price'];
					}else{
						$productID = $warehouseData['product_id'];
						$whCostPrice = $productArr[$productID]['cost_price'];
						$sumCostPrice = $prodQty * $whCostPrice;
					}
					$totalCostPrice += $sumCostPrice;
				}
		?>
		<tr>
			<td><?php echo $whProductCode;?></td>
			<td><?php echo $whProduct;?></td>
			<td><?php echo $product_cat_name;?></td>
			<td><?php echo $CURRENCY_TYPE.$whCostPrice;?></td>
			<td><?php echo $prodQty;?></td>
			<td><?php echo $CURRENCY_TYPE.$sumCostPrice;?></td>
			<td><?php echo date('d-m-Y h:i A',strtotime($warehouseData['created']));//$this->Time->format('d-m-Y h:i A', $warehouseData['created'],null,null);?></td>
		</tr>
		<?php }
		}?>
		<tr>
			<td>&nbsp;</td>
			<td colspan="1"><strong>Total</strong></td>
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