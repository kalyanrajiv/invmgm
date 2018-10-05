<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$customerEmail = $mobileResaleData['MobileReSale']['customer_email'];
if($mobileResaleData['MobileReSale']['discounted_price']>0){
	$grandAmount = $mobileResaleData['MobileReSale']['discounted_price'];
}else{
	$grandAmount = $mobileResaleData['MobileReSale']['selling_price'];
}
$currency = Configure::read('CURRENCY_TYPE');
$vat = $settingArr['vat'];

$address1 = $address2 = $city = $state = $postalCode = "";
?>
<table border="1" cellspacing="0" style="width: 700px;">
	
	<tr>
			<td colspan="7"><?php $imgUrl = "/img/".$settingArr['logo_image'];
			echo $this->Html->image($imgUrl, array('fullBase' => true));?>
				<table style="text-align: center;float: right; width:450px;">
				<tr>
					<td style="font-size: 22px;"><strong>Receipt<?php echo " (".$kiosk[$mobileResaleData['MobileReSale']['kiosk_id']].")";?></strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<td><strong>VAT Reg No.</strong></td>
							<td><strong>Date.</strong></td>
							<td><strong>Invoice No.</strong></td>
						</tr>
						<tr>
							<td><?=$settingArr['vat_number'];?></td>
							<td><?= $this->Time->format('d-m-Y',$mobileResaleData['MobileReSale']['created'],null,null);?></td>
							<td>RECT<?= $mobileResaleData['MobileReSale']['id'];?></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
	</tr>
	<tr>
		<td><strong>Name:</strong></td>
		<td colspan="6"><?= $mobileResaleData['MobileReSale']['customer_fname'];?> <?= $mobileResaleData['MobileReSale']['customer_lname'];?></td>
	</tr>
	<tr>
		<td><strong>Mobile:</strong></td>
		<td colspan="6"><?=$mobileResaleData['MobileReSale']['customer_contact'];?></td>
	</tr>
	<tr>
		<td><strong>Address:</strong></td>
		<td colspan="6"><?= $mobileResaleData['MobileReSale']['customer_address_1']." ".$mobileResaleData['MobileReSale']['customer_address_2'];?></td>
	</tr>
	<tr>
		<td><strong>&nbsp;</strong></td>
		<td colspan="6"><?= $mobileResaleData['MobileReSale']['city']." ".$mobileResaleData['MobileReSale']['state']." ".$mobileResaleData['MobileReSale']['zip'];?></td>
	</tr>
	<tr>
		<td colspan="7"><strong>Purchase Details</strong></td>
	</tr>

	<tr>
		<td><strong>Brand</strong></td>
		<td><strong>Model</strong></td>
		<td><strong>Sale Price</strong></td>
		<td><strong>Quantity</strong></td>
		<td><strong>Discount %</strong></td>
		<td><strong>Vat %</strong></td>
		<td><strong>Amount</strong></td>
	</tr>
	<?php
	$sellingPrice = $mobileResaleData['MobileReSale']['selling_price']/(1+$vat/100);
	$afterDiscountPrice = $sellingPrice-$sellingPrice*$mobileResaleData['MobileReSale']['discount']/100;
	$vatAmount = $afterDiscountPrice*$vat/100;
	?>
	<tr>
		<td><?=$brandName[$mobileResaleData['MobileReSale']['brand_id']];?></td>
		<td><?=$modelName[$mobileResaleData['MobileReSale']['mobile_model_id']];?></td>
		<td><?= $currency.number_format($sellingPrice,2);?></td>
		<td>1</td>
		<!--sending quantity 1 by default, as its for 1 product only, always-->
		<td><?=$mobileResaleData['MobileReSale']['discount'];?></td>
		<td><?=$vat;?></td>
		<td><?= $currency.number_format($afterDiscountPrice,2);?></td>
	</tr>
	
	<tr>
		<td colspan="6"><strong>Sub Total</strong></td>
		<td><?= $currency.number_format($afterDiscountPrice,2);?></td>
	</tr>
	
	<tr>
		<td colspan="6"><strong>Vat</strong></td>
		<td><?=$currency.number_format($vatAmount,2);?></td>
	</tr>
	
	<tr>
		<td colspan="6"><strong>Net Amount</strong></td>
		<td><?=$currency.number_format($afterDiscountPrice,2);?></td>
	</tr>
	
	<tr>
		<td colspan="6"><strong>Total Amount</strong></td>
		<td><?=$currency.$grandAmount;?></td>
	</tr>
<?php if(!empty($mobileReturnData)){?>
	<tr>
		<td colspan="7"><strong>Product Return Details</strong></td>
	</tr>
	<tr>
		<td><strong>Return Date</strong></td>
                <td colspan="2"><strong>Brand</strong></td>
		<td colspan="2"><strong>Model</strong></td>
		<td><strong>Quantity Returned</strong></td>
		<td colspan="2"><strong>Amount Refunded</strong></td>
	</tr>
		<tr>
		<td><?= $this->Time->format('d-m-Y',$mobileResaleData['MobileReSale']['created'],null,null);?></td>
		<td colspan="2"><?=$mobileReturnData['MobileReSale']['brand_id'];?></td>
		<td colspan="2"><?=$mobileReturnData['MobileReSale']['mobile_model_id'];?></td>
		<td>1</td><!--keeping 1 as default value, since we are only selling 1 quantity at time-->
		<td colspan="2"><?=$currency.$mobileReturnData['MobileReSale']['refund_price'];?></td>
		</tr>
	<tr>
		<td colspan="6"><strong>Total Refunded Amount</strong></td>
		<td colspan="2"><?=$currency.$mobileReturnData['MobileReSale']['refund_price'];?></td>
	</tr>
<?php } ?>
	</table>
	<p style="text-align: center;">
		<?=$settingArr['headoffice_address'];?>
	</p>
		
	<p style="text-align: center; font-size: 10px;">
		<?=$settingArr['invoice_terms_conditions'];?>
	</p>