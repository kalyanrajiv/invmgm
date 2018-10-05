<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$siteBaseUrl = Configure::read('SITE_BASE_URL');
	$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
	if(defined('URL_SCHEME')){
		$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
	}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?><div class="invoiceOrders view">
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<div id='printDiv'>
	<table border="1" cellspacing="0" style="width: 700px;">
		<tr>
			<td><?php
				
				if(empty(trim($new_kiosk_data[0]->terms))){
					$imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
					echo $this->Html->image($imgUrl, array('fullBase' => true));
				}else{
					if(!empty(trim($new_kiosk_data[0]->logo_image))){
						$imgUrl = $siteBaseUrl."/logo/".$new_kiosk_data[0]->id."/".$new_kiosk_data[0]->logo_image;
						echo $this->Html->image($imgUrl, array('fullBase' => true,'style'=> "width: 332px;height: 102px;"));	
					}else{
						 $imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
						 echo $this->Html->image($imgUrl, array('fullBase' => true));
					}
				}
					
			
			?>
			<?php
			//<table style="text-align: center;float: right; width: 320px;margin-top: 27px;margin-right: 8px;">
			//<?php if(empty(trim($new_kiosk_data[0]->terms))){ 
			//		 <tr>
			//				<td> <?=$settingArr['headoffice_address']; </td> 
			//		</tr>
			//		 <?php }
			//</table>
			?>
			<table border = '1' style="text-align: center;float: right; width: 320px;margin-top: 31px;margin-right: 8px;">
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
			
			
				<table style="text-align: center;float: right; width: 100%;">
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
								<th>Rep</th>
							</tr>
							<tr><?php //pr($invoiceOrder);die;  ?>
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
								<td><?=date('d-m-Y',strtotime($invoiceOrder['modified']));//$this->Time->format('d-m-Y',$invoiceOrder['modified'],null,null);?></td>
								<td>PER<?=$invoiceOrder['id'];?></td>
								<td><?=$userName;?></td>
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
		<td>
			<table cellspacing="0">
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th width='50%'>Invoice To</th>								
								<th width='50%'>Ship To</th>
							</tr>
							<tr><?php //pr($customerData);die;?>
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
		<td>
			<table border="1" cellspacing="0">
			<tr>
				<th>Cust Number:</th>
				<th>Cust Vat No</th>
				<th>Pay Terms</th>
			</tr>
			<tr>
				<td><?=$customerData['id'];?></td>
				<td><?=$customerData['vat_number'];?></td>
				<td></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" >
			<tr>
				<td colspan="3" style="width: 880px;"><strong>Product</b></td>
				<td><strong>Product</br>Code</strong></td>
				<td><strong>Sale Price</strong></td>
				<td><strong>Quantity</strong></td>
				<td><strong>Discount Price</strong></td>
				<td><strong>Amount</strong></td>
			</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$sub_total = $totalDiscount = 0;
	$netAmount = $invoiceOrder['amount'];
	$bulkDiscountPercentage = $invoiceOrder['bulk_discount'];
	//pr($invoiceOrderDetailData);
	foreach($invoiceOrderDetailData as $key => $orderDetail){
		$vatItem = $vat/100;
		if($orderDetail['discount'] < 0){
			$discountAmount = $itemPrice = round($orderDetail['price'],2);
			
		}else{
			$itemPrice = round($orderDetail['price']/(1+$vatItem),2);
			$discount = round($itemPrice*$orderDetail['discount']/100*$orderDetail['quantity'],2);
			$discountAmount = round(($orderDetail['quantity']*$itemPrice)-$discount,2);
		}
		
		
		
		if($orderDetail['discount'] < 0){
			$discountAmount_s = $discountAmount * $orderDetail['quantity'];	
		}else{
			$discountAmount_s = $discountAmount;
		}
		
		$sub_total = $sub_total + $discountAmount_s;
		$amount+=$discountAmount;
		list($productCode, $productTitle) = $productName[$orderDetail['product_id']];
		?>
		<tr>
			<td colspan="3" style="width: 880px;"><?= $productTitle;?></td>
			<td><?= $productCode;?></td>
			
			<td><?php
			if($orderDetail['discount'] < 0){
				echo  $CURRENCY_TYPE.number_format($discountAmount,2); 	
			}else{
				echo  $CURRENCY_TYPE.number_format($itemPrice,2); 	
			} ?>
			</td>
			<td><?= $orderDetail['quantity'];?></td>
			<td>
            <?php
            if($orderDetail['discount'] == 0 || empty($orderDetail['discount'])){
                echo $CURRENCY_TYPE.number_format($itemPrice,2);    
            }elseif($orderDetail['discount'] < 0){
				echo $CURRENCY_TYPE.number_format($discountAmount,2);    
				}else{
                $dis_amt = $itemPrice - $itemPrice*($orderDetail['discount']/100);
                echo $CURRENCY_TYPE.number_format($dis_amt,2);
            }// number_format(round($orderDetail['discount'],2),2);%
            ?>
            </td>
			<td><?=  $CURRENCY_TYPE.$discountAmount_s;?></td>
		</tr>
	<?php 
	}
	//echo $amount;
		$amount = round($amount,2);
		$sub_total = round($sub_total,2);
		$bulkDiscount = round($sub_total*$invoiceOrder['bulk_discount']/100,2);
		$netAmount = round($sub_total - $bulkDiscount,2);
		$finalAmount = $invoiceOrder['amount'];
		$finalVat = round($finalAmount-$netAmount,2);
        //pr($finalAmount);die;
	?>
			
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;">
				<tr>
					<td rowspan="5"><strong>Bank Details:</strong><br/><?php
				
							if(empty(trim($new_kiosk_data[0]->terms))){
								//echo $NewkioskDetails['terms'];
							echo $settingArr['bank_details'];
							}
					
					?></td>
					<th>Sub Total</th>
					<td><?php echo $CURRENCY_TYPE.round($sub_total,2);?></td>
				</tr>
				<tr>
					<th>Bulk Discount (<?=$invoiceOrder['bulk_discount'];?>%)</th>
					<td><?= $CURRENCY_TYPE.round($bulkDiscount,2);?></td>
				</tr>
				<tr>
					<th>Sub Total(after bulk discount)</th>
					<td><?php echo $subTotal = $CURRENCY_TYPE.round($netAmount,2);?></td>
				</tr>
				<tr>
					<th>VAT</th>
					<td>
					<?php
						if($customerData['country'] != 'OTH'){
							$vatAmt = round(($netAmount * $vat)/100,2);
						}
						if($customerData['country'] == 'OTH'){
							echo $CURRENCY_TYPE.'0.00';
						}else{
							echo $CURRENCY_TYPE.$vatAmt;
						}
					?></td>
				</tr>				
				<tr>
					<th>Total Amount</th>
					<td>
					<?php
					if($customerData['country'] == 'OTH'){
						echo $CURRENCY_TYPE.$finalAmount;
					}else{
						echo  $CURRENCY_TYPE.($vatAmt+$netAmount);
					}
					?>
					</td>
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
						echo $settingArr['tele_sales'];
					}
					?></td>
					
					<td>Fax(Sales) <?=$settingArr['fax_number'];?></td>
					<?php if(!empty(trim($new_kiosk_data[0]->terms))){ ?>
						<td>Email <?=$new_kiosk_data[0]->email;?></td>
					<?php }else{?>
						<td>Email <?=$settingArr['email'];?></td>
					<?php } ?>
					
					<?php if(empty(trim($new_kiosk_data[0]->terms))){ ?>
						<td>Website <?=$settingArr['website'];?></td>
					<?php }?>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	<table style="text-align: center;width: 320px;margin-top: 27px;margin-right: 8px;">
			<?php if(empty(trim($new_kiosk_data[0]->terms))){ ?>
					 <tr>
							<td> <?=$settingArr['headoffice_address'];?> </td> 
					</tr>
					 <?php } ?>
			</table>
		
	<p style="text-align: center; font-size: 10px;">
		<?php
		if(!empty($new_kiosk_data) && !empty($new_kiosk_data[0]->terms)){
			echo $new_kiosk_data[0]->terms;
		}else{
				echo $settingArr['invoice_terms_conditions'];
		}
			?>
		
	</p>
	
	
			
	
</div>	

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
        <?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');//AuthComponent::user('username');
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){?>
		<li><?php echo $this->Html->link(__('Create Sale'), array('action' => 'select_option',$invoiceOrder['id'])); ?> </li>
		<?php }else{ ?>
		<li><?php echo $this->Html->link(__('Create Sale'), array('action' => 'payment_options',$invoiceOrder['id'])); ?> </li>
		<?php } ?>
		<li><?php echo $this->Html->link(__('List Performa'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('Edit Performa'), array('controller' => "home",'action' => 'edit-bulk-performa', $invoiceOrder['id'],$invoiceOrder['customer_id'])); ?></li>
	</ul>
</div>
<?php echo $this->Form->create();?>
	<?php
	$customerEmail = "";
	if(isset($customerData['email']) && !empty($customerData['email'])){
		$customerEmail = $customerData['email'];
	}
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