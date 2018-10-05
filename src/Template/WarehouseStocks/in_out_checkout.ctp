<div class="kioskProductSales index">
	
	<table style="width: 30%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
		if(!empty($warehouseBasket)){
		#echo "<td style='padding-top: 10px;'>".$this->Html->link('Update stock',array('action'=>'update_stock'),array('name'=>'dispatch'))."</td>
							//<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>'index'))."</td>";
		}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('url'=>array('controller'=>'warehouse_stocks','action'=>'update_quantity_in_wh_basket')),array('type'=>'post'));
		$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
		$basketStrDetail = '';
		if(is_array($warehouseBasket)){
			
			foreach($warehouseBasket as $productId=>$productDetails){
				$basketStrDetail.= "<tr>
				<td>".$productCode[$productId]."</td>
				<td>".$productArr[$productId]."</td>
			 	<td>".$productDetails['new_rcp'] ."</td>
				<td>".$productDetails['new_rsp']."</td>
				<td>".$productDetails['price'] ."</td>
				<td>".$productDetails['new_selling_price']."</td>
				<td>".$this->Form->input('qtt',array('type'=>'text','name'=>"warehouse_stocks[$productId]",'value'=>$productDetails['quantity'],'label'=>false))."</td>
				<td>".$productInOut[$productDetails['in_out']]."</td>
				<td>".$productDetails['remarks']."</td>
				<td>".$this->Html->link('delete',array('action'=>'delete_product_from_warehousebasket',$productId),array('id'=>$productId,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
				</tr>";
			}
			
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th>Product code</th>
					<th>Product</th>
					<th>New RCP</th>
					<th>New RSP</th>
					<th style='width: 102px;'>New Cost Price</th>
					<th style='width: 102px;'>New Sale Price</th>
					<th>Quantity</th>
					<th>Type</th>
					<th>Remarks</th>
				</tr>".$basketStrDetail.
				"<tr><td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Update stock',array('action'=>'update_stock'),array('name'=>'dispatch','id'=>'dispatch_loading'))."</span></td>
					<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Edit basket',array('action'=>'index'))."</span></td>
					<td><input type='submit' value='Update Quantity'></td>
				</tr>
				</table>";
			}
			
			$totalItems = count($warehouseBasket);
			if($totalItems){
				echo "Total item Count:$totalItems<br/>$basketStr";
			}
			
		}else{
			echo "<h4>Please add products to the basket!!</h4>";
		}
	echo $this->Form->end();
	?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Sale'), array('action' => 'new_order')); ?></li>		
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'stock', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	$('#dispatch_loading').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>
<script>
	function reply_click(clicked_id)
	{
	    if(!confirm("Do you really want to delete "+clicked_id))
	    return false;
	}
</script>