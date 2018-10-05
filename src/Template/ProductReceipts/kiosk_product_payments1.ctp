<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php

	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	//pr($paymentArr);die;
	//echo $paymentMode;die;
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($search_kw)){$search_kw = "";}
	if(!isset($search_kw1)){$search_kw1 = "";}
	if(!isset($paymentMode)){$paymentMode = "Multiple";}
	$kiosks['-1'] = 'All';
?>
<div class="mobileRepairLogs index">
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo __('Product Payments')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>";?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<form action='<?php echo $this->request->webroot; ?>ProductReceipts/search_product_payments' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div style="height: 69px;">
				<table>
					<tr>
						<td><input type = "text" name = "search_kw" id = "search_kw" placeholder = "receipt id" autofocus style = "width:150px;height: 25px;"value='<?php echo $search_kw;?>'/></td>
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:90px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:90px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
	<?php	
	if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
						<td>
							<?php
								if(!empty($kioskId)){
									echo $this->Form->input(null, array(
										'options' => $kiosks,
										 'label' => false,
										 'div' => false,
										       'name' => 'ProductSale[kiosk_id]',
										      'id'=> 'kioskid',
										      'value' => $kioskId,
										      //'empty' => 'Select Kiosk',
										      'style' => 'width:185px'
											)
										);
								}else{
										echo $this->Form->input(null, array(
											'options' => $kiosks,
											'label' => false,
											'div' => false,
											 'name' => 'ProductSale[kiosk_id]',
											'id'=> 'kioskid',
											//'empty' => 'Select Kiosk',
											'style' => 'width:185px'
												)
											);
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
								</tr>
							</table>
						</td>
					</tr>
				</table>
			
			</div></br>
			<div>**for all option we are displaying sale transaction and refund for same transaction.</div>
			<?php
			
			if(count($this->request->query)>1 && array_key_exists('submit',$this->request->query)){
				$netSale = $saleSum-$refundSum;
				$netSale = round($netSale,2);
				$saleSum = round($saleSum,2);
				$refundSum = round($refundSum,2);
				?>
			
			<span style="float: left; font-weight : bold">Gross Sale = &#163;<?=$saleSum?>, Refund = &#163;<?=$refundSum;?>, Net Sale = &#163;<?=$netSale;?></span>
			<?php } ?>
		</fieldset>	
	</form>
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th>Kiosk Name</th>
			<th><?php echo $this->Paginator->sort('bill_amount','Orig Bill Amount'); ?></th>
			<th>Refund</th>
			<th>Refund By</th>
			<th>Payment</th>
			<th><?php echo $this->Paginator->sort('processed_by'); ?></th>
			<th><?php echo $this->Paginator->sort('created','Sale Date'); ?></th>
			<?php if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){?>
			<th class="actions"><?php echo __('Actions'); ?></th>
			<?php } ?>
	</tr>
	</thead>
	<tbody>
		<?php
		$totalSearchedAmount = 0;
		$totalSalePrice = 0;
		$prodRefunded = false;
		
		//pr($productReceipts);
		
		foreach($productReceipts as $key => $productReceipt){
            //pr($paymentArr);die;
			$receiptID = $productReceipt->id;
			$total_amount = 0;
			if(array_key_exists($productReceipt->processed_by,$users)){
				$userName = $users[$productReceipt->processed_by];
			}else{
				$userName = "--";
			}
			
			$totalSalePrice+= $productReceipt->bill_amount;
			if(array_key_exists($receiptID,$payment_amount_arr)){
				foreach($payment_amount_arr[$receiptID] as $pmtMode => $pmtAmt){
					if($paymentMode != '' && $paymentMode != 'Multiple'){ 
						if($paymentMode == $pmtMode){
							//echo "hi";
							$totalSearchedAmount+=$pmtAmt;//getting total amount as per the searched mode
							//echo $totalSearchedAmount;die;
						}elseif($paymentMode == "refunded"){
							$totalSearchedAmount+=$pmtAmt;
						}
					}else{
						$totalSearchedAmount+=$pmtAmt;//getting total amount as per the searched mode
					}
				}
				$prodRefunded = false;
				//Note: In the case of refund, we have -ve amount in ProductPayment and status = 2
				if(count($paymentArr[$receiptID]) > 1){
					$multipleModes = array();
					foreach($payment_amount_arr[$receiptID] as $pmtMode => $pmtAmt){
						$pmtAmt = number_format($pmtAmt,2);
						$multipleModes[] = "$pmtMode = $pmtAmt";
						if($paymentMode == 'Multiple'){
							if($pmtAmt > 0){
								$total_amount = $total_amount+$pmtAmt;
							}
						}else{
							$total_amount = $total_amount+$pmtAmt;
						}
					}
					$multileStr = implode(', ',$multipleModes);
					$mode = "Multiple($multileStr)";
				}elseif(count($paymentArr[$receiptID]) == 1){
					$singleMode = $paymentArr[$receiptID][0]['payment_method'];
					$singleAmount = round($paymentArr[$receiptID][0]['amount'],2);
					$mode = "$singleMode($singleAmount)";
					$total_amount = $singleAmount;
					$pmtStatus = $paymentArr[$receiptID][0]['status']; //should be 2 for refunded entries
					$prodAmt = $paymentArr[$receiptID][0]['amount']; //should be -ve for refunded entries
					if($pmtStatus == 2 && $prodAmt < 0){
						$prodRefunded = true;
					}
					if($receiptID == 1277){
						//pr($paymentArr[$receiptID]);
						//echo "$receiptID: sin	gleMode = $singleMode , singleAmount = $singleAmount, ";
					}
				}
			}else{
				$mode = '--';
			}
			$updateUrl = "/img/16_edit_page.png";
			$orgBillAmt = $productReceipt->orig_bill_amount;
			$style = "";
			if(empty($orgBillAmt)){
				$orgBillAmt = 0;
				$mode = "---";
				$style = "style='background:red;color:white;'";
			}
			if(array_key_exists($receiptID,$ref_status)){
				$style = "style='background:yellow;'";
			}
			?>
			
			
			<tr <?=$style?>>
				<td><?=$receiptID;?></td>
				<td><?=$kiosks[$kioskId];?></td>
				<td><?php echo $orgBillAmt;?></td>
				<td><?php
						if($total_amount < 0){
							if($paymentMode == "refunded"){
								$refund = $refundedEntries[$receiptID];
							}else{
								$refund = $total_amount;
							}
							//$refund = $orgBillAmt-$total_amount;
						}else{
							$refund = 0.00;
							if($paymentMode == "refunded"){
								$refund = $refundedEntries[$receiptID];
							}else{
								if($prodRefunded){
									$refund = $orgBillAmt-$total_amount;
								}
							}
							
						}
						echo $refund;			
				?></td>
				<td><?php
				$txt = "";
				if(array_key_exists($receiptID,$ref_status)){
					foreach($ref_by_s[$receiptID] as $k => $val){
						if($val == 0 || $val == ""){
							continue;
						}
						if(array_key_exists($val,$users)){
							$uname = $users[$val];
						}else{
							$uname ="--";
						}
						$txt .= " ".$uname."</br>";					
					}
				}
				echo $txt;
				?></td>
				<td><?=$mode;?></td>
				<td><?php
				if(array_key_exists($productReceipt->processed_by,$users)){
					echo $users[$productReceipt->processed_by];
				}else{
					echo "--";
				}
				?></td>
				<td><?=date('jS M, Y h:i A',strtotime($productReceipt->created));//$this->Time->format('jS M, Y h:i A', $productReceipt->created,null,null);?></td>
				<td><?=$this->Html->link('view',array('action' => 'view_kiosk_sale',$kioskId,$receiptID));?></td>
				<td><?php if($mode != "--"){
						echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'kiosk_product_sales', 'action' => 'update_product_payment', $receiptID,$kioskId), array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment'));
					}?>
				</td>
			</tr>
		<?php }
		if($totalSearchedAmount > 0){
		$totalSalePrice = $totalSearchedAmount;
	}?>
	</tbody>
	<tr><td colspan='1'></td>
	<td colspan='3'><strong>Total (as per selected mode)</strong></td>
	<th><?php
				if($paymentMode == "refunded"){
						echo $CURRENCY_TYPE.$totalSearchedAmount;	
				}else{
						echo $CURRENCY_TYPE.$totalSalePrice;	
				}
	?></th>
	</tr>
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
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
	</ul>
</div>
 <script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#search_kw1").val("");
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
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>