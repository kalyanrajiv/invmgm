<?php
//echo'hi';die;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\Time;
$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
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
		<td>Thank you for shopping with us. Please see below copy of your Credit Note.</td>
	</tr>
	<tr><td style="height:20px;"></td></tr>
</table>

<table border="1" cellspacing="0">
	<tr>
		<td><?php $imgUrl = "/img/".$settingArr['logo_image'];
		echo $this->Html->image($imgUrl, array('fullBase' => true));?>
			<table style="text-align: center;float: right; width:482px;">
				<tr>
					<td style="font-size: 30px;"><strong>CREDIT NOTE</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<th>VAT Reg No.</th>
							<th>Date.</th>
							<th>Credit No.</th>
						</tr>
						<tr>
							<td><?=$settingArr['vat_number'];?></td>
                            <?php
                                 $created = $productReceipt['created'];
                                 $created_1 = $created->i18nFormat(
                                                                            [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                                    );
                            ?>
							<td><?=date('d-m-Y h:m:i',strtotime($created_1));//$this->Time->format('d-m-Y',$productReceipt['CreditReceipt']['created'],null,null);?></td>
							<td>CRN<?=$productReceipt['id'];?></td>
						</tr>
						<?php  if(!empty($kioskTable)){?>
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
    <?php //pr($customer);die; ?>
	<tr>
		<td colspan='2'>
			<table cellspacing="0" width='100%'>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th>Invoice To</th>
								<th>Ship To</th>
							</tr>
							<tr>
								<td><?=strtoupper($customer['fname'])." ".strtoupper($customer['lname']);?></td>
								<td><?=strtoupper($customer['fname'])." ".strtoupper($customer['lname']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customer['business']);?></td>
								<td><?=strtoupper($customer['business']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customer['address_1']);?></td>
								<td><?=strtoupper($customer['del_address_1']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customer['address_2']);?></td>
								<td><?=strtoupper($customer['del_address_2']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customer['city'])." ".strtoupper($customer['state']);?></td>
								<td><?=strtoupper($customer['del_city'])." ".strtoupper($customer['del_state']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customer['zip']);?></td>
								<td><?=strtoupper($customer['del_zip']);?></td>
							</tr>
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
			<table border="1" cellspacing="0" style="width: 100%;">
			<tr>
				<th>Cust Vat No</th>
				<th>Rep</th>
				<th>Pay Terms</th>
			</tr>
			<tr>
				<td><?=$customer['vat_number'];?></td>
				<td><?=$user_name;?></td>
				<td><?=implode(", ",$payment_method);?></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='2'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='2'>
			<table border="1" cellspacing="0" style="width: 100%;">
			<tr>
				<th>Code</th>
				<th>Description</th>
				<th>Sale Price</th>
				<th>Quantity</th>
				<th>Discount Price</th>
				
				<th>Amount</th>
			</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$totalDiscount = 0;
	foreach($creditProductDetailsData as $key => $product){
		if($product['status']==1 && $product['quantity'] !=0){
		$vatItem = $vat/100;
		$itemPrice = round($product['sale_price']/(1+$vatItem),2);
		$discount = round($itemPrice*$product['discount']/100*$product['quantity'],2);
		$discountAmount = round(($product['quantity']*$itemPrice)-$discount,2);
		$amount+=$discountAmount;
		$totalDiscount+=$discount;
		
		?>
		<tr>
			<td><?= $productCode[$product['product_id']];?></td>
			<td><?= $productName[$product['product_id']];?></td>
			<?php
				if($product['discount'] > 0){
					$show_amount = $discountAmount/$product['quantity'];
					$itemPrice = $show_amount;
				}
				if($product['discount'] < 0){
					$itemPrice = $itemPrice;
				}
			?>
			<td><?=$CURRENCY_TYPE;?><?= number_format($itemPrice,2);?></td>
			<td><?= $product['quantity'];?></td>
			
			<?php if($product['discount'] < 0){?>
					<td><?php echo $discountAmount;?></td>
			<?php }else{
					$show_amount = $discountAmount/$product['quantity'];?>
					<td><?= number_format($show_amount,2);?></td>
			<?php } ?>
			
			<!--<td><?= number_format($product['discount'], 2);?></td>-->
			<td><?=$CURRENCY_TYPE;?><?= number_format($discountAmount, 2);?></td>
		</tr>
	<?php 	}/*elseif($product['status']==1 && $product['quantity'] ==0){
			$vatAmount = 0;
		}*/
	}
		$amount = round($amount,2);
		$bulkDiscount = round($amount*$productReceipt['bulk_discount']/100,2);
		$netAmount = round($amount - $bulkDiscount,2);
		$finalAmount = $productReceipt['credit_amount'];
		$finalVat = round($finalAmount-$netAmount,2);
	?>
			
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='2'>
			<table border="1" cellspacing="0" style="width: 100%;">
				<tr>
					<td rowspan="5"><strong>Bank Details:</strong><br/><?=$settingArr['bank_details'];?></td>
					<th>Sub Total</th>
					<td nowrap='nowrap'><?=$CURRENCY_TYPE." ".number_format(round($amount,2),2);?></td>
				</tr>
				<tr>
					<th>Bulk Discount</th>
					<td nowrap='nowrap'><?=$CURRENCY_TYPE." ".number_format(round($bulkDiscount,2),2);?></td>
				</tr>
				<tr>
					<th>VAT</th>
					<td nowrap='nowrap'><?=$CURRENCY_TYPE." ".number_format(round($finalVat,2),2);?></td>
				</tr>				
				<tr>
					<th>Total Amount</th>
					<td nowrap='nowrap'><?=$CURRENCY_TYPE." ".number_format($finalAmount, 2);?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='2'>
			<table border="1" cellspacing="0" style="width: 100%;">
				<tr>
					<td>Tel(Sales) <?php
					if(!empty($kioskContact)){
						echo $kioskContact;
					}else{
						echo $settingArr['tele_sales'];
					}
					?></td>
					<td>Fax(Sales) <?=$settingArr['fax_number'];?></td>
					<td>Email <?=$settingArr['email'];?></td>
					<td>Website <?=$settingArr['website'];?></td>
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
			<?=$settingArr['invoice_terms_conditions'];?>
		</td>
	</tr>
</table>