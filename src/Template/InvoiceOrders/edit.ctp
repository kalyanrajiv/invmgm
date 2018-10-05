<div class="invoiceOrders form">
<?php echo $this->Form->create('InvoiceOrder',array('autocomplete'=>'off'));?>
	<fieldset>
		<legend><?php echo __('Edit Performa'); ?></legend>
		<table>
			<tr>
				<th>Product</th>
				<th>Code</th>
				
				<th>Sale Price</br>/item</th>
				<th>Quantity</th>
				<th>Available Qty</th>
				<th>Discount Price</br>/item</th>
				<th>Created On</th>
				<th>Action</th>
			</tr>
		
	<?php
   // pr($this->request['data']);
		foreach($this->request['data']['invoice_order_details'] as $orderDetail){
			//pr($orderDetail);
			$orderDetailId = $orderDetail['id'];
			$quantity = $orderDetail['quantity'];
			$discount = $orderDetail['discount'];
			
			if($discount < 0){
				$priceWithoutVAT = $price1 = round($orderDetail['price'],2);
			}else{
				$priceWithoutVAT = $price1 = number_format(round($orderDetail['price']/(1+$vatItem),2),2);	
			}
			
			if($discount < 0){
				$price = $orderDetail['price'];	
			}elseif($discount == 0){
				$price = $priceWithoutVAT;
			}else{
				$price = $priceWithoutVAT - ($priceWithoutVAT*$discount/100);
			}
			
			
			//$priceWotVat = $orderDetail['price'] - $price1;
			$truncatedProduct = \Cake\Utility\Text::truncate(
				$products[$orderDetail['product_id']],
				50,
				[
				    'ellipsis' => '...',
				    'exact' => false
				]
			);
			echo "<tr>";
			echo "<td>".$this->Html->link(
						      $truncatedProduct,
						      array('controller'=>'products','action'=>'view',$orderDetail['product_id']),
						      array('title'=>$products[$orderDetail['product_id']],'alt'=>$products[$orderDetail['product_id']])).
				"</td>";
				echo "<td>".$products_code[$orderDetail['product_id']]."</td>";
			echo "<td>$priceWithoutVAT</td>";
			echo "<td>".$this->Form->input('quantity',array('label'=>false,'value'=>$quantity,'name'=>"InvoiceOrder[quantity][]")).
			$this->Form->input('null',array('type'=>'hidden','value'=>$orderDetailId,'name'=>"InvoiceOrder[id][]")).
			$this->Form->input('null',array('type'=>'hidden','value'=>$discount,'name'=>"InvoiceOrder[discount][]")).
			$this->Form->input('null',array('type'=>'hidden','value'=>$price,'name'=>"InvoiceOrder[price][]")).
			"</td>";
			echo "<td>".$products_quantity[$orderDetail['product_id']]."</td>";
			echo "<td>".number_format($price,2)."</td>";
			echo "<td>".date('d-m-Y',strtotime($orderDetail['created']))."</td>";
	?>
			<td class="actions">
			<?php echo $this->Form->postLink(__('Delete'), array('controller'=>'invoice_order_details','action' => 'delete', $orderDetailId),
					array('escapeTitle' => false, 'title' => 'Delete','confirm' => "Are you sure you want to delete?",'block' => true)); ?>
			</td>
			</tr>
	<?php	}//__('Are you sure you want to delete # %s?', $orderDetailId) ?>
	
		</table>
	</fieldset>
	<div class="submit">
	
	<?php
	$options=array('div'=>false,'label'=>'Update');
	echo $this->Form->Submit('Update',$options);
    echo $this->Form->end();
	echo $this->fetch('postLink');
	?>
	</div>
	<p><sup>**</sup>Note: Add more products removed as it needs to be refined.</p>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
<?php $performaID = $this->request['data']['id'];?>
		<li><?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('InvoiceOrder.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('InvoiceOrder.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Performa'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('View Performa Receipt'), array('action' => 'view',$performaID)); ?></li>
		
	</ul>
</div>
<script>
	$("input[id='InvoiceOrderQuantity']").keydown(function (event) {
		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||  event.keyCode == 183 ||
		event.keyCode == 110) {
			;
		} else {
			event.preventDefault();
		}
		
		if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
</script>