<?php
//echo'hi';die;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\Time;
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');
$siteBaseUrl = Configure::read('SITE_BASE_URL');
//pr($user_name);?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<div id='printDiv'>
	<table border="1" cellspacing="0" style="width: 700px;">
	<tr>
        <?php
        //pr($settingArr);die;
        ?>
		<td><?php
		if(empty(trim($NewkioskDetails['terms']))){
			$imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
			echo $this->Html->image($imgUrl, array('fullBase' => true));
		}else{
			if(!empty(trim($NewkioskDetails['logo_image']))){
				$imgUrl = $siteBaseUrl."/logo/".$NewkioskDetails['id']."/".$NewkioskDetails['logo_image'];
				echo $this->Html->image($imgUrl, array('fullBase' => true,'style'=> "width: 332px;height: 102px;"));	
			}else{
				$imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
				echo $this->Html->image($imgUrl, array('fullBase' => true));
			}
			
		}
		?>
		
		<table style="text-align: center;float: right; width: 320px;margin-top: 27px;margin-right: 8px;">
			<?php if(empty(trim($NewkioskDetails['terms']))){ ?>
					 <tr>
							<td> <?=$settingArr['headoffice_address'];?> </td> 
					</tr>
					 <?php }?>
			</table>
		
			<table border = '1' style="text-align: center;float: right; width: 320px;margin-top: -68px;margin-right: 8px;">
					<?php
					if(empty($NewkioskDetails)){?>
						
					<?php }else{ ?>
						<tr>
							<td><strong><?=$NewkioskDetails['name'];?></strong></td>
						</tr>
						<tr>
							<td><?=$NewkioskDetails['address_1'];?>
							<?=($NewkioskDetails['address_2'] != '') ? "<br/>".$NewkioskDetails['address_2'] : "";?></td>
						</tr>
						<tr>
							<td><?=$NewkioskDetails['city'];?>, <?=$NewkioskDetails['state'];?></td>
						</tr>
						<tr>
							<td><?=$NewkioskDetails['zip'];?>, UK. <?=($NewkioskDetails['contact'] != '') ? ", Contact:".$NewkioskDetails['contact'] : "";?></td>
						</tr>
					<?php } ?>
				</table>
		</td>
	</tr>
	<tr>
		<td style="font-size: 19px;"><strong>CREDIT NOTE</strong></td>
	</tr>
	<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<?php //pr($NewkioskDetails);die;  ?>
							<?php if(!empty($NewkioskDetails) && $NewkioskDetails['vat_applied'] == 1){ ?>
							<th><strong>VAT Reg No.</strong></th>
							<?php } ?>
							<th>Date.</th>
							<th>Credit Note No.</th>
							<th>Sold by</th>
						</tr>
						<tr>
							<?php if(!empty($NewkioskDetails) && $NewkioskDetails['vat_applied'] == 1){
									if(!empty(trim($NewkioskDetails['vat_no']))){ ?>
										<td><?php echo $NewkioskDetails['vat_no'];?></td>
							  <?php }else{
										if(!empty($settingArr['vat_number'])){ ?>
											<td><?php echo $settingArr['vat_number'];?></td>	
									<?php }
									}
								?>
										
							<?php } ?>
                            <?php $created_1 = $creditReceiptDetail['created'];
