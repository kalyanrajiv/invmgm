<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($search_kw)){$search_kw = "";}
	if(!isset($search_kw1)){$search_kw1 = "";}
	if(!isset($paymentMode)){$paymentMode = "";}
	$kiosks['-1'] = 'All';
	$kioskId = -1;
?>
<div class="mobileRepairLogs index">
	<h2><?php echo __('Product Payments'); ?></h2>
	<form action='<?php echo $this->request->webroot; ?>ProductReceipts/search_product_payments' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div style="height: 69px;">
				<table>
					<tr>
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
						<td colspan='6'>
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
			
			</div>
		</fieldset>	
	</form>
	<table>
		<tr>
			<th>Kiosk</th>
			<th>Total Sale</th>
			<th>Refund</th>
			<th>Net Sale</th>
			<th style="color: blue;">Card</th>
			<th style="color: brown;">Cash</th>
		</tr>
		<?php
		$grandSale = $grandRefund = $grandNetSale = 0;
		foreach($saleSumData as $kiosk_Id => $saleSum){
            //pr($saleSum);die;
			$sale = $saleSum['totalsale']; 
			$refund = $refundData[$kiosk_Id]['todayProductRefund'];
			$total_sale = $sale + $refund;
			$cardP = $cardPayment[$kiosk_Id]['totalsale'];
			$cashP = $cashPayment[$kiosk_Id]['totalsale'];
			
			if(is_numeric($cardP)){
				$cardP = $currency.round($cardP,2);
			}
			
			if(is_numeric($cashP)){
				$cashP = $currency.round($cashP,2);
			}
			
			if($paymentMode == 'Card' && !empty($sale)){
				$refund = 0;
			}
			
			if(is_numeric($sale) && is_numeric($refund)){
				$netSale = $total_sale - $refund; //$sale
			}else{
				$netSale = $total_sale; //$sale
			}
			if(is_numeric($total_sale)){ // $sale
				$grandSale+=$total_sale;//$sale;
				$total_sale = $currency.round($total_sale,2);
			}
			if(is_numeric($refund)){
				$grandRefund+=$refund;
				$refund = $currency.round($refund,2);
			}
			if(is_numeric($netSale)){
				$grandNetSale+=$netSale;
				$netSale = $currency.round($netSale,2);
			}
			?>
			<tr>
				<td><?=$kiosks[$kiosk_Id];?></td>
				<td><?=$total_sale;?></td>
				<td><?=$refund;?></td>
				<td><?=$netSale?></td>
				<td style="color: blue;"><?=$cardP;?></td>
				<td style="color: brown;"><?=$cashP;?></td>
			</tr>
		<?php } ?>
		<tr>
			<td><strong>Grand Total</strong></td>
			<td><?=$currency.round($grandSale,2);?></td>
			<td><?=$currency.round($grandRefund,2);?></td>
			<td><?=$currency.round($grandNetSale,2);?></td>
		</tr>
	</table>
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
        jQuery("#kioskid").val("");
        jQuery("#cash_id").val("");
        jQuery("#card_id").val("");
        jQuery("#refunded_radio").val("");
        jQuery("#multiple_id").val("");
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
