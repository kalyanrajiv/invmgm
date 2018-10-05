<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');?>
<?php $vatPercentage = $productReceipt['vat'];
$address1 = $address2 = $city = $state = $postalCode = "";
//pr($customer_table);die;
if($customer_table[0]['id']>0){
	if($customer_table[0]['address_1']){
		$address1 = $customer_table[0]['address_1'].",";
	}
	if($customer_table[0]['address_2']){
		$address2 = $customer_table[0]['address_2'].",";
	}
	if($customer_table[0]['city']){
		$city = $customer_table[0]['city'].",";
	}
	if($customer_table[0]['state']){
		$state = $customer_table[0]['state'].",";
	}
	if($customer_table[0]['zip']){
		$postalCode = $customer_table[0]['zip'];
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
//pr($sale_table);die;
?>
<table>
	<tr>
		<td>
			<b>Dear Customer</b>,
		</td>
	</tr>
	<tr><td style="height:10px;"></td></tr>
	<tr>
		<td></td>
		<td>Thank you for shopping with us. Please see below copy of your invoice.</td>
	</tr>
	<tr><td style="height:20px;"></td></tr>
</table>
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
				<table border = '1'>
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
					<td style="font-size: 22px;"><strong>Receipt<?php if((int)$sale_table[0]['kiosk_id']){
			echo " (".$kiosk[$sale_table[0]['kiosk_id']].")";
			}?></strong></td>
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
							<td><?=date('d-m-Y g:i A',strtotime($productReceipt['created']));//$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>
							<td>RECT<?=$productReceipt['id'];?></td>
							<td><?php echo $users[$sale_table[0]['sold_by']];?></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
	</tr>
	<tr>
		<td><strong>Name:</strong></td>
		<td colspan="3">
					 <?php
					 if(isset($customer_table[0]['business'])&&!empty($customer_table[0]['business'])){
										  echo $customer_table[0]['business'];
					 }else{?>
					 <?= $productReceipt['fname'];?> <?= $productReceipt['lname'];?>
					 <?php } ?>
					
		</td>
		<td><strong>Date:</strong></td>
		<td colspan='3'><?=date('d-m-Y',strtotime($productReceipt['created']));//$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>
	</tr>
	<tr>
		<td><strong>Mobile:</strong></td>
		<td colspan="3"><?= $productReceipt['mobile'];?></td>
		<td></td>
		<td colspan='3'></td>
	</tr>
	<tr>
		<td><strong>Business:</strong></td>
		<td colspan="3"><?= $customer_table[0]['business'];?></td>
		<td></td>
		<td colspan='3'></td>
	</tr>
	<tr>
		<td><strong>Address:</strong></td>
		<td colspan="3"><?= $address1." ".$address2;?></td>
		<td></td>
		<td colspan='3'></td>
	</tr>
	<tr>
		<td><strong>&nbsp;</strong></td>
		<td colspan="3"><?= $city." ".$state." ".$postalCode;?></td>
		<td></td>
		<td colspan='3'></td>
	</tr>
	<tr>
		<td colspan="3"><strong>Purchase Details</strong></td>
		<td colspan="5">
					 <?php $payment_method = implode(",",$payment_method1);
					 echo "payment method : ".$payment_method;
					 ?>
		</td>
	</tr>

	<tr style="width: 100%;">
		<td colspan='2' style="width: 45%;"><strong>Product</strong></td>
		<td style="width: 10%;"><strong>Product Code</strong></td>
		<td style="width: 10%;"><strong>Sale Price</strong></td>
		<td style="width: 10%;"><strong>Quantity</strong></td>
		<td style="width: 10%;"><strong>Discount Price</strong></td>
		<td style="width: 5%;"><strong>Vat %</strong></td>
		<td style="width: 10%;"><strong>Amount</strong></td>
	</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$counter = $totalDiscount = 0;
    //pr($sale_table);die;
	foreach($sale_table as $key => $product){
		//$counter ++;
		//if($counter > 2){
		//	continue;
		//}
		if($product['status']==1){
		$product_id = $product['product_id'];
		$receiptId = $product['product_receipt_id'];
		$quantityKey = "$product_id|$receiptId";
		$itemPrice = $product['sale_price'];    // /(1+$vatPercentage/100);
		$discount = $itemPrice*$product['discount']/100*$qttyArr[$quantityKey];
		if($product['discount']<0){
			$discount_for_negitive = $itemPrice*$product['discount']/100;
			$discountAmount_for_negtive = ($itemPrice)-$discount_for_negitive;
			$discountAmount = $qttyArr[$quantityKey]*$discountAmount_for_negtive;	 
			//$discountAmount = -1*($itemPrice*$discount)/100;
		}else{
			$discountAmount = ($qttyArr[$quantityKey]*$itemPrice)-$discount;	 
		}
		
		//$discountAmount = ($qttyArr[$quantityKey]*$itemPrice)-$discount;
		$amount+=$discountAmount;
		//$vatItem = $vat/100;
		//$vatAmount = $discountAmount-($discountAmount/(1+$vatItem));
		//$totalVat+=$vatAmount;
		$totalDiscount+=$discount;
		?>
		<tr style="width: 100%;">
			<td colspan='2' style="width: 45%;"><?= $productName[$product['product_id']];?></td>
			<td style="width: 10%;"><?= $productCode[$product['product_id']];?></td>
			<?php
				if($product['discount'] < 0){ ?>
					<td style="width: 10%;"><?= $CURRENCY_TYPE.number_format($discountAmount_for_negtive,2);?></td>
			<?php }else{ ?>
					<td style="width: 10%;"><?= $CURRENCY_TYPE.number_format($itemPrice,2);?></td>
			<?php } ?>
			
			<td style="width: 10%;"><?= $qttyArr[$quantityKey];?></td>
			<?php
			if($product['discount'] < 0){ ?>
				<td style="width: 10%;"><?php echo 0;?></td>
			<?php }else{
                $dis_amout = $itemPrice - $itemPrice*($product['discount']/100); ?>
				<td style="width: 10%;"><?= number_format($dis_amout,2);?></td>
			<?php }
			?>
			<td style="width: 5%;"><?= $vatPercentage;?></td>
			<td style="width: 10%;"><?= $CURRENCY_TYPE.number_format($discountAmount,2);?></td>
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
	?>
	
	<tr> <?php //removed as requested by the client ?>
		<td colspan="7"><strong>Sub Total</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($amount,2);//$amount?></td>
	</tr>
	
	<tr>
		<td colspan="7"><strong>Bulk Discount (<?php echo $productReceipt['bulk_discount'];?>%)</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($bulkDiscount,2);?></td>
	</tr>
	
	<tr>
		<td colspan="7"><strong>after bulk discount</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($netAmount,2);?></td>
	</tr>
	
	<tr>
		<td colspan="7"><strong>Vat</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($finalVat,2);?></td>
	</tr>
	
	
	<tr>
		<td colspan="7"><strong>Total Amount</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($totalAmount,2);?></td>
	</tr>
	<?php
	$dataArr=array();
	foreach($sale_table as $k=>$data){
		$dataArr[$data['status']]=$data['status'];
	}
	$refundedAmount = 0;
	if(array_key_exists(0,$dataArr)){//0 is for refund in status?>
		<tr>
			<td colspan="7"><strong>Product Return Details</strong></td>
		</tr>
		<tr>
			<td><strong>Return Date</strong></td>
			<td colspan="2"><strong>Product</strong></td>
			<td><strong>Quantity Returned</strong></td>
			<td><strong>Refund/Item</strong></td>
			<td colspan="2"><strong>Amount Refunded</strong></td>
		</tr>
		<?php 	$refundedAmount = 0;
		foreach($sale_table as $key => $product){
			if($product['status']==0){
				
				$refundAmount = $product['refund_price']*$product['quantity'];
				$refundedAmount+=$refundAmount;?>
			
				<tr>
				<td><?= date('d-m-Y g:i A',strtotime($product['created']));//$this->Time->format('d-m-Y g:i A',$product['created'],null,null);?></td>
				<td colspan="2"><?= $productName[$product['product_id']];?> (<?= $refundOptions[$product['refund_status']];?>)</td>
				<td><?= $product['quantity'];?></td>
				<td><?= $CURRENCY_TYPE.number_format($product['refund_price'],2);?></td>
				<td colspan="2"><?= $CURRENCY_TYPE.number_format($refundAmount,2);?></td>
				</tr>
		<?php 	}
			$afterRefundAmount = $totalAmount-$refundedAmount;
		}?>
		<tr>
			<td colspan="5"><strong>Total Refunded Amount</strong></td>
			<td colspan="2"><?=$CURRENCY_TYPE.number_format($refundedAmount,2);?></td>
		</tr>
		<tr>
			<td colspan="5"><strong>Amount After Refund</strong></td>
			<td colspan="2"><?=$CURRENCY_TYPE.number_format($afterRefundAmount,2);?></td>
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
	<!--<p style="text-align: center; font-size: 10px;">
		
	</p>-->