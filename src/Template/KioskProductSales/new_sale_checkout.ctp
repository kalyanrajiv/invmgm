<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="kioskProductSales index">
	<table style="width: 30%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$session_basket = $this->request->Session()->read('new_sale_basket');
		if(!empty($session_basket)){
		//echo "<td style='padding-top: 10px;'>".$this->Html->link('Sell',array('action'=>'products_selling',$customerId),array('name'=>'submit'))."</td>
		//					<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>'new_sale',$customerId))."</td>";
		}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('type'=>'post'));
	$bulkDiscountPercentage = $this->request->Session()->read('new_sale_bulk_discount');
		$session_basket = $this->request->Session()->read('new_sale_basket');
		if(is_array($session_basket)){
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			$sub_total = 0;
			
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$counter++;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];
				$price_without_vat = $basketItem['price_without_vat'];
				$sellingPrice = $basketItem['selling_price'];
				$netAmount = $basketItem['net_amount'];
			//	if($netAmount > $sellingPrice){
				//	$sellingPrice = $netAmount;
				//}
				$itemPrice = round($sellingPrice/(1+$vatItem),2);   //$basketItem['selling_price']
				$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
				$totalDiscountAmount+= $discountAmount;
				$totalItemPrice = round($sellingPrice * $basketItem['quantity'],2);				//$basketItem['selling_price']
				//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				$totalItemCost = round($totalItemPrice-$discountAmount,2);
				$totalBillingAmount+=$totalItemCost;
				$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
				$bulkDiscountValue = round((float)$totalBillingAmount*(float)$bulkDiscountPercentage/100,2);
				$netBillingAmount = round($totalBillingAmount-$bulkDiscountValue,2);
				$netPrice = round($netBillingAmount/(1+$vatItem),2);
				$vatAmount = round($netPrice * $vatItem,2);
				//round($netBillingAmount-$netPrice,2);
				
				if($country=="OTH"){
					$finalAmount = $netPrice;
				}else{
					$finalAmount = $netBillingAmount;
				}
				
				$new_totalItemCost = $netAmount * $basketItem['quantity'];
				$sub_total = $sub_total + $new_totalItemCost;
				
				$bulk_amt = ((float)$sub_total*(float)$bulkDiscountPercentage/100);
				
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>". $productIds[$key]."</td>
						<td>".$basketItem['product']."</td>
						 <td>"."<input type = text name =  CheckOut[$productIds[$key]] value = $basketItem[quantity]> "."</td>
						
					 	<td>".$CURRENCY_TYPE.$price_without_vat ."</td>
						<td>".$CURRENCY_TYPE.round($netAmount,2)."</td>
						<td>".$CURRENCY_TYPE.round($new_totalItemCost,2) ."</td>
						<td>".$this->Html->link('delete',array('action'=>'delete_product_from_session',$key,$customerId),array('id'=>$key,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
						</tr>";
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th>Sr No</th>
							<th>Product Code</th>
							<th style='width:250px;'>Product</th>
							<th>Quanity</th>
							<th>Sale Price</th>
							<th>Discount Price</th>
							<th>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='6'>Sub Total</td><td>".$CURRENCY_TYPE.round($sub_total,2)."</td></tr>
							<tr><td colspan='6'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.round($bulk_amt,2)."</td></tr>
							<tr><td colspan='6'>Sub Total(After Blk Discount)</td><td>".$CURRENCY_TYPE.round($netPrice,2)."</td></tr>
							<tr><td colspan='6'>Vat</td><td>".$CURRENCY_TYPE.round($vatAmount,2)."</td></tr>
							
							<tr><td colspan='6'>Total Amount</td><td>".$CURRENCY_TYPE.round($finalAmount,2)."</td></tr>
							<tr><td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							 
							
							<td style='width: 89px;'><span class='actions'>".$this->Html->link('Edit basket',array('action'=>'new_sale',$customerId))."</span></td>
							<td>".
							"<input type='submit' name='update_quantity' value='Update Quantity' style='margin-top: -6px;'/>"."</td></tr></tr>
							</table>";
							
							
				$productCounts = count($this->request->Session()->read('new_sale_basket'));
			//<td><span class='actions'>".$this->Html->link('Sell',array('action'=>'products_selling',$customerId),array('name'=>'submit'))."</span></td>
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
