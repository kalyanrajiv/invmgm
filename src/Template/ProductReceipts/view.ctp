<div class="productReceipts view">
<h2><?php #pr($productReceipt['KioskProductSale']);
echo __('Product Receipt');?></h2>
<?php //pr($productReceipt);?>
<h4><?php echo $this->Html->link('Generate Receipt', array('action' => 'generate_receipt', $productReceipt['id'],$kiosk_id))?></h4>
<?php echo $this->Html->link('Thermal receipt',array('controller' => 'prints','action'=>'generate-receipt',$productReceipt['id'],$kiosk_id)); ?>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($productReceipt['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer'); ?></dt>
		<dd>
			<?php echo $this->Html->link($productReceipt['customer_id'], array('controller' => 'customers', 'action' => 'view', $productReceipt

['customer_id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('First Name'); ?></dt>
		<dd>
			<?php echo h($productReceipt['fname']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Name'); ?></dt>
		<dd>
			<?php echo h($productReceipt['lname']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($productReceipt['email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile'); ?></dt>
		<dd>
			<?php echo h($productReceipt['mobile']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 1'); ?></dt>
		<dd>
			<?php echo h($productReceipt['address_1']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 2'); ?></dt>
		<dd>
			<?php echo h($productReceipt['address_2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('City'); ?></dt>
		<dd>
			<?php echo h($productReceipt['city']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('State'); ?></dt>
		<dd>
			<?php echo $productReceipt['state']; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Zip'); ?></dt>
		<dd>
			<?php echo h($productReceipt['zip']); ?>
			&nbsp;
		</dd>
		<dt><?php #echo __('Status'); ?></dt>
		<dd>
			<?php #echo h($productReceipt['ProductReceipt']['status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Receipt Created'); ?></dt>
		<dd>
			<?php echo date('M jS, Y g:i A',strtotime($productReceipt['created']));//$this->Time->format('M jS, Y g:i A',$productReceipt['created'],null,null); ?>
			&nbsp;
		</dd>		
	</dl>	
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('View Performas'), array('controller' => 'invoice_orders', 'action' => 'index')); ?></li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Products'); ?></h3>
	<?php if (!empty($kiosk_products_data)): ?>
	<table cellpadding = "0" cellspacing = "0" style="font-size: 11px">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Product'); ?></th>
		<th><?php echo __('Sold Qty'); ?></th>
		<th><?php echo __('Qty'); ?></th>
		<th><?php echo __('Returned Qty'); ?></th>
		<th><?php echo __('Sale Price'); ?></th>
		<th><?php echo __('Discount'); ?></th>
		<th><?php echo __('Dscnt Price'); ?></th>
		<th><?php echo __('Sold By'); ?></th>
		<th><?php echo __('Refund By'); ?></th>
		<th><?php echo __('Refund Amount'); ?></th>
		<th><?php echo __('Refund Remarks'); ?></th>
		<th><?php echo __('Status'); ?></th>
		<th><?php echo __('Refund Reason'); ?></th>
		<th><?php echo __('Sold On'); ?></th>
		<th><?php echo __('Last Updated'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($kiosk_products_data as $kioskProductSale):
			foreach($quantityArr as $productId => $qtty){
				if($productId == $kioskProductSale['product_id']){
					$totalQuantity = $qtty;
				}
			}
			
			if($kioskProductSale['refund_status'] == 1 || $kioskProductSale['refund_status'] == 2){
				$quantityReturned = $kioskProductSale['quantity'];
				$totalQuantity = "--";
			}else{
				$quantityReturned = "--";
			}
			
	//if($kioskProductSale['product_id'] )
	?>
	<?php
		if($kioskProductSale['refund_price']==0){
			$refundAmount = "...";
		}else{
			$refundAmount = $kioskProductSale['refund_price'];
		}
		if(empty($kioskProductSale['refund_by'])){
			$refundBy = "...";
		}else{
			$refundBy = $users[$kioskProductSale['refund_by']];
		}
		$productTitle = $productArr[$kioskProductSale['product_id']];
		$truncatedProduct =
\Cake\Utility\Text::truncate($productTitle, 35, [ 'ellipsis' => '...', 'exact' => false ] );
		
		if($kioskProductSale['sale_price']-($kioskProductSale['sale_price']*$kioskProductSale['discount']/100)<$kioskProductSale['sale_price']){
			$product = "<a href='#-1' title='".$productTitle."'alt='".$productTitle."' style='color: red'>".$truncatedProduct."</a>";
			$discountedPrice = $kioskProductSale['sale_price']-($kioskProductSale['sale_price']*$kioskProductSale['discount']/100);
			$discount = $kioskProductSale['discount'];
		}else{
			$product = "<a href='#-1' title='".$productTitle."'alt='".$productTitle."'>".$truncatedProduct."</a>";
			$discountedPrice = "--";
			$discount = "0";
		}
			
		if($kioskProductSale['status'] == 0){
	?>
		<tr style="color: blue">
	<?php 		}else{ ?>
		<tr>
	<?php 	} ?>
			<td><?php 
	
			echo $kioskProductSale['id']; ?></td>			
			<td><?php echo $product; ?></td>
			<td><?php echo $totalQuantity; ?></td>
			<td><?php echo $kioskProductSale['quantity']; ?></td>
			<td><?php echo $quantityReturned; ?></td>
			<td><?php echo $kioskProductSale['sale_price']; ?></td>
			<td><?php echo number_format($discount,2); ?></td>
			<td><?php echo $discountedPrice; ?></td>
			<td><?php echo $users[$kioskProductSale['sold_by']]; ?></td>
			<td><?php echo $refundBy; ?></td>
			<td><?php echo $refundAmount; ?></td>
			<td><?php echo $kioskProductSale['refund_remarks']; ?></td>
			<td><?php echo $sellingOptions[$kioskProductSale['status']]; ?></td>
			<td><?php echo $refundOptions[$kioskProductSale['refund_status']]; ?></td>
			<td><?php echo date('M jS, Y',strtotime($productReceipt['created']));//$this->Time->format('M jS, Y', $productReceipt['created'],null,null); ?></td>
			<td><?php echo date('M jS, Y',strtotime($kioskProductSale['modified']));//$this->Time->format('M jS, Y', $kioskProductSale['modified'],null,null); ?></td>
			<td class="actions">
				<?php if($kioskProductSale['status']==1 && (int)$kioskProductSale['quantity']){
					echo $this->Html->link(__('Refund Product'), array('controller' => 'kiosk_product_sales', 'action' => 'refund', 

$kioskProductSale['id'],$productReceipt['id']));
				}else{
					echo "...";
				}
				?>	
				<?php #echo $this->Html->link(__('View'), array('controller' => 'kiosk_product_sales', 'action' => 'view', $kioskProductSale['id'])); 

?>				
			</td>
		</tr>		
	<?php endforeach; ?>		
	</table>
	<sup style="color:blue">**</sup><span style="color:blue"><i>Refunded</i></span> <sup style="color:red">**</sup><span style="color:red"><i>Discounted</i></span>
<?php endif; ?>
</div>