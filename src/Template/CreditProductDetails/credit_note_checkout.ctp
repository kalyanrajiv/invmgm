<?php
use Cake\Core\Configure;
$currencySymbol = Configure::read('CURRENCY_TYPE');
?>
<div class="kioskProductSales index">
	<table style="width: 30%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$session_basket = $this->request->Session()->read('Basket');
		if(!empty($session_basket)){
		//echo "<td style='padding-top: 10px;'>".$this->Html->link('Generate credit',array('action'=>'generate_credit_note',$customerId),array('name'=>'submit'))."</td>
		//					<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>'credit_note',$customerId))."</td>
		//					<td style='padding-top: 10px;'>".$this->Html->link('Update quantity',array('action'=>'update_quantity_in_session',$customerId))."</td>";
		}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('url'=>array('controller'=>'credit_product_details','action'=>'update_quantity_in_session',$customerId)),array('type'=>'post'));
	$bulkDiscountPercentage = $this->request->Session()->read('bulk_discount');
		$session_basket = $this->request->Session()->read('Basket');
		//pr($session_basket);
		if(is_array($session_basket)){
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$sub_total = $vatAmount = 0;
			//pr($session_basket);
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$counter++;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];				
				$sellingPrice = $basketItem['selling_price'];
				$price_without_vat = $basketItem['price_without_vat'];
				$netAmount = $basketItem['net_amount'];
				
				$itemPrice = round($basketItem['selling_price']/(1+$vatItem),2);
				//$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
				$discountAmount = $price_without_vat*$basketItem['discount']/100* $basketItem['quantity'];
				$totalDiscountAmount+= $discountAmount;
				//$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);
				$totalItemPrice = $price_without_vat * $basketItem['quantity']; //newly updated
				//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				$totalItemCost = round($totalItemPrice-$discountAmount,2);
				$totalBillingAmount+=$totalItemCost;
				$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
				$bulkDiscountValue = round($totalBillingAmount*$bulkDiscountPercentage/100,2);
				$netBillingAmount = round($totalBillingAmount-$bulkDiscountValue,2);
				$vatAmount = round(($netBillingAmount * $vatItem),2);
				$netPrice = $netBillingAmount + $vatAmount;
			//	$netPrice = round($netBillingAmount/(1+$vatItem),2);
				//$vatAmount = round($netPrice * $vatItem,2);
				//round($netBillingAmount-$netPrice,2);
				
				if($country=="OTH"){
					$finalAmount = $netBillingAmount ;
				}else{
					$finalAmount = $netPrice;
				}
				
				$new_totalItemCost = $netAmount * $basketItem['quantity'];
				$sub_total = $sub_total + $new_totalItemCost;
				$bulk_amt = ($sub_total*$bulkDiscountPercentage/100);
				
				$after_blk_amt = $sub_total - $bulk_amt;
				
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$product_code[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['type']."</td>
						<td>".$this->Form->input('qtt',array('type'=>'text','name'=>"credit_product_details[$key]",'value'=>$basketItem['quantity'],'label'=>false))."</td>
						<td>".$currencySymbol.$price_without_vat."</td>
						
						<td>".$currencySymbol.round($netAmount,2)."</td>
						<td>".$currencySymbol.round($new_totalItemCost,2)."</td>
						<td>".$this->Html->link('delete',array('action'=>'delete_item_from_session',$key,$customerId),array('id'=>$key,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
						</tr>";
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th>Sr No</th>
							<th>Product code</th>
							<th style='width:250px;'>Product</th>
							<th>Type</th>
							<th>Quanity</th>
							<th>Sale Price</th>
							
							<th>Discount Value</th>
							<th>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.round($sub_total,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$currencySymbol.round($bulk_amt,2)."</td></tr>
							<tr><td colspan='7'>Sub Total(After Blk Discount)</td><td>".$currencySymbol.round($after_blk_amt,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$currencySymbol.round($vatAmount,2)."</td></tr>
	
							<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.round($finalAmount,2)."</td></tr>
							<tr><td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td class='actions'>".$this->Html->link('Generate credit',array('action'=>'generate_credit_note',$customerId),array('style'=> 'padding: 5px;position: relative;top: 4px;'))."</td>
							<td class='actions'>".$this->Html->link('Edit basket',array('action'=>'credit_note',$customerId),array('style'=>'padding: 5px;position: relative;top: 4px;'))."</td>
							<td><input type='submit' value='Update Quantity'></td></tr>
							</table>";
							

				$productCounts = count($this->request->Session()->read('Basket'));
			
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