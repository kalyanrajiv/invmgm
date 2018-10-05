<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>

<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php
echo $this->Html->script('jquery.printElement');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<?php echo $this->Html->link("View Sales",array('controller'=>'kiosk_product_sales','action'=>'index'));?>
&nbsp;
<?php if($this->request->Session()->read('Auth.User.group_id') == 3 && //KIOSK_USERS
					 $this->request->Session()->read('Auth.User.user_type')=='wholesale'){?>
<?php echo $this->Html->link("View Invoices",array('controller'=>'product_receipts','action'=>'all_invoices'))?>
<?php }

?>
<?php ob_start(); ?>
<div id='printDiv'>
<?php $vatPercentage = $productReceipt['vat'];?>
<?php
//pr($productReceipt['sale_type']);
$address1 = $address2 = $city = $state = $postalCode = "";
if(!empty($customer_table)){
	if($customer_table[0]['address_1']){
		$address1 = $customer_table[0]['address_1'].",";
	}
	if($customer_table[0]['address_2']){
		$address2 = $customer_table[0]['address_2'].",";
	}
	if($customer_table[0]['city']){
		$city = $customer_table[0]['city'].",";
	}
	if($customer_table[0]['state']){
		$state = $customer_table[0]['state'].",";
	}
	if($customer_table[0]['zip']){
		$postalCode = $customer_table[0]['zip'];
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
<?php
	 
	$siteBaseUrl = Configure::read('SITE_BASE_URL');
?>
<table border="1" cellspacing="0" style="width: 750px;">
	
	<tr> 
			<td colspan="8">
			<div style="height: 28px;"></div>
			<?php
			if(empty(trim($kioskDetails['terms'])) && empty($new_kiosk_data[0]->terms)){
					 $imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
					 echo $this->Html->image($imgUrl, array('fullBase' => true));
			}else{
					 if(!empty(trim($kioskDetails['logo_image']))){
					       $imgUrl = $siteBaseUrl."/logo/".$kioskDetails['id']."/".$kioskDetails['logo_image'];
					       echo $this->Html->image($imgUrl, array('fullBase' => true,'style'=> "width: 332px;height: 102px;"));	
					 }elseif(!empty($new_kiosk_data) && !empty(trim($new_kiosk_data[0]->logo_image))){
					       $imgUrl = $siteBaseUrl."/logo/".$new_kiosk_data[0]->id."/".$new_kiosk_data[0]->logo_image;
					       echo $this->Html->image($imgUrl, array('fullBase' => true,'style'=> "width: 332px;height: 102px;"));	
					 }else{
					        $imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
										  echo $this->Html->image($imgUrl, array('fullBase' => true));
				     }
			}
			 
			?>
			<div style="float: right;height: 82px;"></div>
			
			<table border = '1' style="text-align: center;float: right; width: 320px;margin-right: 8px;">
					<?php
					if(empty($new_kiosk_data)){?>
						
					<?php }else{ ?>
						<tr>
							<td><strong><?=$new_kiosk_data[0]->name;?></strong></td>
						</tr>
						<tr>
							<td><?=$new_kiosk_data[0]->address_1;?>
							<?=($new_kiosk_data[0]->address_2 != '') ? "<br/>".$new_kiosk_data[0]->address_2 : "";?></td>
						</tr>
						<tr>
							<td><?=$new_kiosk_data[0]->city;?>, <?=$new_kiosk_data[0]->state;?></td>
						</tr>
						<tr>
							<td><?=$new_kiosk_data[0]->zip;?>, UK. <?=($new_kiosk_data[0]->contact != '') ? ", Contact:".$new_kiosk_data[0]->contact : "";?></td>
						</tr>
					<?php } ?>
				</table>
			 <?php
			 
			 if(!empty($kioskDetails)){ ?>
				<table border = '1' style="text-align: center;float: right; width: 320px;margin-top: -62px;margin-right: 8px;">
					<tr>
						<td><strong><?php
					    if(!empty($kioskDetails)){
							echo $kioskDetails['name'];			  
						}
						?></strong></td>
					</tr>
					<tr>
						<td><?php
					       if(!empty($kioskDetails)){
					             echo $kioskDetails['address_1'];			  
					        }
						?>
						 <?php if(!empty($kioskDetails)){ ?>
						<?=($kioskDetails['address_2'] != '') ? "<br/>".$kioskDetails['address_2'] : "";?><?php } ?></td>
						
					</tr>
					<tr>
						<td><?php if(!empty($kioskDetails)){ ?><?=$kioskDetails['city'];?>, <?=$kioskDetails['state'];?><?php } ?></td>
					</tr>
					<tr>
						<td><?php if(!empty($kioskDetails)){ ?><?=$kioskDetails['zip'];?>, UK. <?=($kioskDetails['contact'] != '') ? ", Contact:".$kioskDetails['contact'] : "";?><?php } ?></td>
					</tr>
				</table>
				<?php } ?>
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
							<?php }elseif(!empty($new_kiosk_data) && $new_kiosk_data[0]->vat_applied == 1){?>
							<td><strong>VAT Reg No.</strong></td>
							<?php }?>
							<td><strong>Date.</strong></td>
							<td><strong>Invoice No.</strong></td>
							<td><strong>Sold by</strong></td>
						</tr>
						<tr>
							<?php
							if(!empty($kioskDetails) && $kioskDetails['vat_applied'] == 1){
										  if(!empty($kioskDetails['vat_no'])){
										           echo "<td>".$kioskDetails['vat_no']."</td>";
										  }else{
															   if(!empty($settingArr['vat_number'])){
																					echo "<td>".$settingArr['vat_number']."</td>";
															   }
										  }
							}elseif(!empty($new_kiosk_data) && $new_kiosk_data[0]->vat_applied == 1){
										  if(!empty($new_kiosk_data[0]->vat_no)){
										          echo "<td>".$new_kiosk_data[0]->vat_no."</td>";				   
										  }else{
											echo "<td>".$settingArr['vat_number']."</td>";				   
										  }
										  
							}
							?>
							<td><?= date("d-m-Y g:i A",strtotime($productReceipt['created']));
							//$this->Time->format('d-m-Y g:i A',$productReceipt['created'],null,null);?></td>
							<td>RECT<?=$productReceipt['id'];?></td>
							<td><?php
										  if(array_key_exists($sale_table[0]['sold_by'],$users)){
															   if (strpos($users[$sale_table[0]['sold_by']], QUOT_USER_PREFIX) !== false) {
																	echo str_replace(QUOT_USER_PREFIX,"",$users[$sale_table[0]['sold_by']]);			
															   }else{
															          echo $users[$sale_table[0]['sold_by']];					
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
	<?php if(!empty($customer_data)){  ?>
	<tr>
					 <td colspan=9>
					 <table cellspacing="0" style="border-right: 0;clear: both;color: #333;margin-bottom: 10px;width: 100%;">
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
										  <?php if(!empty($customer_data)){
															   ?>
										  <?=strtoupper($customer_data[0]['address_1']);?>
										  <?php }?>
								</td>
								<td>
										  <?php if(!empty($customer_data)){
															    if(array_key_exists("del_address_1",$customer_data[0])){
															   ?>
										  <?=strtoupper($customer_data[0]['del_address_1']);?>
										  <?php }else{
															   echo strtoupper($customer_data[0]['address_1']);
															   }}?>
								</td>
							</tr>
							<tr>
								<td>
										  <?php if(!empty($customer_data)){ ?>
								<?=strtoupper($customer_data[0]['address_2']);?>
										  <?php }?>
								</td>
								<td>
										  <?php if(!empty($customer_data)){
															   if(array_key_exists("del_address_1",$customer_data[0])){
															   ?>
										  <?=strtoupper($customer_data[0]['del_address_2']);?>
										  <?php }else{
															   echo strtoupper($customer_data[0]['address_2']);
															   }}?>
								</td>
							</tr>
							<tr>
								<td>
										 <?php if(!empty($customer_data)){ ?> 
								<?=strtoupper($customer_data[0]['city'])." ".strtoupper($customer_data[0]['state']);?>
										  <?php }?>
								</td>
								<td>
										  <?php if(!empty($customer_data)){
															   if(array_key_exists("del_city",$customer_data[0])){
															   ?> 
										  <?=strtoupper($customer_data[0]['del_city'])." ".strtoupper($customer_data[0]['del_state']);?>
										  <?php }else{
															   echo strtoupper($customer_data[0]['city'])." ".strtoupper($customer_data[0]['state']);
															   }}?>
								</td>
							</tr>
							<tr>
								<td>
										  <?php if(!empty($customer_data)){ ?> 
										  <?=strtoupper($customer_data[0]['zip']);?>
										  <?php }?>
								</td>
								<td>
										  <?php if(!empty($customer_data)){
															   if(array_key_exists("del_zip",$customer_data[0])){
															   ?> 
								<?=strtoupper($customer_data[0]['del_zip']);?>
								 <?php }else{
										  echo strtoupper($customer_data[0]['zip']);
										  }}?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
					 </td>
	</tr>
	<?php } ?>
	<?php if(!empty($customer_data) || !empty($payment_method)){ ?>
	<tr>
		<td colspan="8">&nbsp;</td>
	</tr>
	
	<tr>
		<td colspan="8">
			<table border="1" cellspacing="0" style="border-right: 0;clear: both;color: #333;margin-bottom: 10px;width: 100%;">
			<tr>
				<th>Cust Number:</th>
				<th colspan="2">Cust Vat No</th>
				
				<th>Pay Terms</th>
			</tr>
			<tr>
				<td><?php
				if(!empty($customer_data)){
					 echo $customer_data[0]['id'];
				}else{
					 echo "--";
				}
				?></td>
				<td colspan=2><?php
				if(!empty($customer_data)){
					  if(array_key_exists("vat_number",$customer_data[0])){
										  echo $customer_data[0]['vat_number'];
					  }else{
										  echo "--";
					  }
				}else{
					 echo "--";
				}
				?></td>
				
				<td><?=implode(", ",$payment_method);?></td>
			</tr>
			</table>
		</td>
	</tr>
	<?php } ?>

	<tr>
		<?php if( array_key_exists('bulk_invoice', $productReceipt) && $productReceipt['bulk_invoice'] != 1){ ?>
		<td style="width: 880px;" colspan="3">
		<?php }else{?>
		<td colspan="3" style="width: 880px;">
		<?php }?>
		<strong>Product</strong></td>
		
		<td><strong>Product Code</strong></td>
		<td><strong>Sale Price</strong></td>
		<td><strong>Quantity</strong></td>
		<td style="width: 24px;"><strong>Discount Price</strong></td>
		<td style="width: 19px;"><strong>Amount</strong></td>
	</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$totalDiscount = 0;
	$totalqty = 0;
    $shown_entry = array();
	//pr($productReceipt['KioskProductSale']);
    //pr($qttyArr);
	foreach($sale_table as $key => $product){
        
            if(array_key_exists($product['product_id'],$shown_entry)){
						//if($product['discount'] == $shown_entry[$product['product_id']]){
						//				  continue;
						//}
            }else{
            //$shown_entry[$product['product_id']] = $product['discount'];
            }
        
		if($product['quantity'] == 0){continue;}			 
		//pr($product);pr($qttyArr);
		if($product['status']==1){
					 //pr($product);
					 //pr($qttyArr);
		$product_id = $product['product_id'];
		$receiptId = $product['product_receipt_id'];
		$quantityKey = "$product_id|$receiptId";
		$itemPrice = $product['sale_price'];    // /(1+$vatPercentage/100);
		$discount = 0;
		$discountAmount = 0;
		if(array_key_exists($quantityKey, $qttyArr)){
			$discount = $itemPrice*$product['discount']/100*$qttyArr[$quantityKey];
			//echo "</br>";
			if($product['discount']<0){
                 $discount_for_negitive = $itemPrice*$product['discount']/100;
                 $discountAmount_for_negtive = ($itemPrice)-$discount_for_negitive;
				 $discountAmount = $product['quantity']*$discountAmount_for_negtive;	 
                 //$discountAmount = -1*($itemPrice*$discount)/100;
			}else{
				 $discountAmount = ($product['quantity']*$itemPrice)-$discount;	 
			}
           
		   //echo "</br>";
		   //echo $qttyArr[$quantityKey]*$itemPrice;
		   //echo "</br>";
		}
		$amount+=$discountAmount;
		//$vatItem = $vat/100;
		//$vatAmount = $discountAmount-($discountAmount/(1+$vatItem));
		//$totalVat+=$vatAmount;
		$totalDiscount+=$discount;
		?>
		<tr>
			<?php if( array_key_exists('bulk_invoice', $productReceipt) && $productReceipt['bulk_invoice'] != 1){ ?>
			<td colspan="3">
			<?php }else{?>
			<td colspan="3">
			<?php } ?>
			<?= $productName[$product['product_id']];?></td>
			<td><?= $productCode[$product['product_id']];?></td>
			<?php if($product['discount'] < 0){ ?>
			<td><?= $CURRENCY_TYPE.number_format($discountAmount_for_negtive,2);?></td>
			<?php //$discountAmount = $discountAmount * $qttyArr[$quantityKey];?>
			<?php }else{ ?>
			<td><?= $CURRENCY_TYPE.number_format($itemPrice,2);?></td>
			<?php } ?>
			<td><?php
			//if(array_key_exists($quantityKey,$qttyArr)){
					 echo $product['quantity'];
				//echo $qttyArr[$quantityKey];
				$totalqty+=$product['quantity'];
				//$totalqty+=$qttyArr[$quantityKey];
			//}
		 
			?></td>
			<?php if($product['discount'] < 0){ ?>
			<td><?php echo 0; ?></td>
			<?php }else{
					 if($product['discount'] == 0){
							$dis_amt = 	$itemPrice;		  
					 }else{
						$dis_amt = $itemPrice - $itemPrice*($product['discount']/100);				  
					 }
					 ?>
			<td><?= number_format($dis_amt,2);?></td>
			<?php } ?>
			
			<td><?= $CURRENCY_TYPE.number_format($discountAmount,2);?></td>
		</tr>
	<?php 	}//elseif($product['status']==1 && $product['quantity'] ==0){
			//$vatAmount = 0;
		//}
	}
		$bulkDiscount = $amount*$productReceipt['bulk_discount']/100;
		$netAmount = $amount - $bulkDiscount;
		$finalAmount = $productReceipt['bill_amount'];
		$finalVat = $netAmount*$vatPercentage/100;
		//$finalVat = $finalAmount - $amount;
		
		if($productReceipt['sale_type'] == 0){
					 if($productReceipt['bulk_invoice'] !=1 ){
										  $finalVat = $netAmount - $netAmount /(1 + ($vatPercentage/100));
					 }
		}
		
		$totalAmount = $netAmount+$finalVat;
		
		
		
		
	?>
	
	<tr>   <?php //removed as requested by the client // we uncommented it on 3 aug ?>
	<?php
										  $bank_details = "";
										  if(!empty($kioskDetails['terms'])){
										         $bank_details = $kioskDetails['terms'];				   				   
										  }
										  if(!empty($new_kiosk_data[0]->terms)){
										         $bank_details = $new_kiosk_data[0]->terms;				   				   
										  }
										  echo "<td colspan='5' rowspan='5'>";
										  if(empty($bank_details)){
															   echo "<strong>Bank Details:</strong><br/>";
															   echo $settingArr['bank_details'];
										  }
										  echo "</td>";
															   ?>
		<td colspan="2"><strong>Sub Total(total qty = <?php echo $totalqty;?>)</strong></td>
		<td><?=$CURRENCY_TYPE.number_format($amount,2);?></td>
	</tr>
	
	<tr>
		<th colspan="2"><strong>Bulk Discount(<?php echo $productReceipt['bulk_discount'];?>%) </strong></th>
		<td><?=$CURRENCY_TYPE.number_format($bulkDiscount,2);?></td>
	</tr>
	
	<tr>
		<th  colspan="2"><strong>After Bulk Discount</strong></th>
		<td><?=$CURRENCY_TYPE.number_format($netAmount,2);?></td>
	</tr>
	
	<tr>
		<th colspan="2"><strong>Vat(<?php echo $vat; ?>%)</strong></th>
		<td><?=$CURRENCY_TYPE.number_format($finalVat,2);?></td>
	</tr>
	
	
	
	<?php if($productReceipt['sale_type'] == 0){
					 if($productReceipt['bulk_invoice'] == 1){ ?>
										  <tr>
															   <th colspan="2"><strong>Total Amount</strong></th>
															   <td><?=$CURRENCY_TYPE.number_format($totalAmount,2);?></td>
													 </tr> 						  
					 <?php }else{ ?>
					 <tr>
										  <th colspan="2"><strong>Total Amount</strong></th>
										  <td><?=$CURRENCY_TYPE.number_format($netAmount,2);?></td>
								   </tr>						  
					 <?php }
					 ?>
	<?php }else{?>
               <tr>
						 <th colspan="2"><strong>Total Amount</strong></th>
						 <td><?=$CURRENCY_TYPE.number_format($totalAmount,2);?></td>
			   </tr>
	<?php }?>
	
	<tr>
					 <td colspan="9">&nbsp;</td>
	</tr>
	
	<?php
	$dataArr=array();
	foreach($sale_table as $k=>$data){
		$dataArr[$data['status']]=$data['status'];
	}
	$refundedAmount = 0;
	if(array_key_exists(0,$dataArr)){//0 is for refund in status?>
		
		<tr>
		<td colspan="7"><strong>Product Return Details</strong></td>
	</tr>
	<tr>
		<td><strong>Return Date</strong></td>
                <td colspan="2"><strong>Product</strong></td>
		<td><strong>Quantity Returned</strong></td>
		<td><strong>Refund/Item</strong></td>
		<td colspan="2"><strong>Amount Refunded</strong></td>
	</tr>
	
		<?php foreach($sale_table as $key => $product){
			if($product['status']==0){
				
				$refundAmount = $product['refund_price']*$product['quantity'];
				$refundedAmount+=$refundAmount;?>
			
				<tr>
				<td><?=date("d-m-Y g:i A",strtotime($product['created']));//$this->Time->format('d-m-Y',$product['created'],null,null);?></td>
				<td colspan="2"><?= $productName[$product['product_id']];?> (<?= $refundOptions[$product['refund_status']];?>)</td>
				<td><?= $product['quantity'];?></td>
				<td><?= $CURRENCY_TYPE.number_format($product['refund_price'],2);?></td>
				<td colspan="2"><?= $CURRENCY_TYPE.number_format($refundAmount,2);?></td>
				</tr>
		<?php 	}
		}
			$afterRefundAmount = $totalAmount-$refundedAmount;
		?>
		<tr>
			<td colspan="5"><strong>Total Refunded Amount</strong></td>
			<td colspan="2"><?=$CURRENCY_TYPE.number_format($refundedAmount,2);?></td>
		</tr>
		<tr>
			<td colspan="5"><strong>Amount After Refund</strong></td>
			<td colspan="2"><?=$CURRENCY_TYPE.number_format($afterRefundAmount,2);?></td>
		</tr>
	<?php }?>
	<tr>
			<td colspan="2">Tel(Sales) </br><?php
					if(!empty($kioskContact)){
						echo $kioskContact;
					}else{
						echo $settingArr['tele_sales'];
					}
					?></td>



			<td colspan="2">Fax(Sales) </br><?=$settingArr['fax_number'];?></td>
			<?php if(!empty(trim($kioskDetails['terms'])) || (!empty($new_kiosk_data) && !empty(trim($new_kiosk_data[0]->terms)))){
					 if(!empty(trim($kioskDetails['terms']))){?>
					      <td colspan="2">Email </br><?=$kioskDetails['email'];?></td> 
					 <?php }else{ ?>
					      <td colspan="2">Email </br><?=$new_kiosk_data[0]->email;?></td>
					 <?php }?>
					
			<?php }else{?>
					 <td colspan="2">Email </br><?=$settingArr['email'];?></td>
			<?php }?>
			<?php if(empty(trim($kioskDetails['terms'])) && empty(!empty($new_kiosk_data) && trim($new_kiosk_data[0]->terms))){ ?>
					<td colspan="2">Website </br><?=$settingArr['website'];?></td>
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
	<p style="text-align: left; font-size: 10px;">
		<?php
		if(!empty($kioskDetails) && !empty($kioskDetails['terms'])){
					 echo $kioskDetails['terms'];
		}elseif(!empty($new_kiosk_data) && !empty(trim($new_kiosk_data[0]->terms))){
					 echo $new_kiosk_data[0]->terms;
        }else{
					 echo $settingArr['invoice_terms_conditions'];			 
		}
		?>
	</p>
</div>
<?php
echo $output = ob_get_clean();
?>
	<table>
	<?php
	//pr($paymentDetails);die;
		if(isset($paymentDetails)){
			$rowStr = "";
			$created = "";
			//$created = $this->Time->format('d-m-Y',$productReceipt['ProductReceipt']['created'],null,null);
			foreach($paymentDetails as $key => $sngPmtDet){
				$pmtMethod = $sngPmtDet['payment_method'];
				$pmtDesc = $sngPmtDet['description'];
				$amt = $sngPmtDet['amount'];
				$creat = $sngPmtDet['created'];
				$created = date("d-m-Y",strtotime($creat));//$this->Time->format('d-m-Y',$creat,null,null);
				$srNo = $key + 1;
				$rowStr.="<tr><td>$srNo</td><td>$created</td><td>$pmtMethod</td><td>$pmtDesc</td><td>$amt</td></tr>";
			}
			if(!empty($rowStr)){
				echo $pmtStr = <<<PMT_STR
				<br/><h3>Payment Details:</h3>
				<table>
				<tr><th>Sr No.</th><th>Payment date</th><th>Payment Method</th><th>Description</th><th>Amount</th></tr>
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
					'name' => 'customer_email'
						)
					);
		}
	?>
	<?php
	//$option = array('name'=>'send_receipt', 'value'=>'Send');
	echo $this->Form->input(null,array('type' => 'hidden','value' => base64_encode($output),'name' => 'emailContent'));
	echo $this->Form->submit('Send',array('name'=>'submit'));
	echo $this->Form->end();?>

