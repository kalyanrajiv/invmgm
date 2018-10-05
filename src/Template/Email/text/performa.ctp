<div class="invoiceOrders view">
<h2><?php echo __('Performa'); ?></h2>
	<table border="1" style="">
		<tr>
			<th colspan="7" align="center"><?php
			/*if((int)$invoiceOrder['InvoiceOrder']['kiosk_id']){
				echo $kiosk[$invoiceOrder['InvoiceOrder']['kiosk_id']];
			}else{
				echo "www.bolowaheguru.co.uk";
			}*/
			echo "www.bolowaheguru.co.uk";
			?>
			</th>
		</tr>
		<tr>
			<th colspan="2">Order # <?= $invoiceOrder['InvoiceOrder']['id'];?></th>
			<th colspan="3">Customer # <?= $invoiceOrder['InvoiceOrder']['customer_id'];?></th>
			<th>Date:</th>
			<td><?= date("d-m-Y");?></td>
		</tr>
		<tr>
			<td><strong>Name:</strong></td>
			<td colspan="2"><?= $invoiceOrder['InvoiceOrder']['fname'];?> <?= $invoiceOrder['InvoiceOrder']['lname'];?></td>
			<td><strong>Email</strong></td>
			<td><?= $invoiceOrder['InvoiceOrder']['email'];?></td>
			<td><strong>Mobile:</strong></td>
			<td><?= $invoiceOrder['InvoiceOrder']['mobile'];?></td>
		</tr>
		<tr>
			<td><strong>Delivery Address:</strong></td>
			<td colspan="6"><?= $invoiceOrder['InvoiceOrder']['del_address_1'];?> <?= $invoiceOrder['InvoiceOrder']['del_address_2'];?></td>
		</tr>
		<tr>
			<td><strong>City:</strong></td>
			<td><?= $invoiceOrder['InvoiceOrder']['del_city'];?></td>
			<td><strong>State:</strong></td>
			<td colspan="2"><?= $countyOptions[$invoiceOrder['InvoiceOrder']['del_state']];?></td>
			<td><strong>Postal Code:</strong></td>
			<td><?= $invoiceOrder['InvoiceOrder']['del_zip'];?></td>
		</tr>
		<tr>
			<th colspan="7">Purchase Details</th>
		</tr>
	
		<tr>
			<th>Created On</th>
			<th>Product</th>
			<th>Price</th>
			<th>Quantity</th>
			<th>Discount %</th>
			<th>Vat %</th>
			<th>Amount</th>
		</tr>
		<?php
		$amount = 0;
		$totalVat = 0;
		$totalDiscount = 0;
		$netAmount = $invoiceOrder['InvoiceOrder']['amount'];
		$bulkDiscountPercentage = $invoiceOrder['InvoiceOrder']['bulk_discount'];
		foreach($invoiceOrder['InvoiceOrderDetail'] as $key => $orderDetail){
			$discount = $orderDetail['price']*$orderDetail['discount']/100*$orderDetail['quantity'];
			$discountAmount = ($orderDetail['quantity']*$orderDetail['price'])-$discount;
			$amount+=$discountAmount;
			$bulkDiscountValue = $amount - $netAmount;
			$vatAmount = ($discountAmount*$vat/100);
			$totalVat+=$vatAmount;
			$totalDiscount+=$discount;
			?>
			<tr>
				<td><?= $orderDetail['created'];?></td>
				<td><?= $productName[$orderDetail['product_id']];?></td>
				<td><?= $orderDetail['price'];?></td>
				<td><?= $orderDetail['quantity'];?></td>
				<td><?= $orderDetail['discount'];?></td>
				<td><?= $vat;?></td>
				<td><?= $discountAmount;?></td>
			</tr>
		<?php }?>
		
		<tr>
			<th colspan="6">Total Vat</th>
			<td><?=$totalVat;?></td>
		</tr>
		<tr>
			<th colspan="6">Total Discount</th>
			<td><?= $totalDiscount;?></td>
		</tr>
		<tr>
			<th colspan="6">Bulk Discount (<?=$bulkDiscountPercentage;?> %)</th>
			<td><?= $bulkDiscountValue;?></td>
		</tr>
		<tr>
			<th colspan="6">Total Amount</th>
			<td><?=$netAmount;?></td>
		</tr>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
	</ul>
</div>
