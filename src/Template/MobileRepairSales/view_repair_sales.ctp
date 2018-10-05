<?php
	use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php //echo $this->Html->link(__('New Mobile Repair Log'), array('action' => 'add')); ?></li>
		<li><?php //echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php //echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Repairs'), array('controller' => 'mobile_repairs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Repair'), array('controller' => 'mobile_repairs', 'action' => 'add')); ?> </li>
	</ul>
</div>
<?php
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($search_kw)){$search_kw = "";}
	if(!isset($search_kw1)){$search_kw1 = "";}
	if(!isset($paymentMode)){$paymentMode = "Multiple";}
	if(!isset($missing_payment)){$missing_payment = "";}
?>
<div class="mobileRepairLogs index">
	<h2><?php echo __('Mobile Repair Sales'); ?></h2>
	<form action='<?php echo $this->request->webroot; ?>mobile-repair-sales/search' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div style="height: 68px;">
				<table>
					<tr>
						<td><input type = "text" name = "search_kw" id = "search_kw" placeholder = "repair id" autofocus style = "width:150px;height: 25px;"value='<?php echo $search_kw;?>'/></td>
						<td><input type = "text" name = "search_kw1" id = "search_kw1" placeholder = "imei" autofocus style = "width:120px;height: 25px;"value='<?php echo $search_kw1;?>'/></td>
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:90px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:90px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
	<?php	
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
							<td>
								
								<?php
								if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
									if(!empty($kioskId)){
										echo $this->Form->input(null, array(
																			  'options' => $kiosks,
																			   'label' => false,
																			   'div' => false,
																				 'name' => 'RepairSale[kiosk_id]',
																				'id'=> 'kioskid',
																				'value' => $kioskId,
																				'empty' => 'Select Kiosk',
																				'style' => 'width:185px'
																			)
																);
									}else{
											echo $this->Form->input(null, array(
																		'options' => $kiosks,
																		'label' => false,
																		'div' => false,
																		 'name' => 'RepairSale[kiosk_id]',
																		'id'=> 'kioskid',
																		'empty' => 'Select Kiosk',
																		'style' => 'width:185px'
																		)
															  );
									}
								}else{
									if(!empty($kioskId)){
										echo $this->Form->input(null, array(
																			  'options' => $kiosks,
																			   'label' => false,
																			   'div' => false,
																				 'name' => 'RepairSale[kiosk_id]',
																				'id'=> 'kioskid',
																				'value' => $kioskId,
																				'empty' => 'Select Kiosk',
																				'style' => 'width:185px'
																			)
																);
									}else{
											echo $this->Form->input(null, array(
																		'options' => $kiosks,
																		'label' => false,
																		'div' => false,
																		 'name' => 'RepairSale[kiosk_id]',
																		'id'=> 'kioskid',
																		'empty' => 'Select Kiosk',
																		'style' => 'width:185px'
																		)
															  );
									}
								}
									?></span>
							</td>
							<?php  }  ?>
	 			
						<td><input type = "submit" value = "Search" name = "submit" 'style' ='width:185px'/>
						<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;color:#333; border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
						
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
			<?php
			$total = 0;
			$total1 = array();
			//echo $saleSum;die;
			
			foreach ($mobileRepairSales as $mobileRepairSale){
						$total1[$mobileRepairSale['MobileRepairSale']["mobile_repair_id"]] = $mobileRepairSale['MobileRepairSale']['amount'];
				}
				foreach($total1 as $key => $value){
					$total = $total+$value;
				}
					if($paymentMode == 'Multiple'){
						//$saleSum = $total;
					}
					
				
				?>
			<?php if(count($this->request->query)){
				$netSale = $saleSum-$refundSum;
				?>
               
			<span style="float: left;font-weight : bold;margin-left: 9px;margin-top: 23px;">Gross Sale = &#163;<?=$saleSum?>, Refund = &#163;<?=$refundSum;?>, Net Sale = &#163;<?=$netSale;?></span><br><br>
			<?php } ?>
			<?php
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
					?>
					</br> <span <span style="margin-left: 9px;">  ** Missing payment can be filter with or without koisk(No other combination will work).</span> 
					<?php
				}
				?>
		</fieldset>	
	</form>
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo ('Id'); ?></th>
			<th><?php echo ('Kiosk Name'); ?></th>
			<th><?php echo ('Sold By'); ?></th>
			<th><?php echo ('Sold On'); ?></th>
			<?php if($paymentMode == 'refunded'){ ?>
				<th><?php echo ('Org Amt');?></th>
			<?php } ?>
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
		$repairOptions = $repairStatusTechnicianOptions+$repairStatusUserOptions;
            //  pr($mobileRepairSales);die;
		$totalSearchedAmount = 0;
		foreach ($mobileRepairSales as $mobileRepairSale):
		 //pr($mobileRepairSale);die;
		if($mobileRepairSale->kiosk_id == 10000)continue;
		$totalSalePrice += $mobileRepairSale->amount;
		$amount = $mobileRepairSale->amount;
		if($amount>0){
			if(array_key_exists($mobileRepairSale->mobile_repair_id,$paymentArr)){
				foreach($payment_amount_arr[$mobileRepairSale->mobile_repair_id] as $pmtMode => $pmtAmt){
					if($paymentMode != '' && $paymentMode != 'Multiple'){
						if($paymentMode == $pmtMode){
							//echo $totalSearchedAmount.":".$pmtAmt;
							$totalSearchedAmount+=$pmtAmt;//getting total amount as per the searched mode
						}
					}
				}
				if(count($paymentArr[$mobileRepairSale->mobile_repair_id]) > 1){
					$multipleModes = array();
					foreach($payment_amount_arr[$mobileRepairSale->mobile_repair_id] as $pmtMode => $pmtAmt){
						$multipleModes[] = "$pmtMode = $pmtAmt";
					}
					$multileStr = implode(', ',$multipleModes);
					$mode = "Multiple($multileStr)";
				}elseif(count($paymentArr[$mobileRepairSale->mobile_repair_id]) == 1){
					#$mode = $paymentArr[$mobileRepairSale['MobileRepairSale']['mobile_repair_id']][0]['RepairPayment']['payment_method'];
					$singleMode = $paymentArr[$mobileRepairSale->mobile_repair_id][0]['payment_method'];
					$singleAmount = $paymentArr[$mobileRepairSale->mobile_repair_id][0]['amount'];
					$mode = "$singleMode($singleAmount)";
				}
			}else{
				$mode = '--';
			}
		}else{
			$mode = '--';
		}
		 
		$refundAmount = $mobileRepairSale->refund_amount;
		$salePrice = $mobileRepairSale->amount;
		if(empty($mobileRepairSale->refund_by)){
			$users[$mobileRepairSale->refund_by] = '--';
		}
		if(empty($mobileRepairSale->refund_amount)){
			$mobileRepairSale->refund_amount = '--';
		}	
		if(empty($mobileRepairSale->refund_remarks)){
			$mobileRepairSale->refund_remarks = '--';
		}
		if(empty($mobileRepairSale->refund_amount)){
			$mobileRepairSale->refund_amount = '--';
		} ?>
		 
		<?php
			$status = $mobileRepairSale->status;
			if($status == 1){?>
				<tr style="background:yellow;">
		<?php }elseif($mode == '--' && $amount >0){ ?>
				<tr style="color: blue;">
		<?php }else{ ?>
				<tr>
		<?php } ?>
		<td><?php echo $this->Html->link($mobileRepairSale->mobile_repair_id,array('controller'=>'mobile_repairs','action'=>'view',$mobileRepairSale->mobile_repair_id),array('title'=>'View','alt'=>'View')); ?></td>
		<td><?php
				if(array_key_exists($mobileRepairSale->kiosk_id,$kiosks)){
					echo $kiosks[$mobileRepairSale->kiosk_id];
				}else{
					echo "--";
				}
					
					?>
		</td>
		<td><?php if(array_key_exists($mobileRepairSale->sold_by,$users)){
			echo $users[$mobileRepairSale->sold_by];
		}else{
			echo "--";
		}
		?></td>
		<td><?php
        if(!empty($mobileRepairSale->sold_on)){
              $mobileRepairSale->sold_on->i18nFormat(
										[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
								);
			 $sold_on = $mobileRepairSale->sold_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
            echo date('d-m-y h:i:s',strtotime($sold_on));    
        }else{
            echo "--";
        }
		
		//echo $this->Time->format('jS M, Y g:i A', $mobileRepairSale->sold_on,null,null); ?></td>
		<?php if($paymentMode == 'refunded'){?>
			<td>
				<?php
						if(array_key_exists($mobileRepairSale->mobile_repair_id,$origAmtArr)){
                            
							echo number_format($origAmtArr[$mobileRepairSale->mobile_repair_id],2);
						}
				?>
			</td>
			<?php } ?>
		<td><?php
				//$totalSalePrice += $mobileRepairSale['MobileRepairSale']['amount'];
				//$amount = $mobileRepairSale['MobileRepairSale']['amount'];
                $amount = number_format($amount,2);
				echo $CURRENCY_TYPE.$amount;?></td>
		<td><?=$mode;?></td>
		<td><?php
        if(array_key_exists($mobileRepairSale->refund_by,$users)){
					  echo $users[$mobileRepairSale->refund_by];
				}else{
					echo "--";
				}
       ?></td>
		<td>
		<?php
			if(!empty($mobileRepairSale->refund_on)){
				   $mobileRepairSale->refund_on->i18nFormat(
										[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
								);
				$refund_on = $mobileRepairSale->refund_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
				echo date('d-m-y h:i:s',strtotime($refund_on));
				//echo $this->Time->format('jS M, Y g:i A', $mobileRepairSale->refund_on,null,null);
			}else{
				echo "--";
			}
			?>
		</td>
		<td><?php
				//$totalRefund += $mobileRepairSale['MobileRepairSale']['refund_amount'];
				 $refund_amount = $mobileRepairSale->refund_amount;
				 if($refund_amount < 0){
					$refund_amount = $refund_amount * (-1); 
					}
					$totalRefund += (float)$refund_amount;
					if($refund_amount == "" || $refund_amount == "--"){
						echo $CURRENCY_TYPE.'0.00';
					}else{
                        $refund_amount = number_format($refund_amount,2);
							echo $CURRENCY_TYPE.$refund_amount;
					}
		?>&nbsp;</td>
		<td><?php echo $mobileRepairSale->refund_remarks; ?>&nbsp;</td>
		<td><?php
		if(array_key_exists($mobileRepairSale->mobile_repair_id,$mobileRepairStsArray) && $mobileRepairSale->repair_status == -1){//for old records that does not have repair_status updated
			$status =  $mobileRepairStsArray[$mobileRepairSale->mobile_repair_id];
            //pr($repairOptions);
            //pr($mobileRepairStsArray);
            //pr($mobileRepairSale);die;
            if(array_key_exists($mobileRepairStsArray[$mobileRepairSale->mobile_repair_id],$repairOptions)){
                if(array_key_exists($mobileRepairSale->mobile_repair_id,$mobileRepairStsArray)){
                    echo wordwrap($repairOptions[$mobileRepairStsArray[$mobileRepairSale->mobile_repair_id]],25,"<br>\n");
                }
                
            }
			
		}elseif(array_key_exists($mobileRepairSale->repair_status, $repairOptions)){
			echo  wordwrap($repairOptions[$mobileRepairSale->repair_status],25,"<br>\n");
		}else{
			echo "--";
		}
		  ?></td>
		<td><?php
		$refundUrl = "/img/fileview_close_right.png";
		$updateUrl = "/img/16_edit_page.png";
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
				if($mode == '--' && $amount >0){ 
					echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'mobile_repairs', 'action' => 'add_repair_payment', $mobileRepairSale->mobile_repair_id), array('escapeTitle' => false, 'title' => 'Add missing payment', 'alt' => 'Update payment'));
				}
				if((int)$salePrice && empty($refundAmount)){
					if($mode != "--"){
						echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'mobile_repairs', 'action' => 'update_repair_payment', $mobileRepairSale->mobile_repair_id), array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment'));
					}
					//echo $this->Html->link($this->Html->image($refundUrl,array('fullBase' => true)), array('action' => 'mobile_repair_refund', $mobileRepairSale['MobileRepairSale']['id']), array('escapeTitle' => false, 'title' => 'Refund', 'alt' => 'Refund'));
					echo $this->Html->link('Refund', array('controller' => 'mobile_repair_sales', 'action'=> 'mobile_repair_refund', $mobileRepairSale->id)) ;
				}else{
					if($mode != "--"){
						echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'mobile_repairs', 'action' => 'update_repair_payment', $mobileRepairSale->mobile_repair_id), array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment'));
					}
					//echo "--";
				}
			}else{
				if($mode != "--"){
						echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'mobile_repairs', 'action' => 'update_repair_payment', $mobileRepairSale->mobile_repair_id), array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment'));
					}
				//echo "--";
			}
		?></td>
		</tr>
	<?php endforeach;
	if($totalSearchedAmount > 0){
		$totalSalePrice = $totalSearchedAmount;
	}
	?>
	</tbody>
	<tr><?php if($paymentMode == 'refunded'){ ?>
     <td colspan='5'></td><th><?php echo $CURRENCY_TYPE.$totalSalePrice;  ?>
	</th><td colspan='3'></td><th><?php echo $CURRENCY_TYPE.$totalRefund; ?></th>
   <?php }
        else{
        ?>
    <td colspan='4'></td><th><?php echo $CURRENCY_TYPE.$totalSalePrice;  ?>
	</th><td colspan='3'></td><th><?php echo $CURRENCY_TYPE.$totalRefund; ?></th>
    <?php }?>
    </tr>
	</table>
	<p<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	
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
		$('#refunded_radio').attr('checked', false)
	}
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>
