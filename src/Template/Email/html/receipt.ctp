<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
$siteBaseUrl = Configure::read('SITE_BASE_URL');?>

<table>
	<tr>
		<td>
			<b>Dear Customer</b>,
		</td>
	</tr>
	<tr><td style="height:10px;"></td></tr>
	<tr>
		<td></td>
		<td>Thank you for shopping with us. Please see below copy of your Receipt.</td>
	</tr>
	<tr><td style="height:20px;"></td></tr>
</table>
<table border="1" cellspacing="0">
	<tr>
		<td><?php $imgUrl = "/img/".$settingArr['logo_image'];
		echo $this->Html->image($imgUrl, array('fullBase' => true));?> 
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
					<td style="font-size: 30px;"><strong>Receipt</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<?php if(!empty($kioskDetails) && $kioskDetails['vat_applied'] == 1){
								?>
								<th>VAT Reg No.</th>
							<?php } ?>
							<th>Date.</th>
							<th>Receipt No.</th>
                                                        <th>Sold By</th>
						</tr>
						<tr>
							<?php if(!empty($kioskDetails) && $kioskDetails['vat_applied'] == 1){
									if(!empty(trim($kioskDetails['vat_no']))){ ?>
										<td><?php echo $kioskDetails['vat_no'];?></td>
							  <?php }else{
										if(!empty($settingArr['vat_number'])){ ?>
											<td><?php echo $settingArr['vat_number'];?></td>	
									<?php }
									}
								?>
										
							<?php } ?>
							<td><?=date('d-m-Y',strtotime($productReceipt['created']));//$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>
							<td>RECT<?=$productReceipt['id'];?></td>
                                                        <td><?=$user_name;?></td>
						</tr>
						<?php if(!empty($kioskTable)){?>
						<tr>
							<td colspan="3">
								<?=$kioskTable;?>
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
		<td colspan='2'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='2'>
                                <table border="1" style="width:100%" cellspacing="0">
                                                <?php
                                                if(!empty($productReceipt['fname']) ||
                                                    !empty($productReceipt['lname']
                                                          )){
                                                 echo "<tr><td><strong>Name:</strong></td><td>".strtoupper($productReceipt['fname'])." ".strtoupper($productReceipt['lname'])."</td></tr>";
                                                }
                                                 
                                                if(!empty($productReceipt['address_1'])){
                                                 echo "<tr><td><strong>Address1:</strong></td><td>".strtoupper($productReceipt['address_1'])."</td></tr>";
                                                }
                                                
                                                if(!empty($productReceipt['address_2'])){
                                                 echo "<tr><td><strong>Address2:</strong></td><td>".strtoupper($productReceipt['address_2'])."</td></tr>";
                                                }
                                                
                                                if(!empty($productReceipt['city']) ||
                                                    !empty($productReceipt['state']
                                                          )){
                                                 echo "<tr><td><strong>City:</strong></td><td>".strtoupper($productReceipt['city'])." ".strtoupper($productReceipt['state'])."</td></tr>";
                                                }
                                                 
                                                if(!empty($productReceipt['zip'])){
                                                 echo "<tr><td><strong>Zip:</strong></td><td>".strtoupper($productReceipt['zip'])."</td></tr>";
                                                }?>
                                </table>
		</td>
	</tr>
	<tr>
		<td colspan="4"><b>Payment Method : </b>
		 <?php $payment_method = implode(",",$payment_method1);
					 echo $payment_method;
					 ?></td>
	</tr>
	<tr>
		<td colspan='2'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='2'>
			<table border="1" cellspacing="0" style="width: 100%;">
			<tr>
				<th style="width: 64px;">Code</th>
				<th style="width: 400px;">Description</th>
				<th style="text-align: right;width: 67px;">Sale Price</th>
				<th style="text-align: center;">Qty</th>
				<th style="text-align: right;width: 60px;">Discount Price </th>
				<!--<th>Vat %</th>-->
				<th style="text-align: right;width: 72px;">Amount</th>
			</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$totalDiscount = 0;
	$vatPercentage = $productReceipt['vat'];
	foreach($res as $key => $product){
		$discount_price_to_show = 0;
		if($product['status']==1 && $product['quantity'] !=0){
		$vatItem = $vat/100;
		$itemPrice = round($product['sale_price'],2);
		//$itemPrice = round($product['sale_price']/(1+$vatItem),2);
		$discount = round($itemPrice*$product['discount']/100*$product['quantity'],2);
		$discount_price_to_show = $product['sale_price']-($product['sale_price']*$product['discount']/100);
		$discountAmount = round(($product['quantity']*$itemPrice)-$discount,2);
		$amount+=$discountAmount;
		$totalDiscount+=$discount;
		
		?>
		<tr>
			<td><?= $productCode[$product['product_id']];?></td>
			<td><?= $productName[$product['product_id']];?></td>
			<td style="text-align: right;"><?=$currency;?><?= number_format($itemPrice,2);?></td>
			<td style="text-align: center;"><?= $product['quantity'];?></td>
			<td style="text-align: right;"><?=$currency;?><?= number_format($discount_price_to_show,2);?></td>
			<!--<td><?php #$vatPercentage;?></td>-->
			<td style="text-align: right;"><?=$currency;?><?= number_format($discountAmount,2);?></td>
		</tr>
	<?php 	}/*elseif($product['status']==1 && $product['quantity'] ==0){
			$vatAmount = 0;
		}*/
	}
		$amount = round($amount,2);
		$finalAmount = $productReceipt['bill_amount'];
		$finalVat = round($finalAmount-$amount,2);
		
		$amount1 = $amount/(1+($vatPercentage/100));
		$final_vat1 = $amount - $amount1;
	?>
			
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="width: 100%;">
				<tr>
					<th style="text-align: right;">Sub Total</th>
					<td style="text-align: right;"><?=$currency." ".number_format($amount,2);?></td>
				</tr>
				<tr>
					<th style="text-align: right;">VAT</th>
					<td style="text-align: right;"><?=$currency." ".number_format($final_vat1,2);?></td>
				</tr>
				<tr>
					<th style="text-align: right;">Net Amount</th>
					<?php $net_amount = $amount- $final_vat1;?>
					<td style="text-align: right;"><?=$currency." ".number_format($net_amount,2);?></td>
				</tr>
				<tr>
					<th style="text-align: right;">Total Amount</th>
					<td style="text-align: right;"><?=$currency." ".number_format($finalAmount,2);?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='2'>
			<table border="1" cellspacing="0" style="width: 100%;">
				<tr>
					
					<td colspan='4'>Website <?=$settingArr['website'];?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="font-size: 12px;text-align: center;" colspan='2'>
			<?=$settingArr['headoffice_address'];?>
		</td>
	</tr>
	<tr>
		<td style="text-align: center;" colspan='2'>
			<?php if(!empty(trim($kioskDetails['terms']))){
								echo $kioskDetails['terms'];
							}else{
									echo $settingArr['invoice_terms_conditions'];
							} ?>	
		</td>
	</tr>
</table>