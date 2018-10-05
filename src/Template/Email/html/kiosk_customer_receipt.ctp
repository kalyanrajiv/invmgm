<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
?>
<?php $siteBaseUrl = Configure::read('SITE_BASE_URL');?>
<?php $vatPercentage = $productReceipt['vat'];
$address1 = $address2 = $city = $state = $postalCode = "";
if(!empty($customer_data) && $customer_data[0]['id']>0){
	if($customer_data[0]['address_1']){
		$address1 = $customer_data[0]['address_1'].",";
	}
	if($customer_data[0]['address_2']){
		$address2 = $customer_data[0]['address_2'].",";
	}
	if($customer_data[0]['city']){
		$city = $customer_data[0]['city'].",";
	}
	if($customer_data[0]['state']){
		$state = $customer_data[0]['state'].",";
	}
	if($customer_data[0]['zip']){
		$postalCode = $customer_data[0]['zip'];
	}
	
}else{
	if($productReceipt['address_1']){
		$address1 = $productReceipt['address_1'].",";
	}
	if($productReceipt['address_2']){
		$address2 = $productReceipt['address_2'].",";
	}
	if($productReceipt['city']){
		$city = $productReceipt['city'].",";
	}
	if($productReceipt['state']){
		$state = $productReceipt['state'].",";
	}
	if($productReceipt['zip']){
		$postalCode = $productReceipt['zip'];
	}
}
?>
<table border="1" cellspacing="0" style="width: 700px;">
	
	<tr>
			<td colspan="8"><?php $imgUrl = "/img/".$settingArr['logo_image'];
			echo $this->Html->image($imgUrl, array('fullBase' => true));?> 
			<!--<table style="text-align: center;float: right; width: 320px;margin-top: 27px;margin-right: 8px;">
				<tr>
					<td>
							<?=$settingArr['headoffice_address'];?>
					</td>
				</tr>
			</table>-->
			<?php if(!empty($kioskDetails)){?>
				<table border = '1' style="float:right;margin-top: 27px;margin-right: 8px;width: 320px;text-align: center;">
					<tr>
						<td><strong><?=$kioskDetails['name'];?></strong></td>
					</tr>
					<tr>
						<td><?=$kioskDetails['address_1'];?>
						<?=($kioskDetails['address_2'] != '') ? "<br/>".$kioskDetails['address_2'] : "";?></td>
					</tr>
					<tr>
						<td><?=$kioskDetails['city'];?>, <?=$kioskDetails['state'];?></td>
					</tr>
					<tr>
						<td><?=$kioskDetails['zip'];?>, UK. <?=($kioskDetails['contact'] != '') ? ", Contact:".$kioskDetails['contact'] : "";?></td>
					</tr>
				</table>
				<?php } ?>
				<table style="text-align: center; width:100%;">
				<tr>
					<td style="font-size: 22px;"><strong>Receipt<?php if((int)$kiosk_products_data[0]['kiosk_id']){
			echo " (".$kiosk[$kiosk_products_data[0]['kiosk_id']].")";
			}
            
            ?></strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<td><strong>VAT Reg No.</strong></td>
							<td><strong>Date.</strong></td>
							<td><strong>Invoice No.</strong></td>
							<td><strong>Sold by</strong></td>
						</tr>
						<tr>
							<td><?=$settingArr['vat_number'];?></td>
							<td><?=date('d-m-Y',strtotime($productReceipt['created']));//$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>
							<td>RECT<?=$productReceipt['id'];?></td>
							<td><?php echo $users[$kiosk_products_data[0]['sold_by']];?></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
	</tr>
	<tr>
		<td><strong>Name:</strong></td>
		<td colspan="4">
					 <?php
					 if(isset($customer_data['business'])&&!empty($customer_data['business'])){
										  echo $customer_data['business'];
					 }else{?>
					 <?= $productReceipt['fname'];?> <?= $productReceipt['lname'];?>
					 <?php } ?>
					
		</td>
		<td colspan='1'><strong>Date:</strong></td>
		<td colspan='3'><?=date('d-m-Y',strtotime($productReceipt['created']));//$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>
	</tr>
	<tr>
		<td><strong>Mobile:</strong></td>
		<td colspan="4"><?= $productReceipt['mobile'];?></td>
		<td></td>
		<td colspan='2'></td>
	</tr>
	<tr>
		<td><strong>Business:</strong></td>
		<td colspan="4"><?php if(!empty($customer_data)){ echo $customer_data[0]['business']; } ?></td>
		<td></td>
		<td colspan='2'></td>
	</tr>
	<tr>
		<td><strong>Address:</strong></td>
		<td colspan="4"><?= $address1." ".$address2;?></td>
		<td></td>
		<td colspan='2'></td>
	</tr>
	<tr>
		<td><strong>&nbsp;</strong></td>
		<td colspan="4"><?= $city." ".$state." ".$postalCode;?></td>
		<td></td>
		<td colspan='2'></td>
	</tr>
	<tr>
		<td colspan="4"><strong>Purchase Details</strong></td>
		<td colspan="4">
					 <?php $payment_method = implode(",",$payment_method1);
					 echo "Payment Method : <b>".$payment_method."</b>";
					 ?>
		</td>
	</tr>

	<tr>
		<td><strong>Product Code</strong></td>
		<td colspan="3" style="width: 1811px;"><strong>Product</strong></td>
		<td style="text-align: right;width: 5px;"><strong>Sale Price</strong></td>
		<td style="text-align: center;width: 4px;"><strong>Qty</strong></td>
		<td style="text-align: right;"><strong>Discount Price</strong></td>
		
		<td style="text-align: right;"><strong>Amount</strong></td>
	</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$totalDiscount = 0;
	foreach($kiosk_products_data as $key => $product){
		if($product['status']==1){
		$product_id = $product['product_id'];
		$receiptId = $product['product_receipt_id'];
		$quantityKey = "$product_id|$receiptId";
		$itemPrice = $product['sale_price'];//(1+$vatPercentage/100);
		$discount = $itemPrice*$product['discount']/100*$qttyArr[$quantityKey];
		$dis_to_show = $itemPrice - ($itemPrice*$product['discount']/100);
		$discountAmount = ($qttyArr[$quantityKey]*$itemPrice)-$discount;
		$amount+=$discountAmount;
		//$vatItem = $vat/100;
		//$vatAmount = $discountAmount-($discountAmount/(1+$vatItem));
		//$totalVat+=$vatAmount;
		$totalDiscount+=$discount;
		?>
		<tr>
			<td><?= $productCode[$product['product_id']];?></td>
			<td colspan="3" style="width: 1811px;"><?= $productName[$product['product_id']];?></td>
			<td style="text-align: right;"><?= $CURRENCY_TYPE.number_format($itemPrice,2);?></td>
			<td style="text-align: center;"><?= $qttyArr[$quantityKey];?></td>
			
			<td style="text-align: right;"><?= $CURRENCY_TYPE.number_format($dis_to_show,2);?></td>
			
			<td style="text-align: right;"><?= $CURRENCY_TYPE.number_format($discountAmount,2);?></td>
		</tr>
	<?php 	}/*elseif($product['status']==1 && $product['quantity'] ==0){
			$vatAmount = 0;
		}*/
	}
		$bulkDiscount = $amount*$productReceipt['bulk_discount']/100;
		$netAmount = $amount - $bulkDiscount;
		$finalAmount = $productReceipt['bill_amount'];
		$finalVat = $netAmount*$vatPercentage/100;
		//$finalVat = $finalAmount - $amount;
		$totalAmount = $netAmount+$finalVat;
		
		$amount1 = $amount/(1+($vatPercentage/100));
		$final_vat1 = $amount - $amount1;
		
	?>
	
	<tr> <?php //removed as requested by the client ?>
		<td colspan="5" style="text-align: right;"><strong>Sub Total</strong></td>
		<td colspan="3" style="text-align: right;"><?=$CURRENCY_TYPE.number_format($amount,2);//$amount?></td>
	</tr>
	
	<tr>
		<td colspan="5" style="text-align: right;"><strong>Bulk Discount (<?php echo $productReceipt['bulk_discount'];?>%)</strong></td>
		<td colspan="3" style="text-align: right;"><?=$CURRENCY_TYPE.number_format($bulkDiscount,2);?></td>
	</tr>
	
	<tr>
		<td colspan="5" style="text-align: right;"><strong>Vat</strong></td>
		<td colspan="3" style="text-align: right;"><?=$CURRENCY_TYPE.number_format($final_vat1,2);?></td>
	</tr>
	<tr>
		<?php $net_amount = $amount-$final_vat1;?>
		<td colspan="5" style="text-align: right;"><strong>Net Amount</strong></td>
		<td colspan="3" style="text-align: right;"><?=$CURRENCY_TYPE.number_format($net_amount,2);?></td>
	</tr>
	
	<tr>
		<td colspan="5" style="text-align: right;"><strong>Total Amount</strong></td>
		<td colspan="3" style="text-align: right;"><?=$CURRENCY_TYPE.number_format($amount,2);?></td>
	</tr>
	<?php
	$dataArr=array();
	foreach($kiosk_products_data as $k=>$data){
		$dataArr[$data['status']]=$data['status'];
	}
	$refundedAmount = 0;
	if(array_key_exists(0,$dataArr)){//0 is for refund in status?>
		<tr>
			<td colspan="8"><strong>Product Return Details</strong></td>
		</tr>
		<tr>
			<td><strong>Return Date</strong></td>
			<td colspan="3" style="width: 3000px;"><strong>Product</strong></td>
			<td style="text-align: center;"><strong>Qty Returned</strong></td>
			<td><strong>Refund/Item</strong></td>
			<td colspan="2"><strong>Amount Refunded</strong></td>
		</tr>
		<?php 	$refundedAmount = 0;
		foreach($kiosk_products_data as $key => $product){
			if($product['status']==0){
				
				$refundAmount = $product['refund_price']*$product['quantity'];
				$refundedAmount+=$refundAmount;?>
			
				<tr>
				<td><?= date('d-m-Y g:i A',strtotime($product['created']));//$this->Time->format('d-m-Y g:i A',$product['created'],null,null);?></td>
				<td colspan="3" style="width: 3000px;"><?= $productName[$product['product_id']];?> (<?= $refundOptions[$product['refund_status']];?>)</td>
				<td style="text-align: center;"><?= $product['quantity'];?></td>
				<td style="text-align: right;"><?= $CURRENCY_TYPE.number_format($product['refund_price'],2);?></td>
				<td style="text-align: right;" colspan="2"><?= $CURRENCY_TYPE.number_format($refundAmount,2);?></td>
				</tr>
		<?php 	}
			$afterRefundAmount = $amount-$refundedAmount;
		}?>
		<tr>
			<td colspan="5" style="text-align: right;"><strong>Total Refunded Amount</strong></td>
			<td colspan="4" style="text-align: right;"><?=$CURRENCY_TYPE.number_format($refundedAmount,2);?></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align: right;"><strong>Amount After Refund</strong></td>
			<td colspan="4" style="text-align: right;"><?=$CURRENCY_TYPE.number_format($afterRefundAmount,2);?></td>
		</tr>
	<?php }?>
	<tr>
			<td colspan="2">Tel(Sales) </br><?php
					if(!empty($kioskContact)){
						echo $kioskContact;
					}else{
						echo $settingArr['tele_sales'];
					}
					?></td>
			<td colspan="2">Fax(Sales) </br><?=$settingArr['fax_number'];?></td>
					<td colspan="2">Email </br><?=$settingArr['email'];?></td>
					<td colspan="2">Website </br><?=$settingArr['website'];?></td>
		</tr>
		<tr>
		<td style="font-size: 12px;text-align: center;" colspan='8' >
			<?=$settingArr['headoffice_address'];?>
		</td>
	</tr>
	<tr>
		<td style="text-align: center;" colspan='8'>
			<?=$settingArr['invoice_terms_conditions'];?>	
		</td>
	</tr>
	</table>