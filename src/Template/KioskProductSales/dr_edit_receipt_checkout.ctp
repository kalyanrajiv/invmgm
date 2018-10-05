<?php
	//pr($_SESSION);
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="kioskProductSales index">
	<table style="width: 30%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$session_basket = $this->request->Session()->read('Basket');
		if(!empty($session_basket)){
		//echo "<td style='padding-top: 10px;'>".$this->Html->link('Sell',array('action'=>'products_selling',$customerId),array('name'=>'submit'))."</td>
		//					<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>'new_sale',$customerId))."</td>";
		}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('type'=>'post'));
	$bulkDiscountPercentage = $this->request->Session()->read('BulkDiscount');
		$session_basket = $this->request->Session()->read('Basket');
		
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
				$discountAmount = $price_without_vat * $basketItem['discount']/100 * $basketItem['quantity'];
				$totalDiscountAmount+= $discountAmount;
				//$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);
				$totalItemPrice = $price_without_vat * $basketItem['quantity'];
				//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				$totalItemCost = round($totalItemPrice-$discountAmount,2);
				$totalBillingAmount+=$totalItemCost;
				$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
				$bulkDiscountValue = round($totalBillingAmount*$bulkDiscountPercentage/100,2);
				$netBillingAmount = round($totalBillingAmount-$bulkDiscountValue,2);
				//$netPrice = round($netBillingAmount/(1+$vatItem),2);
				$netPrice = $netBillingAmount;
				//$vatAmount = round($netPrice * $vatItem,2);
				$vatAmount = $netBillingAmount*$vatItem;
				//round($netBillingAmount-$netPrice,2);
				$finalAmount = $netBillingAmount;
				
				if($country=="OTH"){
					$finalAmount = $netPrice;
				}else{
					$finalAmount = $netBillingAmount+$vatAmount;
				}
				
				$new_totalItemCost = $netAmount * $basketItem['quantity'];
				$sub_total = $sub_total + $new_totalItemCost;
				
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>". $productIds[$key]."</td>
						<td>".$basketItem['product']."</td>
						 <td>"."<input type = text name =  data[CheckOut][$productIds[$key]] value = $basketItem[quantity]> "."</td>
						
					 	<td>".$CURRENCY_TYPE.$price_without_vat ."</td>
						
						<td>".$CURRENCY_TYPE.round($new_totalItemCost,2)."</td>
						<td>".$CURRENCY_TYPE.round($totalItemCost,2)."</td>
						<td>".$this->Html->link('delete',array('action'=>'dr_delete_product_from_session2',$key,$orderId),array('id'=>$key,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
						</tr>";
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th>Sr No</th>
							<th>Product Code</th>
							<th style='width:250px;'>Product</th>
							<th>Quanity</th>
							<th>Sale Price</th>
							<th>Discount Value</th>
							<th>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='6'>Sub Total</td><td>".$CURRENCY_TYPE.round($sub_total,2)."</td></tr>
							<tr><td colspan='6'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.round($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='6'>Sub Total(After bulk discount)</td><td>".$CURRENCY_TYPE.round($netBillingAmount,2)."</td></tr>
							<tr><td colspan='6'>Total Amount</td><td>".$CURRENCY_TYPE.round($netPrice,2)."</td></tr>
							<tr><td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							 
							<td><span class='actions'>".$this->Html->link('Sell',array('action'=>'dr_save_updated_receipt',$orderId),array('name'=>'submit'))."</span></td>
							<td style='width: 89px;'>"."<input type='submit' name='edit_basket' value='edit_basket' style='margin-top: -6px;'/>"."</td>
							<td>".
							"<input type='submit' name='update_quantity' value='Update Quantity' style='margin-top: -6px;'/>"."</td></tr></tr>
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
