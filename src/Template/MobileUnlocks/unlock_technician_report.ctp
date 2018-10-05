<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="mobileUnlock index">
	<?php
	
	/*if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
		
	}else{
		$value = '';*/
	
	//}?>
		<?php
	$start_date = '';
	$end_date = '';
	$kiosk_id = '';
	$service_center = '';
	if($this->request->data){
		$start_date = $this->request->data['start_date'];
		$end_date = $this->request->data['end_date'];
		$kiosk_id = $this->request->data['kiosk'];
		$service_center = $this->request->data['service_center'];
	}?>
	<?php
		$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo __('Unlock Technician Report')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<?php
	echo $this->Form->create('unlockTechnicianReport');?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<td><?php echo $this->Form->input('user',array('options' => $users, 'empty' => '--All--'));?></td>
		<td><?php echo $this->Form->input('kiosk',array('options' => $kiosks, 'empty' => '--All--'));?></td>
		<td><?php echo $this->Form->input('service_center',array('options' => $serviceCenters, 'empty' => '--All--', 'label'=>'Unlock Center'));?></td>
	</tr>
	<tr>
		<td><?php echo $this->Form->input('null',array('id' => 'datepicker1',
							       'readonly' => 'readonly',
							       'name' => 'start_date',
							       'placeholder' => "From Date",
							       'label' => false,
							       'value' => $start_date,
							       'style' => "width: 150px;margin-top: 12px;"
							       )
						  );?></td>
		<td><?php echo $this->Form->input('null',array('id' => 'datepicker2',
			     'readonly' => 'readonly',
			     'name' => 'end_date',
			     'placeholder' => "To Date",
			     'label' => false,
			     'value' => $end_date,
			     'style' => "width: 200px;margin-top: 12px;"
			     )
		);?></td>
		<td><input type = 'submit' value = 'Search' name = 'submit'/>
		<input type='button' name='reset' value='Reset Search' onClick='reset_search();' style="width: 90px;border-radius: 4px;margin-top: 18px;"/><td>
		
			<?php echo $this->Form->end();?>
		
		 
	</tr>
	<tr>
		<td colspan="3">
			<table>
				<tr>
					<th>Username</th>
					<th>Total Unlocks</th>
					<th>Success</th>
					<th>Failure</th>
					<th>Sale</th>
					<th>Refund</th>
					<th>Net</th>
					<th>Cost</th>
					<th>Profit</th>
					<th>Action</th>
				</tr>
				<?php 
					$total_unlock = $total_success = $total_failure = $total_cost = $total_sale = $total_refund = $grand_net = $total_profit = 0; 
					$unlockReportRel = $this->Html->link(array('controller' => 'MobileUnlocks', 'action' => 'unlock_report_detail'));
				foreach($userArray as $userId => $unlockData){
                                    if($userId == 1){continue;}//admin
						$queryStr = "unlock_report_detail?user_id=".$userId."&kiosk_id=".$kiosk_id."&service_center=".$service_center."&start_date=".$start_date."&end_date=".$end_date;
						$unlockReportUrl = $queryStr;
						$countSuccess = 0;
						$countFailure = 0;
						foreach($unlockData as $key => $unlockLog){
							if($unlockLog['unlock_status'] == DISPATCHED_2_KIOSK_UNLOCKED){
								$countSuccess++;
							}elseif($unlockLog['unlock_status'] == DISPATCHED_2_KIOSK_UNPROCESSED){
								$countFailure++;
							}
							elseif($unlockLog['unlock_status'] == UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK){
								$countSuccess++;
							}
							elseif($unlockLog['unlock_status'] == UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK){
								$countFailure++;
							}
						}
					
						if(!empty($sum_sale[$userId]['sumSale'])){
							$sale = $sum_sale[$userId]['sumSale'];
							
						}else{
							$sale = '0';
						}
					
						if(!empty($refund_sale[$userId]['refundSale'])){
							$refund = $refund_sale[$userId]['refundSale'];
							
							if($refund<0){
								$refund = -$refund;
							}
							
							if((int)$sale && $sale>0){
								$net = $sale - $refund;
							}else{
								$net = '0';
							}
						}else{
							$refund = '0';
							$net = '0';
						}
						if((int)$sale && $sale>0){
							$net = $sale - $refund;
							if(array_key_exists($userId,$unlockCost)){
								$profit = $sale - $unlockCost[$userId] - $refund;
							}else{
								$profit = '0';
							}
							
						}else{
							$net = '0';
							$profit = '0';
						}
				?>
					<tr>
						<td><?php
							if(array_key_exists($userId,$userName)){
								echo $userName[$userId];
							}else{
								echo "--";
							}
						?></td>
						<td><?php echo count($unlockData);?></td>
						<td><?php echo $countSuccess;?></td>
						<td><?php echo $countFailure;?></td>
						<td><?php echo $CURRENCY_TYPE.$sale;?></td>
						<td><?php echo $CURRENCY_TYPE.$refund;?></td>
						<td><?php echo $CURRENCY_TYPE.$net;?></td>
						<td><?php
							if(array_key_exists($userId,$unlockCost)){
									echo $CURRENCY_TYPE.$unlockCost[$userId];
									}else{
										echo "--";
								}
						?></td>
						<td><?php echo $CURRENCY_TYPE.$profit;?></td>
						<td><?php echo "<a href='$unlockReportUrl'>view</a>";?>
					</tr>
					<?php
						$total_unlock+= count($unlockData);
						$total_success+= $countSuccess;
						$total_failure+= $countFailure;
						if(array_key_exists($userId,$unlockCost)){
							$total_cost+= $unlockCost[$userId];
						}
						else{
							$total_cost+= "0";
						}
						$total_sale+= $sale;
						$total_refund+= $refund;
						$grand_net+= $net;
						$total_profit+= $profit;
				} ?>
					<tr>
						<td><strong>Total:</strong></td>
						<td><?=$total_unlock;?></td>
						<td><?=$total_success;?></td>
						<td><?=$total_failure;?></td>
						<td><?php echo $CURRENCY_TYPE.$total_sale;?></td>
						<td><?php echo $CURRENCY_TYPE.$total_refund;?></td>
						<td><?php echo $CURRENCY_TYPE.$grand_net;?></td>
						<td><?php echo $CURRENCY_TYPE.$total_cost;?></td>
						<td><?php echo $CURRENCY_TYPE.$total_profit;?></td>
					</tr>
				 
				
				
			</table>
		</td>
	</tr>
	</thead>
	<tbody>
	</tbody>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Mobile Unlock'), array('action' => 'index')); ?></li>
		<li><?php #echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>			
		<li><?php #echo $this->element('repair_navigation'); ?></li>		
	</ul>
</div>

<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#user").val("");
		jQuery("#kiosk").val("");
		jQuery("#service-center").val("");
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