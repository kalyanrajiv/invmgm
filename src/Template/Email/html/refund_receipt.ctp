<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
	$siteBaseUrl = Configure::read('SITE_BASE_URL');
	$vatPercentage = $productReceipt['vat'];
	$address1 = $address2 = $city = $state = $postalCode = "";
	if(!empty($customer_data)){
		if($customer_data['address_1']){
			$address1 = $customer_data['address_1'].",";
		}
		if($customer_data['address_2']){
			$address2 = $customer_data['address_2'].",";
		}
		if($customer_data['city']){
			$city = $customer_data['city'].",";
		}
		if($customer_data['state']){
			$state = $customer_data['state'].",";
		}
		if($customer_data['zip']){
			$postalCode = $customer_data['zip'];
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
		<td colspan="7">
		<?php
			$imgUrl = "/img/".$settingArr['logo_image'];
			echo $this->Html->image($imgUrl, array('fullBase' => true));
			//if((int)$productReceipt['KioskProductSale'][0]['kiosk_id']){
			//	$kioskTitle = " (".$kiosk[$productReceipt['KioskProductSale'][0]['kiosk_id']].")";
			//}
		?>
			<table border = '1' style="text-align: center;float: right; width: 320px;margin-top: 27px;margin-right: 8px;">
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
			<table style="text-align: center; width:100%;">
				<tr>
					<td style="font-size: 22px;"><strong>Receipt<?php #echo $kioskTitle;?></strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<td><strong>VAT Reg No.</strong></td>
							<td><strong>Date.</strong></td>
							<td><strong>Receipt No.</strong></td>
						</tr>
						<tr>
							<td><?=$settingArr['vat_number'];?></td>
							<td><?=date('d-m-Y',strtotime($productReceipt['created']));?></td>
							<td>RECT<?=$productReceipt['id'];?></td>
						</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td><strong>Name:</strong></td>
		<td colspan="2"><?= $productReceipt['fname'];?> <?= $productReceipt['lname'];?></td>
		<td colspan="1"><strong>Date:</strong></td>
		<td colspan="3"><?=date('d-m-Y',strtotime($productReceipt['created']));?></td>
	</tr>
	<tr>
		<td><strong>Mobile:</strong></td>
		<td colspan="2"><?= $productReceipt['mobile'];?></td>
		<td colspan="2"></td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><strong>Address:</strong></td>
		<td colspan="2"><?= $address1." ".$address2;?></td>
		<td colspan="2"></td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><strong>&nbsp;</strong></td>
		<td colspan="2"><?= $city." ".$state." ".$postalCode;?></td>
		<td colspan="2"></td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td colspan="3"><strong>Purchase Details</strong></td>
		<td colspan="4"> <?php $payment_method = implode(",",$payment_method1);
					 echo "Payment Method: <b>".$payment_method."</b>";
					 ?></td>
	</tr>

	<tr>
		<td><strong>Product Code</strong></td>
		<td style="width: 1000px;"><strong>Product</strong></td>
		<td style="text-align: right;width: 70px;"><strong>Sale Price</strong></td>
		<td style="text-align: center;width: 4px;"><strong>Qty</strong></td>
		<td colspan="2" style="text-align: right;"><strong>Discount Price</strong></td>
		<td style="text-align: right;width: 60px;"><strong>Amount</strong></td>
	</tr>
	<?php
		$amount = 0;
		$totalVat = 0;
		$totalDiscount = 0;
		foreach($sales_data as $key => $product){
			if($product['status']==1){
				$product_id = $product['product_id'];
				$receiptId = $product['product_receipt_id'];
				$quantityKey = "$product_id|$receiptId";
				$itemPrice = $product['sale_price'];  // /(1+$vatPercentage/100);
				$discount = $itemPrice*$product['discount']/100*$qttyArr[$quantityKey];
				$discountAmount = ($qttyArr[$quantityKey]*$itemPrice)-$discount;
				$amount += $discountAmount;
				$totalDiscount += $discount;
	?>
		<tr>
			<td><?php echo $product_code[$product['product_id']] ; ?></td>
			<td><?= $productName[$product['product_id']];?></td>
			<td style="text-align: right;"><?= $currency.number_format($itemPrice,2);?></td>
			<td style="text-align: center;"><?= $qttyArr[$quantityKey];?></td>
			<td colspan="2" style="text-align: right;"><?= $currency.number_format($discountAmount,2);?></td>
			
			<td style="text-align: right;"><?= $currency.number_format($discountAmount,2);?></td>
		</tr>
	<?php
			}
		}
		
		$finalAmount = $productReceipt['bill_amount'];
		$finalVat = $amount / (1+($vatPercentage / 100));
		$without_vatAmount = $amount-$finalVat;
	?>
	<tr>
		<td colspan="4" style="text-align: right;"><strong>Sub Total</strong></td>
		<td colspan="4" style="text-align: right;"><?=$currency.number_format($amount,2);?></td>
	</tr>
	
	<tr>
		<td colspan="4" style="text-align: right;"><strong>Vat</strong></td>
		<td colspan="4" style="text-align: right;"><?=$currency.number_format($finalVat,2);?></td>
	</tr>
	
	<tr>
		<td colspan="4" style="text-align: right;"><strong>Net Amount</strong></td>
		<td colspan="4" style="text-align: right;"><?=$currency.number_format($without_vatAmount,2);?></td>
	</tr>
	
	<tr>
		<td colspan="4" style="text-align: right;"><strong>Total Amount</strong></td>
		<td colspan="4" style="text-align: right;"><?=$currency.number_format($amount,2);?></td>
	</tr>
	<?php
	$dataArr = array();
	foreach($sales_data as $k=>$data){
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
			<td style="text-align: center;"><strong>Qty Returned</strong></td>
			<td style="text-align: right;"><strong>Refund/Item</strong></td>
			<td colspan="2" style="text-align: right;"><strong>Amount Refunded</strong></td>
		</tr>
	<?php
		$refundedAmount = 0;
		foreach($sales_data as $key => $product){
			if($product['status'] == 0){
				
				$refundAmount = $product['refund_price']*$product['quantity'];
				$refundedAmount += $refundAmount;
	?>
			<tr>
				<td><?= date('d-m-Y g:i A',strtotime($product['created']));?></td>
				<td colspan="2"><?= $productName[$product['product_id']];?> (<?= $refundOptions[$product['refund_status']];?>)</td>
				<td style="text-align: center;"><?= $product['quantity'];?></td>
				<td style="text-align: right;"><?= $currency.number_format($product['refund_price'],2);?></td>
				<td colspan="2" style="text-align: right;"><?= $currency.number_format($refundAmount,2);?></td>
			</tr>
	<?php
			}
			$afterRefundAmount = $amount-$refundedAmount;
		}
	?>
		<tr>
			<td colspan="5" style="text-align: right;"><strong>Total Refunded Amount</strong></td>
			<td colspan="2" style="text-align: right;"><?=$currency.number_format($refundedAmount,2);?></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align: right;"><strong>Amount After Refund</strong></td>
			<td colspan="2" style="text-align: right;"><?=$currency.number_format($afterRefundAmount,2);?></td>
		</tr>
<?php
	}
?>
	</table>
	<p style="text-align: center;">
		<?=$settingArr['headoffice_address'];?>
	</p>
		
	<p style="text-align: center; font-size: 10px;">
		<?=$settingArr['invoice_terms_conditions'];?>
	</p>