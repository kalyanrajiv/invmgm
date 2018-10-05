<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<?php echo $this->Html->link("View Sales",array('controller'=>'kiosk_product_sales','action'=>'index'));?>
&nbsp;
<?php if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
					 $this->request->session()->read('Auth.User.user_type')=='wholesale'){?>
<?php echo $this->Html->link("View Invoices",array('controller'=>'product_receipts','action'=>'all_invoices'))?>
<?php }

?>

<div id='printDiv'>
    <?php //pr($productReceipt);die; ?>
<?php $vatPercentage = $productReceipt['vat'];
$address1 = $address2 = $city = $state = $postalCode = "";
//pr($customer_data);die;
if(!empty($customer_data) ){
    if($customer_data[0]['id']>0){
        if($customer_data[0]['address_1']){
            $address1 = $customer_data[0]['address_1'].",";
        }
        if($customer_data[0]['address_2']){
            $address2 = $customer_data[0]['address_2'].",";
        }
        if($customer_data[0]['city']){
            $city = $customer_data[0]['city'].",";
        }
        if($customer_data[0]['state']){
            $state = $customer_data[0]['state'].",";
        }
        if($customer_data[0]['zip']){
            $postalCode = $customer_data[0]['zip'];
        }
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
?>
<table border="1" cellspacing="0" style="width: 700px;">
	
	<tr>
			<td colspan="12"><?php
			
			$siteBaseUrl = Configure::read('SITE_BASE_URL');	
					              if(empty(trim($kioskDetails['terms'])) && empty($new_kiosk_data[0]->terms)){
										  $imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
										  echo $this->Html->image($imgUrl, array('fullBase' => true));
								 }else{
										  if(!empty(trim($kioskDetails['logo_image']))){
												$imgUrl = $siteBaseUrl."/logo/".$kioskDetails['id']."/".$kioskDetails['logo_image'];
												echo $this->Html->image($imgUrl, array('fullBase' => true,'style'=> "width: 332px;height: 102px;"));	
										  }else{
												 $imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
															   echo $this->Html->image($imgUrl, array('fullBase' => true));
										  }
								 }
			?>
			<?php  if(!empty($kioskDetails)){?>
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
				<?php }?>
				<table style="text-align: center;width:100%;">
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
							<td><strong>Invoice No.</strong></td>
							<td><strong>Sold By</strong></td>
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
							<td><?php
										  if(array_key_exists($kiosk_products_data[0]['sold_by'],$users)){
															   if (strpos($users[$kiosk_products_data[0]['sold_by']], QUOT_USER_PREFIX) !== false) {
																	echo str_replace(QUOT_USER_PREFIX,"",$users[$kiosk_products_data[0]['sold_by']]);			
															   }else{
															          echo $users[$kiosk_products_data[0]['sold_by']];					
															   }
															   
										  }?>
					     </td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
	</tr>
	<?php
	
	if(!empty($customer_data)){  ?>
	<tr>
					 <td colspan=12>
					 <table cellspacing="0">
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th>Invoice To</th>
								<th>Ship To</th>
							</tr>
							<tr><?php
							$str = "";
										if(!empty($customer_data)){  
										  $str = strtoupper($customer_data[0]['fname'])." ".strtoupper($customer_data[0]['lname']);
										}
															   if(!empty($str) && strlen($str) > 40){													
							?>
								<td><?=strtoupper($customer_data[0]['fname']);?></td>
								<td><?=strtoupper($customer_data[0]['fname']);?></td>
								<?php }else{?>
								<td>
										  <?php if(!empty($customer_data)){ ?>
								<?=strtoupper($customer_data[0]['fname'])." ".strtoupper($customer_data[0]['lname']);?>
										  <?php }?>
								</td>
								<td>
										    <?php if(!empty($customer_data)){ ?>
								<?=strtoupper($customer_data[0]['fname'])." ".strtoupper($customer_data[0]['lname']);?>
															   <?php }?>
								</td>
								<?php } ?>
							</tr>
							<tr>
								<td>
										   <?php if(!empty($customer_data)){
															   if(array_key_exists("business",$customer_data[0])){
															   ?>
								<?=strtoupper($customer_data[0]['business']);?>
															   <?php }}?>
								</td>
								<td>
										   <?php if(!empty($customer_data)){
															     if(array_key_exists("business",$customer_data[0])){
															   ?>
								<?=strtoupper($customer_data[0]['business']);?>
										  <?php }}?>
								</td>
								
							</tr>
							<tr>
								<td>
										  <?php if(!empty($customer_data)){ ?>
										  <?=strtoupper($customer_data[0]['address_1']);?>
										  <?php }?>
								</td>
								<td>
										  <?php if(!empty($customer_data)){ ?>
										  <?=strtoupper($customer_data[0]['address_1']);?>
										  <?php }?>
								</td>
							</tr>
							<tr>
								<td>
										  <?php if(!empty($customer_data)){ ?>
								<?=strtoupper($customer_data[0]['address_2']);?>
										  <?php }?>
								</td>
								<td>
										  <?php if(!empty($customer_data)){ ?>
										  <?=strtoupper($customer_data[0]['address_2']);?>
										  <?php }?>
								</td>
							</tr>
							<tr>
								<td>
										 <?php if(!empty($customer_data)){ ?> 
								<?=strtoupper($customer_data[0]['city'])." ".strtoupper($customer_data[0]['state']);?>
										  <?php }?>
								</td>
								<td>
										  <?php if(!empty($customer_data)){ ?> 
										  <?=strtoupper($customer_data[0]['city'])." ".strtoupper($customer_data[0]['state']);?>
										  <?php }?>
								</td>
							</tr>
							<tr>
								<td>
										  <?php if(!empty($customer_data)){ ?> 
										  <?=strtoupper($customer_data[0]['zip']);?>
										  <?php }?>
								</td>
								<td>
										  <?php if(!empty($customer_data)){ ?> 
								<?=strtoupper($customer_data[0]['zip']);?>
								 <?php }?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
					 </td>
	</tr>
	<?php } ?>
	

	<tr>
		
		<td colspan="7"><strong>Product</strong></td>
		<td><strong>Product Code</strong></td>
		<td><strong>Sale Price</strong></td>
		<td><strong>Quantity</strong></td>
		<td><strong>Discount </strong></td>
		<td><strong>Amount</strong></td>
	</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$totalDiscount = 0;
	//pr($productReceipt['KioskProductSale']);
    //pr($kiosk_products_data);die;
	foreach($kiosk_products_data as $key => $product){
					 $show_single_price = 0;
		if($product['status']==1){
					 //pr($product);
					 //pr($qttyArr);
		$product_id = $product['product_id'];
		$receiptId = $product['product_receipt_id'];
		$quantityKey = "$product_id|$receiptId";
		$itemPrice = $product['sale_price'];
		$discount = ($itemPrice*$product['discount']/100);
		$discountAmount = ($itemPrice)-$discount;
		$show_single_price = $discountAmount*$qttyArr[$quantityKey];
		$amount += $discountAmount*$qttyArr[$quantityKey];
		//$vatItem = $vat/100;
		//$vatAmount = $discountAmount-($discountAmount/(1+$vatItem));
		//$totalVat+=$vatAmount;
		$totalDiscount+=$discount;
		?>
		<tr>
			
			<td colspan="7"><?= $productName[$product['product_id']];?></td>
			<td><?= $productCode[$product['product_id']];?></td>
			<td><?= $CURRENCY_TYPE.number_format($itemPrice,2);?></td>
			<td><?= $qttyArr[$quantityKey];?></td>
			
			<td><?= number_format($show_single_price/$qttyArr[$quantityKey],2);?></td>
			<td><?= $CURRENCY_TYPE.number_format($show_single_price,2);?></td>
		</tr>
	<?php 	}/*elseif($product['status']==1 && $product['quantity'] ==0){
			$vatAmount = 0;
		}*/
	}
		#$bulkDiscount = $amount*$productReceipt['ProductReceipt']['bulk_discount']/100;
		#$netAmount = $amount - $bulkDiscount;
		$finalAmount = $productReceipt['bill_amount'];
		$totalAmount = $amount/(1+($vatPercentage/100));
		//$finalVat = $finalAmount - $amount;
		$finalVat = $amount-$totalAmount;
		?>
	
	<tr><?php
		$bank_details = "";
										  if(!empty($kioskDetails['terms'])){
										         $bank_details = $kioskDetails['terms'];				   				   
										  }
										  if(!empty($new_kiosk_data[0]->terms)){
										         $bank_details = $new_kiosk_data[0]->terms;				   				   
										  }
										  echo "<td colspan='9' rowspan='4'>";
										  if(empty($bank_details)){
															   echo "<strong>Bank Details:</strong><br/>";
															   echo $settingArr['bank_details'];
										  }
										  echo "</td>";			 
			?>		 
		<td colspan="2"><strong>Sub Total</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($amount,2);?></td>
	</tr>
	
	<tr>
        <?php if($kioskDetails['vat_applied'] == 1){ ?>					 
		<td colspan="2"><strong>Vat(<?=$vatPercentage;?>%)</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($finalVat,2);?></td>
		<?php } ?>
	</tr>
	
	<tr>
		<td colspan="2"><strong>Net Amount</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($totalAmount,2);?></td>
	</tr>
	
	<tr>
		<td colspan="2"><strong>Total Amount</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($amount,2);?></td>
	</tr>
	
	<?php 
	$dataArr=array();
	foreach($kiosk_products_data as $k=>$data){
		$dataArr[$data['status']]=$data['status'];
	}
	$refundedAmount = 0;
    //pr($dataArr);die;
	if(array_key_exists(0,$dataArr)){//0 is for refund in status?>
	
		<tr>
		<td colspan="12"><strong>Product Return Details</strong></td>
	</tr>
	<tr>
		<td><strong>Return Date</strong></td>
                <td colspan="6"><strong>Product</strong></td>
		<td><strong>Quantity Returned</strong></td>
		<td><strong>Refund/Item</strong></td>
		<td colspan="3"><strong>Amount Refunded</strong></td>
	</tr>
	
		<?php foreach($kiosk_products_data as $key => $product){
			if($product['status']==0){
				
				$refundAmount = $product['refund_price']*$product['quantity'];
				$refundedAmount+=$refundAmount;?>
			
				<tr>
				<td><?=date('d-m-Y',strtotime($product['created']));//$this->Time->format('d-m-Y',$product['created'],null,null);?></td>
				<td colspan="6"><?= $productName[$product['product_id']];?> (<?= $refundOptions[$product['refund_status']];?>)</td>
				<td><?= $product['quantity'];?></td>
				<td><?= $CURRENCY_TYPE.number_format($product['refund_price'],2);?></td>
				<td colspan="3"><?= $CURRENCY_TYPE.number_format($refundAmount,2);?></td>
				</tr>
		<?php 	}
		}
			$afterRefundAmount = $amount-$refundedAmount;
		?>
		<tr>
			<td colspan="9"><strong>Total Refunded Amount</strong></td>
			<td colspan="3"><?=$CURRENCY_TYPE.number_format($refundedAmount,2);?></td>
		</tr>
		<tr>    
			<td colspan="9"><strong>Amount After Refund</strong></td>
			<td colspan="3"><?=$CURRENCY_TYPE.number_format($afterRefundAmount,2);?></td>
		</tr>
	<?php }?>
	<tr>
			<td colspan="3">Tel(Sales) </br><?php
					if(!empty($kioskContact)){
						echo $kioskContact;
					}else{
						echo $settingArr['tele_sales'];
					}
					?></td>



			<td colspan="3">Fax(Sales) </br><?=$settingArr['fax_number'];?></td>
			<?php if(!empty(trim($kioskDetails['terms'])) || (!empty($new_kiosk_data) && !empty(trim($new_kiosk_data[0]->terms)))){
					 if(!empty(trim($kioskDetails['terms']))){?>
					      <td colspan="3">Email </br><?=$kioskDetails['email'];?></td> 
					 <?php }else{ ?>
					      <td colspan="3">Email </br><?=$new_kiosk_data[0]->email;?></td>
					 <?php }?>
					
			<?php }else{?>
					 <td colspan="3">Email </br><?=$settingArr['email'];?></td>
			<?php }?>
			<?php if(empty(trim($kioskDetails['terms'])) && empty(!empty($new_kiosk_data) && trim($new_kiosk_data[0]->terms))){ ?>
					<td colspan="3">Website </br><?=$settingArr['website'];?></td>
            <?php } ?>
		</tr>
	</table>
	<table style="text-align: center; width: 465px;margin-top: 27px;margin-right: 8px;">
					 <?php
					 
					 if(empty(trim($kioskDetails['terms'])) || (!empty($new_kiosk_data) && empty(trim($new_kiosk_data[0]->terms)))){ ?>
					 <tr>
							<td> <?=$settingArr['headoffice_address'];?> </td> 
					</tr>
					 <?php } ?>
			</table>	
		
	<p style="text-align: center; font-size: 10px;">
		<div style="text-align: left;">
		<?php
					 if(!empty($kioskDetails) && !empty($kioskDetails['terms'])){
					              echo $kioskDetails['terms'];
					 }else{
								  echo $settingArr['invoice_terms_conditions'];			 
					 }
				?>
		</div>
	</p>
</div>

	<table>
	<?php 
		if(isset($paymentDetails)){
			$rowStr = "";
			foreach($paymentDetails as $key => $sngPmtDet){
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
	<?php $rectID =  $productReceipt['id'];
	if(!empty($kioskDetails)){
		$kiosk_id = $kioskDetails['id'];			 
	}else{
		$kiosk_id=-1;			 
	}
	?>
	<?php echo $this->Form->create('Email',array('url' => array('action' => "generate_receipt_kiosk_sale/$rectID/$kiosk_id")));?>
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
					'name' => 'customer_email'
						)
					);
		}
	?>
	<?php
	$option = array('name'=>'submit', 'value'=>'Send');
	echo $this->Form->Submit('Send',$option);
    echo $this->Form->end();?>
