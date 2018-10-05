<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="warehouseStocks view">
<strong><span style="font-size: 17px;color: red;"><?php echo 'Reference Stock</span> ('.$dateAdded;?>)</strong></br>
<b>**Current Stock = Prev Qty + Newly entry</b>
	<table>
		<tr>
			<th>Product Code</th>
			<th>Product</th>
			<th><?php echo  (__('user_id')); ?></th>
			<th><?php echo  (__('warehouse_vendor_id')); ?></th>
			<th><?php echo  (__('reference_number','Reference')); ?>Number</th>
			<th><?php echo  (__('quantity','Qty')); ?></th>
			<th><?php echo  (__('current_stock')); ?></th>
			<th><?php echo  (__('price')); ?></th>
			<th><?php echo  (__('in_out')); ?></th>
			<th><?php echo (__('remarks')); ?></th>
		</tr>
		<?php
		$EXT_RETAIL = Configure::read('EXT_RETAIL');
		if(!empty($EXT_RETAIL)){
			$site = $EXT_RETAIL[0];
		}
		$current_site = $path = dirname(__FILE__);
		$isboloRam = strpos($current_site,$site);
		
		$count = $total_cur_stock = $total_qty = $totalValue = 0;	
		foreach($dateWiseStock as $key=>$dateWiseStockInfo){
			$count ++; 
			$product_code = $productArr[$dateWiseStockInfo['product_id']]['product_code'];
			$product = $productArr[$dateWiseStockInfo['product_id']]['product'];
			$user_id = $dateWiseStockInfo['user_id'];
			$warehouse_vendor_id = $dateWiseStockInfo['warehouse_vendor_id'];
			$reference_number = $dateWiseStockInfo['reference_number'];
			$quantity = $dateWiseStockInfo['quantity'];
			
			
			if($isboloRam != false){
				$price = $productArr[$dateWiseStockInfo['product_id']]['cost_price'];
			}else{
				$price = $dateWiseStockInfo['price'];
			}
			
			$in_out = $dateWiseStockInfo['in_out'];
			$remarks = $dateWiseStockInfo['remarks'];
			$totalValue = $totalValue + ($quantity*$price);
			$current_stock = $dateWiseStockInfo['current_stock'];
			
			$total_qty += $quantity;
			$total_cur_stock += $current_stock;
		?>
		<tr>
			<td><?php echo $product_code;?></td>
			<td><?php echo $product;?></td>
			<td><?php echo $userName[$user_id];?></td>
			<td><?php echo $vendorName[$warehouse_vendor_id];?></td>
			<td><?php echo $reference_number;?></td>
			<td><?php echo $quantity;?></td>
			<td><?php echo $current_stock;?></td>
			<td><?php echo $CURRENCY_TYPE.$price; ?></td>
			<td><?php echo $in_out == 1 ? 'Stock In' : 'Stock Out';?></td>
			<td><?php echo $remarks;?></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan='5'>&nbsp;</td>
			<td><?php echo $total_qty;?></td>
			<td><?php echo $total_cur_stock;?></td>
			<td><strong>Total</strong></br><?php echo number_format($totalValue, 2, null, ',');?></td>
			<td colspan='3'>&nbsp;</td>
		</tr>
		<tr>
			<td colspan='3'>
				Total Number Of Rows : <?php echo $count; ?>
			</td>
		</tr>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List </br>Warehouse Stocks'), array('action' => 'index'),array('escape'=>false,'style'=>"width: 119px;")); ?> </li>
		<li><?php echo $this->Html->link(__('View Stock In/Out'), array('action' => 'reference_stock'),array('escape'=>false,'style'=>"width: 119px;")); ?> </li>
		<li><?php echo $this->Html->link(__('List Suppliers'), array('controller' => 'warehouse_vendors', 'action' => 'index'),array('escape'=>false,'style'=>"width: 119px;")); ?> </li>
		<li><?php echo $this->Html->link(__('New Supplier'), array('controller' => 'warehouse_vendors', 'action' => 'add'),array('escape'=>false,'style'=>"width: 119px;")); ?> </li>
	</ul>
</div>
