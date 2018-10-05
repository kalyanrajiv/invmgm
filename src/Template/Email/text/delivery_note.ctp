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
			<table style="text-align: center;float: right; width:482px;">
				<tr>
					<td style="font-size: 15px;"><strong><?php if(isset($invoice)){ echo "Invoice"; }else{ echo  "Recipt"; } ?><?php if((int)$sale_table[0]['kiosk_id']){
								echo " (".$settingArr['kiosk_recipt_heading'].")";
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
							<?php if(!empty($kioskDetails)){?>
							<tr>
								<td colspan="4">
									<table>
										<tr>
											<td>
												<strong style="color: chocolate;"><?=$settingArr['kiosk_recipt_heading'];?></strong>
											</td>
										</tr>
										<tr>
											<td>
												<?=$kioskDetails['address_1'];?>,<?=($kioskDetails['address_2'] != '') ? "<br/>".$kioskDetails['address_2'] : "";?>,<?=$kioskDetails['city'];?>, <?=$kioskDetails['state'];?>,<?=$kioskDetails['zip'];?>, UK. <?=($kioskDetails['contact'] != '') ? ", Contact:".$kioskDetails['contact'] : "";?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<?php } ?>
						</table>	
					</td>
				</tr>
			</table>
			</td>
	</tr>
	<tr>
		<td colspan='8'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='8'>
			<table cellspacing="0" width='100%'>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th>Invoice To</th>
								<th>Ship To</th>
							</tr>
							<tr>
								<td> <?php
										if(isset($customer_table[0]['business'])&&!empty($customer_table[0]['business'])){
															 echo $customer_table[0]['business'];
										}else{?>
										<?= $productReceipt['fname'];?> <?= $productReceipt['lname'];?>
										<?php } ?>
								</td>
								<td>
									 <?php
										if(isset($customer_table[0]['business'])&&!empty($customer_table[0]['business'])){
															 echo $customer_table[0]['business'];
										}else{?>
										<?= $productReceipt['fname'];?> <?= $productReceipt['lname'];?>
										<?php }
									?>
								</td>
							</tr>
							<tr>
								<td><?=strtoupper($customer_table[0]['business']);?></td>
								<td><?=strtoupper($customer_table[0]['business']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($address1);?></td>
								<td><?=strtoupper($address1);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($address2);?></td>
								<td><?=strtoupper($address2);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($city)." ".strtoupper($state);?></td>
								<td><?=strtoupper($city)." ".strtoupper($state);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($postalCode);?></td>
								<td><?=strtoupper($postalCode);?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='8'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='8'>
			<table border="1" cellspacing="0" style="width: 100%;">
			<tr>
				<th>Cust Number</th>
				<th>Cust Vat No</th>
				
				
			</tr>
			<tr>
				<td><?php echo $customer_data[0]['id'];?></td>
				<td><?=$customer_table[0]['vat_number'];?></td>
				
				
			</tr>
			</table>
		</td>
	</tr>
	<tr style="width: 100%;">
		<td style="width: 10%;"><strong>Product Code</strong></td>
		<td colspan='2' style="width: 45%;"><strong>Product</strong></td>
		<td style="width: 10%;"><strong>Quantity</strong></td>
	</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$counter = $totalDiscount = 0;
    
	foreach($sale_table as $key => $product){
		
		if($product['status']==1){
		$product_id = $product['product_id'];
		$receiptId = $product['product_receipt_id'];
		$quantityKey = "$product_id|$receiptId";
		$itemPrice = $product['sale_price'];    
		?>
		<tr style="width: 100%;">
			<td style="width: 10%;"><?= $productCode[$product['product_id']];?></td>
			<td colspan='2' style="width: 45%;"><?= $productName[$product['product_id']];?></td>
			<td style="width: 10%;"><?= $qttyArr[$quantityKey];?></td>
		</tr>
	<?php 	}
	}
		
	?>
	
	
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