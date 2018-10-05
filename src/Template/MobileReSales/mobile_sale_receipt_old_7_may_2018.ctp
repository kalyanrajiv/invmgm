<?php
//echo $customerEmail;die;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\Time;
?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<?php echo $this->Html->script('jquery.printElement');
$siteBaseUrl = Configure::read('SITE_BASE_URL');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<?php echo $this->Html->link(__('View Sales'), array('controller' => 'mobile_re_sales', 'action' => 'index'));?>
<?php #echo $this->Html->link("View Sales",array('controller'=>'kiosk_product_sales','action'=>'index'))?>
<div id='printDiv'>
<?php
//pr($this->request);die;
$customerEmail = $mobileResaleData['customer_email'];
//echo $customerEmail;die;
if($mobileResaleData['discounted_price']>0){
	$grandAmount = $mobileResaleData['discounted_price'];
}else{
	$grandAmount = $mobileResaleData['selling_price'];
}
$currency = $settingArr['currency_symbol'];
$vat = $settingArr['vat'];

$address1 = $address2 = $city = $state = $postalCode = "";?>
<table border="1" cellspacing="0" style="width: 700px;">
	
	<tr>
			<td colspan="7"><?php $imgUrl = "{$siteBaseUrl}/img/".$settingArr['logo_image'];
			echo $this->Html->image($imgUrl, array('fullBase' => true));?>
				<table border = '1' style="text-align: center;float: right; width: 320px;margin-top: 27px;margin-right: 8px;">
					<?php if(count($kioskDetails) == 0){?>
						<tr>
							<td><strong>Warehouse</strong></td>
						</tr>
					<?php }else{ ?>
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
					<?php } ?>
				</table>
				<table style="text-align: center;float: right; width:100%;">
				<tr>
					<td style="font-size: 22px;"><strong>Receipt</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<?php //pr($kioskDetails);die; ?>
							<?php if(!empty($kioskDetails) && $kioskDetails['vat_applied'] == 1){ ?>
							<td><strong>VAT Reg No.</strong></td>
							<?php } ?>
							<td><strong>Date.</strong></td>
							<td><strong>Invoice No.</strong></td>
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
                            <?php $created = $mobileResaleData['created'];
                                    if(!empty($created)){
									  $created->i18nFormat(
                                                                   [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                                   );
									$created_date = $created->i18nFormat('dd-MM-yyyy HH:mm:ss');
									}else{
										$created_date = '';
									}
                            ?>
							<td><?= date('d-m-Y',strtotime($created_date));?></td>
							<td>RECT<?= $mobileResaleData['id'];?></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
	</tr>
	<tr>
		<td><strong>Name:</strong></td>
		<td colspan="6"><?= $mobileResaleData['customer_fname'];?> <?= $mobileResaleData['customer_lname'];?></td>
	</tr>
	<tr>
		<td><strong>Mobile:</strong></td>
		<td colspan="6"><?=$mobileResaleData['customer_contact'];?></td>
	</tr>
	<tr>
		<td><strong>Address:</strong></td>
		<td colspan="6"><?= $mobileResaleData['customer_address_1']." ".$mobileResaleData['customer_address_2'];?></td>
	</tr>
	<tr>
		<td><strong>&nbsp;</strong></td>
		<td colspan="6"><?= $mobileResaleData['city']." ".$mobileResaleData['state']." ".$mobileResaleData['zip'];?></td>
	</tr>
	<tr>
		<td colspan="7"><strong>Purchase Details</strong></td>
	</tr>

	<tr>
		<td colspan="7">
			<table border="1" cellspacing="0">
				<tr>
					<td><strong>Imei</strong></td>
					<td><strong>Brand</strong></td>
					<td><strong>Model</strong></td>
					<td><strong>Sale Price</strong></td>
					<td><strong>Quantity</strong></td>
					<td><strong>Discount %</strong></td>
					<td><strong>Amount</strong></td>
				</tr>
				<?php
				//pr($mobileResaleData);
				$sellingPrice = $mobileResaleData['discounted_price'] /(1+$vat/100);
				
				$afterDiscountPrice = $sellingPrice-$sellingPrice*$mobileResaleData['discount']/100;
				$vatAmount = $afterDiscountPrice*$vat/100;
				?>
				<tr>
					<td><?=$mobileResaleData['imei'];?></td>
					<td><?php
					if(array_key_exists($mobileResaleData['brand_id'],$brandName)){
						echo $brandName[$mobileResaleData['brand_id']];
					}else{
						echo '';
					}
					?></td>
					<td><?php
					if(array_key_exists($mobileResaleData['mobile_model_id'],$modelName)){
						echo $modelName[$mobileResaleData['mobile_model_id']];
					}else{
						echo '';
					}
					?></td>
					<td><?= $CURRENCY_TYPE.number_format($sellingPrice,2);?></td>
					<td>1</td>
					
					<td><?=$mobileResaleData['discount'];?></td>
					<td><?= $CURRENCY_TYPE.number_format($grandAmount,2);?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="6"><strong>Sub Total</strong></td>
		<td><?= $CURRENCY_TYPE.number_format($afterDiscountPrice,2);?></td>
	</tr>
	<?php // vat $CURRENCY_TYPE.number_format($vatAmount,2);?>
	
	<?php // net amount $CURRENCY_TYPE.number_format($afterDiscountPrice,2);?>
	
	<tr>
		<td colspan="6"><strong>Total Amount</strong></td>
		<td><?=$CURRENCY_TYPE.$grandAmount;?></td>
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
		<td><?= $this->Time->format('d-m-Y',$mobileReturnData['created'],null,null);?></td>
		<td colspan="2"><?=$mobileReturnData['imei'];?></td>
		<td><?=$brandName[$mobileReturnData['brand_id']];?></td>
		<td><?=$modelName[$mobileReturnData['mobile_model_id']];?></td>
		<td>1</td>
		<td><?=$CURRENCY_TYPE.$mobileReturnData['refund_price'];?></td>
		</tr>
	<tr>
		<td colspan="6"><strong>Total Refunded Amount</strong></td>
		<td><?=$CURRENCY_TYPE.$mobileReturnData['refund_price'];?></td>
	</tr>
<?php } ?>
	</table>
	<p style="text-align: center;">
		<div style="text-align: left;">
		<?=$settingArr['headoffice_address'];?>
		</div>
	</p>
		
	<p style="text-align: center; font-size: 10px;">
		<div style="text-align: left;">
		<?php if(!empty(trim($kioskDetails['terms']))){
						echo $kioskDetails['terms'];
				}else{
						echo $settingArr['invoice_terms_conditions'];
				} ?>
		</div>
	</p>
</div>

	<table>
	</table>
	
	<?php echo $this->Form->create();?>
	<?php
	
		if(!empty($customerEmail)){
			//echo $customerEmail;
			echo $this->Form->input(null,array(
					'type' => 'text',
					'label' => 'Enter customer email',
					'name' => 'customer_email',
					'value' => $customerEmail
						)
					);
		}else{
			if(array_key_exists('customer_email',$this->request['data'])){
				$customerEmail = $this->request['data']['customer_email'];
					echo $this->Form->input(null,array(
					'type' => 'text',
					'label' => 'Enter customer email',
					'name' => 'customer_email',
					'value' => $customerEmail
						)
					);
			}else{
				echo $this->Form->input(null,array(
					'type' => 'text',
					'label' => 'Enter customer email',
					'name' => 'customer_email'
						)
					);
			}
		}
	?>
	<?php
	$option = array('name'=>'submit', 'value'=>'Send');
	echo $this->Form->submit('send_receipt',$option);
	echo $this->Form->end();?>
