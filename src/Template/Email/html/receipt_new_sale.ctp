<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
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
		<td>Thank you for shopping with us. Please see below copy of your invoice.</td>
	</tr>
	<tr><td style="height:20px;"></td></tr>
</table>
<!--Using same template for new sale (new invoice by admin), create sale from performa and edit invoice-->
<table border="1" cellspacing="0">
	<tr>
		<td><?php $imgUrl = "/img/".$settingArr['logo_image'];
		echo $this->Html->image($imgUrl, array('fullBase' => true));
		//pr($productReceipt);
		?>
			<table style="text-align: center;float: right; width:482px;">
				<tr>
					<td style="font-size: 15px;"><strong>Invoice <?php echo " (".$settingArr['kiosk_recipt_heading'].")"; ?></strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<?php if(isset($new_kiosk_data)){
								if(!empty($new_kiosk_data) && $new_kiosk_data[0]->vat_applied == 1 ){
								?>
								<th>VAT Reg No.</th>
							<?php }
								}else{ ?>
								<th>VAT Reg No.</th>
							<?php }?>
							<th>Date.</th>
							<th>Invoice No.</th>
							<th>Sold by</th>
						</tr>
						<tr>
							<?php
							if(isset($new_kiosk_data)){
								if(!empty($new_kiosk_data) && $new_kiosk_data[0]->vat_applied == 1){
										if(!empty(trim($new_kiosk_data[0]->vat_no))){ ?>
											<td><?php echo $new_kiosk_data[0]->vat_no;?></td>
								  <?php }else{
											if(!empty($settingArr['vat_number'])){ ?>
												<td><?php echo $settingArr['vat_number'];?></td>	
										<?php }
										}
									?>
											
								<?php }	
							}else{
							?>
							<td><?=$settingArr['vat_number'];?></td>
							<?php }?>
							<td><?=date('d-m-Y g:i A',strtotime($productReceipt['created']));//$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>
							<!--<td><?=$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>-->
							<td>INV<?=$productReceipt['id'];?></td>
							<td><?=$user_name;?></td>
						</tr>
						<?php if(!empty($kioskTable)){?>
						<tr>
							<td colspan="4">
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
			<table cellspacing="0" width='100%'>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th>Invoice To</th>
								<th>Ship To</th>
							</tr>
							<tr>
								<td><?=strtoupper($cust_data['fname'])." ".strtoupper($cust_data['lname']);?></td>
								<td><?=strtoupper($cust_data['fname'])." ".strtoupper($cust_data['lname']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($cust_data['business']);?></td>
								<td><?=strtoupper($cust_data['business']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($cust_data['address_1']);?></td>
								<td><?=strtoupper($cust_data['del_address_1']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($cust_data['address_2']);?></td>
								<td><?=strtoupper($cust_data['del_address_2']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($cust_data['city'])." ".strtoupper($cust_data['state']);?></td>
								<td><?=strtoupper($cust_data['del_city'])." ".strtoupper($cust_data['del_state']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($cust_data['zip']);?></td>
								<td><?=strtoupper($cust_data['del_zip']);?></td>
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
				<th>Cust Number</th>
				<th>Cust Vat No</th>
				<!--<th>Rep</th>-->
				<th>Pay Terms</th>
			</tr>
			<tr>
				<td><?php echo $cust_data['id']; ?></td>
				<td><?=$cust_data['vat_number'];?></td>
				<!--<td><?=$user_name;?></td>-->
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
	$vatPercentage = $productReceipt['vat'];
	foreach($sale_table as $key => $product){
		if($product['status']==1 && $product['quantity'] !=0){
		$vatItem = $vat/100;
		$itemPrice = number_format($product['sale_price'],2); //sourabh
		//$itemPrice = round($product['sale_price']/(1+$vatItem),2);  //sourabh
		$discount = round($itemPrice*$product['discount']/100*$product['quantity'],2);
		
		if($product['discount']<0){
			$discount_for_negitive = $itemPrice*$product['discount']/100;
			$discountAmount_for_negtive = ($itemPrice)-$discount_for_negitive;
			$discountAmount = $product['quantity']*$discountAmount_for_negtive;	 
			//$discountAmount = -1*($itemPrice*$discount)/100;
		}else{
			$discountAmount = ($product['quantity']*$itemPrice)-$discount;	 
		}
		
		
		//$discountAmount = round(($product['quantity']*$itemPrice)-$discount,2);
		$amount+=$discountAmount;
		$totalDiscount+=$discount;
		
		?>
		<tr>
			<td><?= $productCode[$product['product_id']];?></td>
			<td><?= $productName[$product['product_id']];?></td>
			
			
			<?php
				if($product['discount'] < 0){ ?>
					<td style="width: 10%;"><?= $CURRENCY_TYPE.number_format($discountAmount_for_negtive,2);?></td>
			<?php }else{ ?>
					<td style="width: 10%;"><?= $CURRENCY_TYPE.number_format($itemPrice,2);?></td>
			<?php } ?>
			
			
			<!--<td><?=$CURRENCY_TYPE;?><?= $itemPrice;?></td> -->
			<td><?= $product['quantity'];?></td>
			
			<?php
			if($product['discount'] < 0){ ?>
				<td style="width: 10%;"><?php echo 0;?></td>
			<?php }else{
                $dis_amout = $itemPrice - $itemPrice*($product['discount']/100); ?>
				<td style="width: 10%;"><?= number_format($dis_amout,2);?></td>
			<?php }
			?>
			<td><?=$CURRENCY_TYPE;?><?= number_format($discountAmount,2);?></td>
		</tr>
	<?php 	}/*elseif($product['status']==1 && $product['quantity'] ==0){
			$vatAmount = 0;
		}*/
	}
		$amount = round($amount,2);
		$bulkDiscount = round($amount*$productReceipt['bulk_discount']/100,2);
		$netAmount = round($amount - $bulkDiscount,2);
		$finalAmount = $productReceipt['bill_amount'];
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
					<td nowrap='nowrap'><?=$CURRENCY_TYPE." ".number_format($amount,2);?></td>
				</tr>
				<tr>
					<th>Bulk Discount</th>
					<td nowrap='nowrap'><?=$CURRENCY_TYPE." ".$bulkDiscount;?></td>
				</tr>
				<tr>
					<th>VAT</th>
					<td nowrap='nowrap'><?=$CURRENCY_TYPE." ".number_format($finalVat,2);?></td>
				</tr>				
				<tr>
					<th>Total Amount</th>
					<td nowrap='nowrap'><?=$CURRENCY_TYPE." ".number_format($finalAmount,2);?></td>
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
			<?php
			if(isset($new_kiosk_data)){
				if(!empty($new_kiosk_data) && !empty($new_kiosk_data[0]->terms)){
							echo $new_kiosk_data[0]->terms;
					  }else{
								echo $settingArr['invoice_terms_conditions'];
					  }
			}else{
						echo $settingArr['invoice_terms_conditions'];
			}
			?>
		</td>
	</tr>
</table>