<?php
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?>
<div class="invoiceOrders view">
	<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
	<div id='printDiv'>
		<?php //pr($productReceipt['KioskProductSale']);?>
		<table border="1">
			<tr><td colspan="4">
				<table>
					<tr>
						<td width='50%'>
							<?=$settingArr['company_name'];?><br/>
							<?=$settingArr['unit_number'];?><br/>
							<?=$settingArr['street'];?><br/>
							<?=$settingArr['city_county'];?><br/>
							<?=$settingArr['postal_code'];?><br/>
							Email:<?=$settingArr['email'];?><br/>
							<strong>VAT Reg No:</strong><?=$settingArr['vat_number'];?><br/>
						</td>
						<td width='50%' align='right' style='text-align:right;'>
							<?php
							$imgUrl = "/img/".$settingArr['logo_image'];
							echo $this->Html->image($imgUrl, array('fullBase' => true));
							?>
						</td>
					</tr>
				</table>
			</td></tr>
			
			
			<tr>
				<th colspan="4" style="text-align: center; font-size: 24px;">Delivery Note</th>
			</tr>
			<tr><?php //pr($productReceipt);die;  ?>
				<td colspan="2"><?=$productReceipt['fname'];?> <?=$productReceipt['lname'];?></td>
				<td style="text-align: right; "><strong>Delivery Note</strong></td>
				<td style="text-align: center; width: 163px;">DEL NO <?=$productReceipt['id'];?></td>
			</tr>
			<tr>
				<td colspan="2"><?=$productReceipt['address_1']?> <?=$productReceipt['address_2']?></td>
				<td style="text-align: right;"><strong>Date</strong></td>
				<td style="text-align: center; width: 163px;"><?=date("d-m-Y",strtotime($productReceipt['created']));//$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>
			</tr>
			<tr>
				<td colspan="2"><?=$productReceipt['city']?>, <?=$productReceipt['state']?></td>
				<td style="text-align: right;"><strong>Mobile No</strong></td>
				<td style="text-align: center; width: 163px;"><?=$productReceipt['mobile']?></td>
			</tr>
			<tr>
				<td colspan="2"><?=$productReceipt['zip'];?></td>
				<td style="text-align: right;"><strong>Customer Number</strong></td>
				<td style="text-align: center;width: 163px;"><?=$productReceipt['customer_id'];?></td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
			</tr>
			<tr>
				<th>Code</th>
				<th colspan="2">Description</th>
				<th>Quantity</th>
			</tr>
			<?php foreach($kiosk_product_data as $key => $sale){;?>
			<tr>
				<td style="width: 90px;"><?=$productCode[$sale['product_id']];?></td>
				<td colspan="2"><?=$productName[$sale['product_id']];?></td>
				<td style="width: 0px;"><?=$sale['quantity'];?></td>
			</tr>
			<?php } ?>
			<tr>
			<td style="font-size: 12px;text-align: center;" colspan="4">
				<?=$settingArr['headoffice_address'];?>
			</td>
			</tr>
			<tr>
				<td style="text-align: center;" colspan="4">
					<?=$settingArr['invoice_terms_conditions'];?>
				</td>
			</tr>
		</table>
	</div>
</div>
<?php echo $this->Form->create();?>
	<?php
		if(isset($customerEmail) && !empty($customerEmail)){
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
	?>
	<?php
	//$option = array('name'=>'send_receipt', 'value'=>'Send');
	echo $this->Form->submit('Send',array('name'=>'submit'));
	echo $this->Form->end();?>