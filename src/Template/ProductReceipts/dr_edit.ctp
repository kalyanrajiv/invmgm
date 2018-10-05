<div class="invoiceOrders form">
<?php echo $this->Form->create('InvoiceOrder',array('autocomplete'=>"off")); ?>
	<fieldset>
		<legend><?php echo 'Edit Invoice (Bulk Discount:'.$bulkDiscount; ?>%)</legend>
		<table>
			<tr>
				<th>Product<br/>Code</th>
				<th>Product</th>
				<th>sale Price</th>
				<th>Available Quantity</th>
				<th>Sold Quantity</th>
				<th>Add quantity</th>
				<th>Discount Price</th>
				<th>Created On</th>
			</tr>
		
	<?php
	$productArr = array();
	$centralQuantity = 0;
		//pr($this->request['data']);die;
        foreach($this->request['data'] as $key=>$productSale){
			$centralQuantity = $productQuantityArr[$productSale['product_id']];
			$receiptId = $productSale['id'];
			$quantity = $productSale['quantity'];
			$discount = $productSale['discount'];
			$price = $productSale['sale_price'];
			$truncatedProduct = \Cake\Utility\Text::truncate(
				$productName[$productSale['product_id']],
				50,
				[
				    'ellipsis' => '...',
				    'exact' => false
				]
			);
			echo "<tr>";
			echo "<td>".$productCode[$productSale['product_id']]."</td>";
			echo "<td>".$this->Html->link(
						      $truncatedProduct,
						      array('controller'=>'products','action'=>'view',$productSale['product_id']),
						      array('title'=>$productName[$productSale['product_id']],'alt'=>$productName[$productSale['product_id']])).
				"</td>";
			echo "<td>".$productSale['sale_price']."</td>";
			echo "<td>".$centralQuantity."</td>";
			echo "<td>".$quantity."</td>";
			echo "<td>".$this->Form->input('add_quantity',array('label'=>false,'name'=>"ProductReceipt[quantity][]",'id'=>"add_quantity_$key",'style'=>'width:40px;')).
			$this->Form->input('null',array('type'=>'hidden','value'=>$centralQuantity,'name'=>"ProductReceipt[centralQuantity][]")).
			$this->Form->input('null',array('type'=>'hidden','value'=>$receiptId,'name'=>"ProductReceipt[id][]")).
			$this->Form->input('null',array('type'=>'hidden','value'=>$discount,'name'=>"ProductReceipt[discount][]")).
			$this->Form->input('null',array('type'=>'hidden','value'=>$price,'name'=>"ProductReceipt[price][]")).
			"</td>";
            $dis = $productSale['discount'];
            $after_dis_value = $productSale['sale_price'] - ($productSale['sale_price'] * ($dis/100));
            $after_dis_value = round($after_dis_value,2);
            
			echo "<td>".$after_dis_value."</td>";
			echo "<td>".date('M jS, Y g:i A',strtotime($productSale['created']));//$this->Time->format('M jS, Y g:i A',$productSale['created'],null,null)."</td>";
	?>
			<td class="actions">
			<?php #echo $this->Form->postLink(__('Delete'), array('controller'=>'invoice_order_details','action' => 'delete', $receiptId),					array('escapeTitle' => false, 'title' => 'Delete'), __('Are you sure you want to delete # %s?', $receiptId)); ?>
			</td>
			</tr>
	<?php	} ?>
	
		</table>
	</fieldset>
	<div class="submit">
	<input type="submit" name="add_more_products" value="Add more products" />
	<?php
	$options=array('div'=>false,'label'=>'Update Invoice');
	echo $this->Form->Submit('Update Invoice',$options);
    echo $this->Form->end(); ?>
	</div>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('InvoiceOrder.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('InvoiceOrder.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Invoice Orders'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$("input[id*='add_quantity_']").keydown(function (event) {
		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||  event.keyCode == 183 ||
		event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab
			//event.keyCode == 46 for dot
			//event.keyCode == 190 for dot
		} else {
			event.preventDefault();
		}
		
		if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
</script>