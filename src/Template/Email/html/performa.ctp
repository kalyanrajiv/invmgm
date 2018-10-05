<!-- Using this template for save performa and edit performa-->
<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
?>
<table border="1" cellspacing="0">
		<tr>
			<td><?php $imgUrl = "/img/".$settingArr['logo_image'];
		echo $this->Html->image($imgUrl, array('fullBase' => true));?></td>
			<td>
				<table style="text-align: center;float: right; width: 482px;">
					<tr>
						<td style="font-size: 30px;"><strong>PERFORMA</strong></td>
					</tr>
					<tr>
						<td>
							<table border="1" width="100%" cellspacing="0">
							<tr>
								<?php if(!empty($new_kiosk_data) && $new_kiosk_data[0]->vat_applied == 1){?>
								<th>VAT Reg No.</th>
								<?php } ?>
								<th>Date.</th>
								<th>Performa No.</th>
							</tr>
							<tr>
								<?php if(!empty($new_kiosk_data) && $new_kiosk_data[0]->vat_applied == 1){
									if(!empty(trim($new_kiosk_data[0]->vat_no))){ ?>
										<td><?php echo $new_kiosk_data[0]->vat_no;?></td>
							  <?php }else{
										if(!empty($settingArr['vat_number'])){ ?>
											<td><?php echo $settingArr['vat_number'];?></td>	
									<?php }
									}
								?>
										
							<?php } ?>
								<td><?=$this->Time->format('d-m-Y',$invoiceOrder['modified'],null,null);?></td>
								<td>PER<?=$invoiceOrder['id'];?></td>
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
		<!-- customer data -->
		<tr>
		<td colspan='2'>
			<table cellspacing="0" width='100%'>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th width='50%'>Invoice To</th>								
								<th width='50%'>Ship To</th>
							</tr>
							<tr>
								<td><?=strtoupper($customerData['fname'])." ".strtoupper($customerData['lname']);?>&nbsp;</td>
								
								<td><?=strtoupper($customerData['fname'])." ".strtoupper($customerData['lname']);?>&nbsp;</td>
							</tr>
							<tr>
								<td><?=strtoupper($customerData['business']);?>&nbsp;</td>
								
								<td><?=strtoupper($customerData['business']);?>&nbsp;</td>
							</tr>
							<tr>
								<td><?=strtoupper($customerData['address_1']);?>&nbsp;</td>
								
								<td><?=strtoupper($customerData['del_address_1']);?>&nbsp;</td>
							</tr>
							<tr>
								<td><?=strtoupper($customerData['address_2']);?>&nbsp;</td>
								
								<td><?=strtoupper($customerData['del_address_2']);?>&nbsp;</td>
							</tr>
							<tr>
								<td><?=strtoupper($customerData['city'])." ".strtoupper($customerData['state']);?>&nbsp;</td>
								
								<td><?=strtoupper($customerData['del_city'])." ".strtoupper($customerData['del_state']);?>&nbsp;</td>
							</tr>
							<tr>
								<td><?=strtoupper($customerData['zip']);?>&nbsp;</td>
								<td><?=strtoupper($customerData['del_zip']);?>&nbsp;</td>
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
		<!-- customer data -->
<!-- customer vat -->
<tr>
		<td colspan='2'>
			<table border="1" cellspacing="0" width='100%'>
			<tr>
				<th>Cust Vat No</th>
				<th>Rep</th>
				<th>Pay Terms</th>
			</tr>
			<tr>
				<td><?=$customerData['vat_number'];?></td>
				<td><?=$userName;?></td>
				<td></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='2'>&nbsp;</td>
	</tr>
<!--/customer vat -->
		<!-- product rows -->
<tr>
		<td colspan='2'>
			<table border="1" cellspacing="0" width='100%'>
			<tr>
				<th>Code</th>
				<th>Description</th>
				<th>Sale Price</th>
				<th>Quantity</th>
				<th>Discount</th>				
				<th>Amount</th>
			</tr>