//                                  $created->i18nFormat(
//                                                                            [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
//                                                                    );
//								 $created_1 =  $created->i18nFormat('dd-MM-yyyy HH:mm:ss');
								 ?>
							<td><?=date('d-m-Y h:m:i',strtotime($created_1));//$this->Time->format('d-m-Y',$creditReceiptDetail['created'],null,null);?></td>
							<td>CRN<?=$creditReceiptDetail['id'];?></td>
							<td><?php if(!empty($user_name)){
											if (strpos($user_name, QUOT_USER_PREFIX) !== false) {
												 echo str_replace(QUOT_USER_PREFIX,"",$user_name);			
											}else{
												   echo $user_name;					
											}
										}else{echo "--";}?></td>
						</tr>
						
						</table>
					</td>
				</tr>
	<tr>
		<td>
			<table cellspacing="0">
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th>Invoice To</th>
								<th>Ship To</th>
							</tr>
							<tr><?php //pr($customer);die;
										$str = strtoupper($customer['fname'])." ".strtoupper($customer['lname']);
										if(strlen($str) > 40){
							?>
								<td><?=strtoupper($customer['fname']);?></td>
								<td><?=strtoupper($customer['fname']);?></td>
								<?php }else{ ?>
								<td><?=strtoupper($customer['fname'])." ".strtoupper($customer['lname']);?></td>
								<td><?=strtoupper($customer['fname'])." ".strtoupper($customer['lname']);?></td>
								<?php } ?>
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
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
			<tr>
				<th>Cust Number:</th>
				<th colspan=2>Cust Vat No</th>
				
				<th>Pay Terms</th>
			</tr>
			<tr>
				<td><?php echo $customer['id']?></td>
				<td colspan=2><?=$customer['vat_number'];?></td>
				
				<td><?=implode(", ",$payment_method);?></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;">
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
	$total_qty = $totalDiscount = 0;
    //pr($creditReceiptDetail);die;
	//pr($creditProductDetailsData);die;
	foreach($creditProductDetailsData as $key => $product){
        //pr($product);die;
		$vatItem = $vat/100;
		$itemPrice = round($product['sale_price']/(1+$vatItem),2);
		$discount = round($itemPrice*$product['discount']/100*$product['quantity'],2);
		$discountAmount = round(($product['quantity']*$itemPrice)-$discount,2);
		$amount+=$discountAmount;
		$totalDiscount+=$discount;
		$total_qty += $product['quantity'];
		?>
		<tr>
			<td><?= $productCode[$product['product_id']];?></td>
			<?php
				if($product['discount'] > 0){
					$show_amount = $discountAmount/$product['quantity'];
					$itemPrice = $show_amount;
				}
				if($product['discount'] < 0){
					$itemPrice = $itemPrice;
				}
			?>
			<td><?= $productName[$product['product_id']];?></td>
			<td><?= $CURRENCY_TYPE.$itemPrice;?></td>
			<td><?= $product['quantity'];?></td>
            
            <?php if($product['discount'] < 0){?>
					<td><?php echo $discountAmount;?></td>
			<?php }else{
					$show_amount = $discountAmount/$product['quantity'];?>
					<td><?= number_format($show_amount,2);?></td>
			<?php } ?>
            
			
			<td><?= $CURRENCY_TYPE.$discountAmount;?></td>
		</tr>
	<?php 	
	}
	//pr($creditReceiptDetail);die;
		$amount = round($amount,2);
		$bulkDiscount = round($amount*$creditReceiptDetail['bulk_discount']/100,2);
		$netAmount = round($amount - $bulkDiscount,2);
		$finalAmount = $creditReceiptDetail['credit_amount'];
		$finalVat = round($finalAmount-$netAmount,2);
	?>
			</table>
		</td>
	</tr>
	
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;">
				<tr>
					<?php
					if(empty(trim($NewkioskDetails['terms']))){
						echo "<td colspan='7' rowspan='5'><strong>Bank Details:</strong><br/>";
						//echo $NewkioskDetails['terms'];
					echo $settingArr['bank_details'];
					echo "</td>";
					}
					?>
					<th colspan=2>Sub Total</br>(total qty = <?php echo $total_qty;?>)</th><td><?=$amount;?></td>
					
				</tr>
				<tr>
					<th>Bulk Discount (<?=$CURRENCY_TYPE.$creditReceiptDetail['bulk_discount'];?>%)</th>
					<td colspan="2"><?=$CURRENCY_TYPE.round($bulkDiscount,2);?></td>
				</tr>
				<tr>
					<th>Sub Total</th>
					<td colspan="2"><?=$CURRENCY_TYPE.round($netAmount,2);?></td>
				</tr>
				<tr>
					<th>VAT</th>
					<td colspan="2"><?=$CURRENCY_TYPE.round($finalVat,2);?></td>
				</tr>				
				<tr>
					<th>Total Amount</th>
					<td colspan="2"><?=$CURRENCY_TYPE.$finalAmount;?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					<td>Tel(Sales) <?php
					if(!empty($kioskContact)){
						echo $kioskContact;
					}else{
						if(!empty(trim($NewkioskDetails['terms']))){
							echo $NewkioskDetails['contact'];
						}else{
							echo $settingArr['tele_sales'];	
						}
					}
					?></td>
					<td>Fax(Sales) <?=$settingArr['fax_number'];?></td>
					<?php if(!empty(trim($NewkioskDetails['terms']))){ ?>
						<td>Email <?=$NewkioskDetails['email'];?></td>
					<?php }else{?>
						<td>Email <?=$settingArr['email'];?></td>
					<?php } ?>
					<?php if(empty(trim($NewkioskDetails['terms']))){ ?>
						<td>Website <?=$settingArr['website'];?></td>
					<?php }?>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	
		
	<p style="text-align: center; font-size: 10px;">
		<div style="text-align: left;">
		<?php if(!empty(trim($NewkioskDetails['terms']))){
						echo $NewkioskDetails['terms'];
				}else{
						echo $settingArr['invoice_terms_conditions'];
				} ?>
		</div>
	</p>
</div>
	<table>
	<?php
		if(isset($paymentDetails)){
			$rowStr = "";
			foreach($paymentDetails as $key => $sngPmtDet){
                //pr($sngPmtDet);die;
				$pmtMethod = $sngPmtDet['payment_method'];
				$pmtDesc = $sngPmtDet['description'];
				$amt = $sngPmtDet['amount'];
				$srNo = $key + 1;
				$rowStr.="<tr><td>$srNo</td><td>$pmtMethod</td><td>$pmtDesc</td><td>$amt</td></tr>";
			}
			if(!empty($rowStr)){
				echo $pmtStr = <<<PMT_STR
				<br/><h3>Payment Details:</h3>
				<table>
				<tr><th>Sr No.</th><th>Payment Method</th><th>Description</th><th>Amount</th></tr>
				$rowStr
				</table>
PMT_STR;
			}
		}
	?>
	</table>
	<?php echo $this->Form->create();?>
	<?php
		if(!empty($customerEmail)){
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
					'name' => 'customer_email',
                    'value' => '',
						)
					);
		}
	?>
	<?php
	//$option = array('name'=>'send_receipt', 'value'=>'Submit');
	echo $this->Form->submit('Submit',array('name'=>'submit'));
    echo $this->Form->end();?>