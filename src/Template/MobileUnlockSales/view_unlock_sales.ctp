<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\Time;

	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00",'places' => 4, 'escape' => false));
?>
<?php
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($search_kw)){$search_kw = "";}
	if(!isset($search_kw1)){$search_kw1 = "";}
	if(!isset($paymentMode)){$paymentMode = "Multiple";}
	if(!isset($missing_payment)){$missing_payment = "";}
    //pr($kiosk_id);die;
	//echo $missing_payment;die;
	$unlockOptions = $unlockStatusUserOptions+$unlockStatusTechnicianOptions;
?>
<div class="mobileRepairLogs index">
	<h2><?php echo __('Mobile Unlock Sales'); ?></h2>
	<form action='<?php echo $this->request->webroot; ?>mobile-unlock-sales/search' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div style="height: 67px;">
				<table>
					<tr>
						<td><input type = "text" name = "search_kw" id = "search_kw" placeholder = "unlock id"  autofocus style = "width:130px;height: 25px;"value='<?php echo $search_kw;?>'/></td>
						<td><input type = "text" name = "search_kw1" id = "search_kw1" placeholder = "imei"  autofocus style = "width:120px;height: 25px;"value='<?php echo $search_kw1;?>'/></td>
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:85px;height: 25px;" value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:85px;height: 25px;" value='<?php echo $end_date;?>' /></td>
						<?php	
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
							<td>
								
								<?php
								if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
									if(!empty($kiosk_id)){
										echo $this->Form->input(null, array(
																			  'options' => $kiosks,
																			   'label' => false,
																			   'div' => false,
																				'name' => 'data[UnlockSale][kiosk_id]',
																				'id'=> 'kioskid',
																				'value' => $kiosk_id,
																				'empty' => 'Select Kiosk',
																				'style' => 'width:175px'
																			)
																);
									}else{
											echo $this->Form->input(null, array(
																		'options' => $kiosks,
																		'label' => false,
																		'div' => false,
																		'name' => 'data[UnlockSale][kiosk_id]',
																		'id'=> 'kioskid',
																		'empty' => 'Select Kiosk',
																		'style' => 'width:175px'
																		)
															  );
									}
								}else{
									if(!empty($kiosk_id)){
										echo $this->Form->input(null, array(
																			  'options' => $kiosks,
																			   'label' => false,
																			   'div' => false,
																				'name' => 'data[UnlockSale][kiosk_id]',
																				'id'=> 'kioskid',
																				'value' => $kiosk_id,
																				'empty' => 'Select Kiosk',
																				'style' => 'width:175px'
																			)
																);
									}else{
											echo $this->Form->input(null, array(
																		'options' => $kiosks,
																		'label' => false,
																		'div' => false,
																		'name' => 'data[UnlockSale][kiosk_id]',
																		'id'=> 'kioskid',
																		'empty' => 'Select Kiosk',
																		'style' => 'width:175px'
																		)
															  );
									}
								}
									?></span>
							</td>
							<?php  }  ?>
						<td><input type = "submit" value = "Search" width:1000px name = "submit"/></td>
						<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;width:100px ;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
					</tr>
					<tr>
						<td colspan='7'>
							<table style="width: 60%">
								<tr>
									<td>
										<input type = "radio" name = "payment_mode" value="Cash" id="cash_id" <?=$checked = ($paymentMode == 'Cash') ? 'checked' : '';?>>Cash</td>
									<td>
										<input type = "radio" name = "payment_mode" value="Card" id="card_id" <?=$checked = ($paymentMode == 'Card') ? 'checked' : '';?>>Card</td>
									<td>
										<input type = "radio" name = "payment_mode" value="refunded" id="refunded_radio" <?=$checked = ($paymentMode == 'refunded') ? 'checked' : '';?>>Refunded
									</td>
									<td>
										<input type = "radio" name = "payment_mode" value="Multiple" id="multiple_id" <?=$checked = ($paymentMode == 'Multiple') ? 'checked' : '';?>>All
									</td>
									<?php
									if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
									?>
									<td>
										<input type = "radio" name = "payment_mode" value="missing" id="missing_div" <?=$checked = ($missing_payment == 'missing') ? 'checked' : '';?>>Missing Payment
									</td>
									<?php
									}
									?>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				
			</div>
			<?php if(count($this->request->query)){
				$netSale = $saleSum-$refundSum;
				?>
			<span style="float: left;font-weight : bold;margin-left: 9px;margin-top: 23px;">Gross Sale = &#163;<?=$saleSum?>, Refund = &#163;<?=$refundSum;?>, Net Sale = &#163;<?=$netSale;?></span>
			<br><br>
            <?php } ?>
			<?php
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
					?>
					</br><span <span style="margin-left: 9px;"> ** Missing payment can be filter with or without koisk(No other combination will work).</span> 
					<?php
				}
				?>
		</fieldset>	
	</form>
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<?php //pr($paymentArr);?>
			<th><?php echo ('Unlock Id'); ?></th>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
				<th><?php echo ('Kiosk Name'); ?></th>
			<?php } ?>
			<th><?php echo ('Sold By'); ?></th>
			<th><?php echo ('Sold On'); ?></th>
			<th><?php echo ('Sale Price'); ?></th>
			<th><?php echo ('Payment Mode'); ?></th>
			<th><?php echo ('Refund By'); ?></th>
			<th><?php echo ('Refund On'); ?></th>
			<th><?php echo ('Refund Amount'); ?></th>
			<th><?php echo ('Refund Remarks'); ?></th>
			<th><?php echo ('Status'); ?></th>
			<?php if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){?>
			<th class="actions"><?php echo __('Actions'); ?></th>
			<?php } ?>
	</tr>
	</thead>
	<tbody>
	<?php
	$totalSalePrice = 0;
	$totalRefund = 0;
	$totalSearchedAmount = 0;
	//pr($paymentArr);
	foreach ($mobileUnlockSales as $mobileUnlockSale):
		  $mobileUnlockSale->sold_on->i18nFormat(
												[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
											);
		
		$soldOn =   $mobileUnlockSale->sold_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
		
		if($mobileUnlockSale->kiosk_id == 10000)continue;
	
		//**code for integration of payment
		if(array_key_exists($mobileUnlockSale->mobile_unlock_id,$paymentArr)){
			foreach($payment_amount_arr[$mobileUnlockSale->mobile_unlock_id] as $pmtMode => $pmtAmt){
				if($paymentMode != '' && $paymentMode != 'Multiple'){
					if($paymentMode == $pmtMode){
						$totalSearchedAmount .":". $pmtAmt;
						$totalSearchedAmount+=$pmtAmt;//getting total amount as per the searched mode
					}
				}
			}
			if(count($paymentArr[$mobileUnlockSale->mobile_unlock_id]) > 1){
				$multipleModes = array();
				foreach($payment_amount_arr[$mobileUnlockSale->mobile_unlock_id] as $pmtMode => $pmtAmt){
					$multipleModes[] = "$pmtMode = $pmtAmt";
				}
				$multileStr = implode(', ',$multipleModes);
				$mode = "Multiple($multileStr)";
			}elseif(count($paymentArr[$mobileUnlockSale->mobile_unlock_id]) == 1){
				
				#$mode = $paymentArr[$mobileUnlockSale['MobileUnlockSale']['mobile_unlock_id']][0]['RepairPayment']['payment_method'];
				$singleMode = $paymentArr[$mobileUnlockSale['mobile_unlock_id']][0]['payment_method'];
				$singleAmount = $paymentArr[$mobileUnlockSale['mobile_unlock_id']][0]['amount'];
				$mode = "$singleMode($singleAmount)";
			}
		}else{
			$mode = '--';
		}
		//payment till here **
	
		$refundAmount = $mobileUnlockSale->refund_amount;
		$salePrice = $mobileUnlockSale->amount;
		if(empty($mobileUnlockSale->refund_by)){
			$users[$mobileUnlockSale->refund_by] = '--';
		}
		if(empty($mobileUnlockSale->refund_amount)){
			$mobileUnlockSale->refund_amount = '--';
		}	
		if(empty($mobileUnlockSale->refund_remarks)){
			$mobileUnlockSale->refund_remarks = '--';
		} 		
	?>
	
	<?php
	//pr($unlockAmounts);
		if(!isset($unlockAmounts)){$unlockAmounts=array();}
			if(count($unlockAmounts)){
				if(array_key_exists($mobileUnlockSale->mobile_unlock_id,$unlockAmounts)){
					$amount = $unlockAmounts[$mobileUnlockSale->mobile_unlock_id];
				}else{
					$amount = 0;
				}
				$totalSalePrice += $amount;
			}else{
				$totalSalePrice += $mobileUnlockSale->amount;
				$amount =  $mobileUnlockSale->amount;
			}
	?>
	
	<?php $status = $mobileUnlockSale->status;?>
	<?php if($status == 1){?>
	<tr style="background:yellow;">
	<?php }elseif($mode == '--' && $amount >0){ ?>
	<tr style="color: blue;">
	<?php }else{ ?>
	<tr>
	<?php } ?>
	
	<?php //if($mode == '--' && $amount >0){?>
	
	<?php //}else{ ?>
	
	<?php //} ?>
		<td><?php echo $this->Html->link($mobileUnlockSale->mobile_unlock_id, array('controller'=>'mobile_unlocks', 'action'=>'view',$mobileUnlockSale->mobile_unlock_id),array('alt'=>'View','title'=>'View')); ?></td>
		<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
			<td><?php
			if(array_key_exists($mobileUnlockSale->kiosk_id,$kiosks)){
				echo $kiosks[$mobileUnlockSale->kiosk_id];	
			}else{
				echo "--";
			}
			
			 ?></td>
		<?php } ?>
		<td><?php if(array_key_exists($mobileUnlockSale->sold_by,$users)){
			echo $users[$mobileUnlockSale->sold_by];
		}?></td>
		<td><?php echo date('d-m-y h:i:s',strtotime($soldOn));//$this->Time->format('jS M, Y g:i A', $mobileUnlockSale->sold_on,null,null); ?></td>
		<td><?php
			//if(!isset($unlockAmounts)){$unlockAmounts=array();}
			//if(count($unlockAmounts)){
			//	if(array_key_exists($mobileUnlockSale['MobileUnlockSale']['mobile_unlock_id'],$unlockAmounts)){
			//		$amount = $unlockAmounts[$mobileUnlockSale['MobileUnlockSale']['mobile_unlock_id']];
			//	}else{
			//		$amount = 0;
			//	}
			//	//$amount = $unlockAmounts[$mobileUnlockSale['MobileUnlockSale']['mobile_unlock_id']];
			//	$totalSalePrice += $amount;
			//}else{
			//	$totalSalePrice += $mobileUnlockSale['MobileUnlockSale']['amount'];
			//	$amount =  $mobileUnlockSale['MobileUnlockSale']['amount'];
			//}
            $amount = number_format($amount,2);
			echo $CURRENCY_TYPE.$amount;
			?></td>
		<td><?=$mode;?></td>
		<td><?php
        if(array_key_exists($mobileUnlockSale->refund_by,$users)){
				 echo $users[$mobileUnlockSale->refund_by];
			}else{
				echo "--";
			}
        ?></td>
		<td>
		<?php
		
		if(!empty($mobileUnlockSale->refund_on)){
			 $mobileUnlockSale->refund_on->i18nFormat(
												[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
											);
						$refundOn =   $mobileUnlockSale->refund_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
			echo date('d-m-y h:i:s',strtotime($refundOn));//$this->Time->format('jS M, Y g:i A', $mobileUnlockSale->refund_on,null,null);
		}else{
			echo '--';
		}
		?>
		</td>				
		<td><?php
			//$totalRefund += $mobileUnlockSale['MobileUnlockSale']['refund_amount'];
			$refund_amount =  $mobileUnlockSale->refund_amount;
			if($refund_amount < 0){
				$refund_amount = -$refund_amount; 
			}
			$totalRefund += (float)$refund_amount;
			if($refund_amount == "" || $refund_amount == "--"){
				echo $CURRENCY_TYPE."0.00";
			}else{
                 $refund_amount = number_format($refund_amount,2);
				echo $CURRENCY_TYPE.$refund_amount;
			}
			?>&nbsp;</td>
		<td><?php echo $mobileUnlockSale->refund_remarks; ?>&nbsp;</td>
		<td><?php // pr($mobileUnlockStsArray);pr($unlockOptions);die;
		if(array_key_exists($mobileUnlockSale->mobile_unlock_id,$mobileUnlockStsArray)){
				echo wordwrap($unlockOptions[$mobileUnlockStsArray[$mobileUnlockSale->mobile_unlock_id]],25,"<br>\n");
			}else{
				echo "--";
			}  ?></td>
		<td><?php
		//pr($mobileUnlockSale);
		$refundUrl = "/img/fileview_close_right.png";
		$updateUrl = "/img/16_edit_page.png";
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
			if($mode == '--' && $amount >0){ 
					echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'mobile_unlock_sales', 'action' => 'add_unlock_payments', $mobileUnlockSale->mobile_unlock_id), array('escapeTitle' => false, 'title' => 'Add missing payment', 'alt' => 'Update payment'));
				}
			//if($mobileUnlockStsArray[$mobileUnlockSale['MobileUnlockSale']['mobile_unlock_id']]==UNLOCKED_CONFIRMATION_PASSED ||
			//   $mobileUnlockStsArray[$mobileUnlockSale['MobileUnlockSale']['mobile_unlock_id']]==DELIVERED_UNLOCKED_BY_CENTER ||
			//   $mobileUnlockStsArray[$mobileUnlockSale['MobileUnlockSale']['mobile_unlock_id']]==DELIVERED_UNLOCKED_BY_KIOSK){
				if($status != 1 && $mobileUnlockSale->refund_amount >= 0){
					if($mode != "--"){
						echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'mobile_unlocks', 'action' => 'update_unlock_payment', $mobileUnlockSale->mobile_unlock_id), array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment'));
					}
				#echo $this->Html->link($this->Html->image($refundUrl,array('fullBase' => true)), array('action' => 'mobile_unlock_refund', $mobileUnlockSale['MobileUnlockSale']['id']), array('escapeTitle' => false, 'title' => 'Refund', 'alt' => 'Refund'));
				echo $this->Html->link('Refund', array('controller' => 'mobile_unlock_sales', 'action'=> 'mobile_unlock_refund', $mobileUnlockSale->id));
				//}
			}else{
				//echo $unlockOptions[$mobileUnlockStsArray[$mobileUnlockSale['MobileUnlockSale']['mobile_unlock_id']]];
				//echo "--";
				if($mode != "--"){
					echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'mobile_unlocks', 'action' => 'update_unlock_payment', $mobileUnlockSale->mobile_unlock_id), array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment'));
					}
			}
		}else{
			if($mode != "--"){
				echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'mobile_unlocks', 'action' => 'update_unlock_payment', $mobileUnlockSale->mobile_unlock_id), array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment'));
					}
			//echo "--";
		}
		?></td>
	</tr>
<?php endforeach;
//echo $totalSearchedAmount;
	if($totalSearchedAmount > 0){
		$totalSalePrice = $totalSearchedAmount;
	}
?>
	</tbody><tr>
	<?php
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
		 <td colspan='4'><th><?php echo $CURRENCY_TYPE.$totalSalePrice; ?></th></td> 
		 <td colspan='3'></td><th><?php echo $CURRENCY_TYPE.$totalRefund;?></th></tr>
	<?php }else { ?>
		 <td colspan='3'><th><?php echo $CURRENCY_TYPE.$totalSalePrice; ?></th></td> 
		 <td colspan='3'></td><th><?php echo $CURRENCY_TYPE.$totalRefund;?></th></tr>
		
	<?php } ?>
	
		
	</table>
	<p>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Mobile Repair Log'), array('action' => 'add')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'add')); ?> </li>
	</ul>
</div>
 <script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#search_kw1").val("");
		jQuery("#kioskid").val("");
		$('#cash_id').attr('checked', false)
		$('#card_id').attr('checked', false)
		$('#multiple_id').attr('checked', false)
	}
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>
