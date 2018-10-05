<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$siteBaseUrl = Configure::read('SITE_BASE_URL');
$customerEmail = $mobileResaleData['customer_email'];
if($mobileResaleData['discounted_price']>0){
	$grandAmount = $mobileResaleData['discounted_price'];
}else{
	$grandAmount = $mobileResaleData['selling_price'];
}
$currency = Configure::read('CURRENCY_TYPE');;
$vat = $settingArr['vat'];

$address1 = $address2 = $city = $state = $postalCode = "";
?>
<table border="1" cellspacing="0" style="width: 700px;">
	
	<tr>
			<td colspan="7"><?php $imgUrl = "/img/".$settingArr['logo_image'];
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
				<table style="text-align: center;float: right; width:100%;">
				<tr>
					<td style="font-size: 22px;"><strong>Receipt</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<?php if(!empty($kioskDetails) && $kioskDetails['vat_applied'] == 1){ ?>
							<td><strong>VAT Reg No.</strong></td>
							<?php } ?>
							<td><strong>Date.</strong></td>
							<td><strong>Receipt No.</strong></td>
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
							<td><?= date('d-m-Y',strtotime($mobileResaleData['created']));?></td>
							<td>RECT<?= $mobileResaleData['id'];?></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
	</tr>
	<tr>
		<td style="width: 117px;"><strong>Name:</strong></td>
		<td colspan="6"><?= $mobileResaleData['customer_fname'];?> <?= $mobileResaleData['customer_lname'];?></td>
	</tr>
	<tr>
		<td style="width: 117px;"><strong>Mobile:</strong></td>
		<td colspan="6"><?=$mobileResaleData['customer_contact'];?></td>
	</tr>
	<tr>
		<td style="width: 117px;"><strong>Address:</strong></td>
		<td colspan="6"><?= $mobileResaleData['customer_address_1']." ".$mobileResaleData['customer_address_2'];?></td>
	</tr>
	<tr>
		<td style="width: 117px;"><strong>City</strong></td>
		<td colspan="6"><?= $mobileResaleData['city']." ".$mobileResaleData['state']." ".$mobileResaleData['zip'];?></td>
	</tr>
	<tr>
		<td colspan="7" style="text-align: center"><strong>Purchase Details</strong></td>
	</tr>

	<tr>
		<td colspan="7">
			<table border="1" cellspacing="0" style="width: 100%;">
				<tr>
					<td><strong>Imei</strong></td>
					<td><strong>Brand</strong></td>
					<td><strong>Model</strong></td>
					<td style="text-align: right"><strong>Sale Price</strong></td>
					<td><strong>Quantity</strong></td>
					<td style="text-align: right"><strong>Discount Price</strong></td>
					
					<td style="text-align: right"> <strong>Amount</strong></td>
				</tr>
				<?php
				$sellingPrice = $mobileResaleData['selling_price']/(1+$vat/100);
				$afterDiscountPrice = $sellingPrice-$sellingPrice*$mobileResaleData['discount']/100;
				$vatAmount = $afterDiscountPrice*$vat/100;
				?>
				<tr>
					<td><?=$mobileResaleData['imei'];?></td>
					<td><?=$brandName[$mobileResaleData['brand_id']];?></td>
					<td><?=$modelName[$mobileResaleData['mobile_model_id']];?></td>
					<td style="text-align: right"><?= $currency.number_format($sellingPrice,2);?></td>
					<td>1</td>
					<!--sending quantity 1 by default, as its for 1 product only, always-->
					<td style="text-align: right"><?=$currency.number_format($afterDiscountPrice,2);?></td>
					
					<td style="text-align: right"><?= $currency.number_format($grandAmount,2);?></td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td colspan="6" style="text-align: right;width: 378px;"><strong>Sub Total</strong></td>
		<td colspan="3" style="text-align: right"><?= $currency.number_format($afterDiscountPrice,2);?></td>
	</tr>
	
	<!--<tr>
		<td colspan="6" style="text-align: right;width: 378px;"><strong>Vat</strong></td>
		<td colspan="3" style="text-align: right"><?=$currency.number_format($vatAmount,2);?></td>
	</tr>-->
	
	<!--<tr>
		<td colspan="6" style="text-align: right;width: 378px;"><strong>Net Amount</strong></td>
		<td colspan="3" style="text-align: right"><?=$currency.number_format($afterDiscountPrice,2);?></td>
	</tr>-->
	
	<tr>
		<td colspan="6" style="text-align: right;width: 378px;"><strong>Total Amount</strong></td>
		<td colspan="3" style="text-align: right"><?=$currency.number_format($grandAmount,2);?></td>
	</tr>
<?php if(!empty($mobileReturnData)){?>
	<tr>
		<td colspan="7"><strong>Product Return Details</strong></td>
	</tr>
	<tr>
		<td><strong>Return Date</strong></td>
		<td colspan="2"><strong>Imei</strong></td>
                <td><strong>Brand</strong></td>
		<td><strong>Model</strong></td>
		<td><strong>Quantity Returned</strong></td>
		<td><strong>Amount Refunded</strong></td>
	</tr>
		<tr>
		<td><?= date('d-m-Y',strtotime($mobileReturnData['created']));?></td>
		<td colspan="2"><?=$mobileReturnData['imei'];?></td>
		<td><?=$brandName[$mobileReturnData['brand_id']];?></td>
		<td><?=$modelName[$mobileReturnData['mobile_model_id']];?></td>
		<td>1</td><!--keeping 1 as default value, since we are only selling 1 quantity at time-->
		<td><?=$currency.$mobileReturnData['refund_price'];?></td>
		</tr>
	<tr>
	<tr>
		<td colspan="6" style="text-align: right"><strong>Total Refunded Amount</strong></td>
		<td colspan="2" style="text-align: right"><?=$currency.$mobileReturnData['refund_price'];?></td>
	</tr>
<?php } ?>
	</table>
	<p style="text-align: center;">
		<?=$settingArr['headoffice_address'];?>
	</p>
		
	<p style="text-align: center; font-size: 10px;">
		<?php if(!empty($kioskDetails['terms'])){
			echo $kioskDetails['terms'];
		}else{?>
			<?=$settingArr['invoice_terms_conditions'];?>
		<?php }?>
	</p>