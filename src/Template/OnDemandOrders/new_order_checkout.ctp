<div class="kioskProductSales index">
	<table style="width: 30%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$session_basket = $this->request->Session()->read('on_demand_basket');
		if(!empty($session_basket)){
		echo "<td style='padding-top: 10px;'>".$this->Html->link('Create Demand Order',array('action'=>'sell_products'),array('name'=>'submit'))."</td>
							<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>'new_order'))."</td>";
		}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('type'=>'post'));
	$bulkDiscountPercentage = $this->request->Session()->read('new_sale_bulk_discount');
		$session_basket = $this->request->Session()->read('on_demand_basket');
		if(is_array($session_basket)){
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$counter++;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];				
				$sellingPrice = $basketItem['selling_price'];
				$itemPrice = round($basketItem['selling_price']/(1+$vatItem),2);
				$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
				$totalDiscountAmount+= $discountAmount;
				$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);				
				//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				$totalItemCost = round($totalItemPrice-$discountAmount,2);
				$totalBillingAmount+=$totalItemCost;
				$netPrice = round($totalBillingAmount/(1+$vatItem),2);
				$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
				$vatAmount = round($totalBillingAmount-$netPrice,2);
				//{$productIds[$key]}-rasu
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$key}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<!--td>".$currencySymbol.number_format($sellingPrice,2)."</td-->
						
						
						<td>".$this->Html->link('delete',array('action'=>'delete_product_from_session1',$key),array('id'=>$key,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
						</tr>";
			}
			//<td>".number_format($discount,2)."</td>
			//<td>".$currencySymbol.number_format($discountAmount,2)."</td>
			//			<td>".$currencySymbol.number_format($totalItemCost,2)."</td>
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th>Sr No</th>
							<th>Product Code</th>
							<th style='width:250px;'>Product</th>
							<th>Quanity</th>
							<!--th>Price/Item</th-->
							
							<th>Delete </th>
							</tr>".$basketStr."
							</table>";
							
							//<th>Discount %</th>
							//<th>Discount Value</th>
							//<th>Gross</th>
							//<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($totalBillingAmount,2)."</td></tr>
							//<tr><td colspan='7'>Vat (".$vat."%)</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
							//<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
							//<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($totalBillingAmount,2)."</td></tr>
				$productCounts = count($this->request->Session()->read('on_demand_basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					echo "Total item Count:$productCounts.<br/>$basketStr";
				}
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
		<li><?php echo $this->Html->link(__('New Sale'), array('action' => 'new_order')); ?></li>		
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