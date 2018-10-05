<div class="kioskProductSales index">
	
	<table style="width: 30%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$session_basket = $this->request->Session()->read('stBasket');
		if(!empty($session_basket)){
		echo "<td style='padding-top: 10px;'>".$this->Html->link('Update stock',array('action'=>'update_center_stock'),array('name'=>'dispatch'))."</td>
							<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>'stock_transfer_by_kiosk'))."</td>";
		}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('type'=>'post'));
	
		$session_basket = $this->request->Session()->read('stBasket');
		$basketStrDetail = '';
		if(is_array($session_basket)){
			
			foreach($session_basket as $productId=>$productDetails){
				$basketStrDetail.= "<tr>
				<td>".$productIds[$productId]."</td>
				<td>".$productArr[$productId]."</td>
				<td><input type='text' value='{$productDetails['quantity']}' name='CheckOut[$productIds[$productId]]' style='width: 50px;'/></td>
				<td>".$productDetails['price']."</td>
				<td>".$productDetails['remarks']."</td>
				<td>".$this->Html->link('delete',array('action'=>'delete_product_by_kiosk_from_session',$productId),array('id'=>$productId,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
				</tr>";
			}
			
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th>Product code</th>
					<th>Product</th>
					<th>Quantity</th>
					<th>Selling price</th>
					<th>Remarks</th>
				</tr>".$basketStrDetail.
				"<tr><td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><input type='submit' name='update_quantity' value='Update Quantity'/></td>
					<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Update stock',array('action'=>'update_center_stock'),array('name'=>'dispatch'))."</span></td>
					<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Edit basket',array('action'=>'stock_transfer_by_kiosk'))."</span></td>
				</tr>
				</table>";
			}
			
			$totalItems = count($session_basket);
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
	function reply_click(clicked_id)
	{
	    if(!confirm("Do you really want to delete "+clicked_id))
	    return false;
	}
</script>