<?php
	$amount = 0;
	$totalVat = 0;
	$totalDiscount = 0;
	$netAmount = $invoiceOrder['amount'];
	$bulkDiscountPercentage = $invoiceOrder['bulk_discount'];
    //pr($InvoiceOrderDetail_data);die;
	foreach($InvoiceOrderDetail_data as $key => $orderDetail){
		$vatItem = $vat/100;
		$itemPrice = round($orderDetail['price']/(1+$vatItem),2);
		$discount = round($itemPrice*$orderDetail['discount']/100*$orderDetail['quantity'],2);$dis = round($itemPrice - $itemPrice*$orderDetail['discount']/100,2);
		if($orderDetail['discount'] < 0 ){
				$amt_to_show = round($itemPrice,2);
		}else{
				$amt_to_show = $discount;		
		}
		
		$discountAmount = round(($orderDetail['quantity']*$itemPrice)-$discount,2);
		
		
		//$amount+=$amt_to_show*$orderDetail['quantity'];
		list($productCode, $productTitle) = $productName[$orderDetail['product_id']];
		?>
		<tr>
			<td><?= $productCode;?></td>
			<td><?= $productTitle;?></td>
			<td><?=$CURRENCY_TYPE;?><?php
			if($orderDetail['discount'] < 0){
				echo number_format($orderDetail['price'],2);
			}else{
				echo number_format($itemPrice,2);	
			}
			?></td>
			<td><?= $orderDetail['quantity'];?></td>
            
			<td>
            <?php
				if($orderDetail['discount'] < 0){
						echo $amt = number_format($orderDetail['price'],2);
				}else{
						echo $amt = number_format($dis,2);
				}
				$amount+=$amt*$orderDetail['quantity'];
                //if($orderDetail['discount'] == 0 || empty($orderDetail['discount'])){
                //    echo $CURRENCY_TYPE.number_format($itemPrice,2);    
                //}else{
                //    $dis_amt = $itemPrice - $itemPrice*($orderDetail['discount']/100);
                //    echo $CURRENCY_TYPE.number_format($dis_amt,2);
                //}// number_format(round($orderDetail['discount'],2),2);%
            ?>
            </td>
			<td><?=$CURRENCY_TYPE;?><?php
				if($orderDetail['discount'] < 0){
						echo number_format($orderDetail['price']*$orderDetail['quantity'],2);
				}else{
						echo number_format($itemPrice*$orderDetail['quantity']-$discount,2);
				}
				
			?></td>
		</tr>
	<?php 	
			//$vatAmount = 0;
		
	}
		$amount = round($amount,2);
		$bulkDiscount = round($amount*$invoiceOrder['bulk_discount']/100,2);
		$netAmount = round($amount - $bulkDiscount,2);
		$finalAmount = $invoiceOrder['amount'];
		$finalVat = round($finalAmount-$netAmount,2);
	?>
	
			
			</table>
		</td>
	</tr>
<tr>
		<td colspan='2'>
			<table border="1" cellspacing="0" width='100%'>
				<tr>
					<td rowspan="5"><strong>Bank Details:</strong><br/><?=$settingArr['bank_details'];?></td>
					<th>Sub Total</th>
					<td><?=$CURRENCY_TYPE." ".number_format(round($amount,2),2);?></td>
				</tr>
				<tr>
					<th>Bulk Discount (<?=$invoiceOrder['bulk_discount'];?>%)</th>
					<td><?=$CURRENCY_TYPE." ".number_format(round($bulkDiscount,2),2);?></td>
				</tr>
				<tr>
					<th>VAT</th>
					<td><?php
								#echo $settingArr['currency_symbol']." ".round($finalVat,2);
								/*
								if(!empty($finalVat)){
									$vatAmt = round(($netAmount * $vat)/100,2);
									echo $settingArr['currency_symbol']." ".number_format(round($vatAmt,2),2);
								}else{
									echo $settingArr['currency_symbol']." ".number_format(round($finalVat,2),2);
								}*/
								if($customerData['country'] != 'OTH'){
							$vatAmt = round(($netAmount * $vat)/100,2);
						}
								if($customerData['country'] == 'OTH'){
										echo $CURRENCY_TYPE." ".number_format('0.00',2);
									}else{
										echo $CURRENCY_TYPE." ".number_format(round($vatAmt,2),2);
									}
					
						?></td>
				</tr>				
				<tr>
					<th>Total Amount</th>
					<td><?php
						#$settingArr['currency_symbol']." ".$finalAmount;
						if($customerData['country'] == 'OTH'){
										echo  $CURRENCY_TYPE.number_format($finalAmount,2);
									}else{
										echo  $CURRENCY_TYPE.number_format(($vatAmt+$netAmount),2);
									}
									/*
						if(!empty($finalVat)){
								echo  $settingArr['currency_symbol'].number_format(($vatAmt+$netAmount),2);
						}else{
								echo  $settingArr['currency_symbol'].number_format($finalAmount,2);
						}*/
						?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='2'>&nbsp;</td>
	</tr>		
		<!--/product rows -->
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
			<?php if(!empty($new_kiosk_data) && !empty($new_kiosk_data[0]->terms)){
			echo $new_kiosk_data[0]->terms;
		}else{
				echo $settingArr['invoice_terms_conditions'];
		}
			?>
		</td>
	</tr>
</table>