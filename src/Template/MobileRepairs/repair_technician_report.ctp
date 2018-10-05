<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="mobileRepairs index">
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
	}
	
	?>
	<?php
		$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo __('Repair Technician Report')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<?php
	echo $this->Form->create('RepairTechnicianReport');?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<td><?php echo $this->Form->input('user',array('options' => $users, 'empty' => '--All--'));?></td>
		<td><?php echo $this->Form->input('kiosk',array('options' => $kiosks, 'empty' => '--All--'));?></td>
		<td><?php echo $this->Form->input('service_center',array('options' => $serviceCenters, 'empty' => '--All--'));?></td>
	</tr>
	<tr>
		<td><?php echo $this->Form->input('null',array('id' => 'datepicker1',
							       'readonly' => 'readonly',
							       'name' => 'start_date',
							       'placeholder' => "From Date",
							       'label' => false,
							       'value' => $start_date,
							       'style' => "width: 200px;margin-top: 12px;"
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
		<input type='button' name='reset' value='Reset Search'  onClick='reset_search();' style="width: 90px;border-radius: 4px;margin-top: 18px;"/></td>
		<?php echo $this->Form->end();?>
		
	</tr>
	<tr>
		<td colspan="3">
			<table>
				<tr>
					<th>Username</th>
					<th>Total Repairs</th>
					<th>Success</th>
					<th>Failure</th>
					<th>Sale</th>
					<th>Refund</th>
					<th>Net</th>
					<th>Dynamic Cost</th>
					<th>Fixed Cost</th>
					<th>Profit</th>
					<th>Action</th>
				</tr>
				<?php
				$total_cost_fixed =$total_repairs = $total_success = $total_failure = $total_cost = $total_sale = $total_refund = $grand_net = $total_profit = 0;
				$repairReportRel = $this->Html->link(array('controller' => 'mobile_repairs', 'action' => 'repair_report_detail'));
				//pr($repairCost);die;
				foreach($userArray as $userId => $repairData){
					$queryStr = "repair_report_detail?user_id=".$userId."&kiosk_id=".$kiosk_id."&service_center=".$service_center."&start_date=".$start_date."&end_date=".$end_date;
					$repairReportUrl = $queryStr;//$repairReportRel.
					$countSuccess = 0;
					$countFailure = 0;
					foreach($repairData as $key => $repairLog){
                        //pr($repairLog);die;
						if($repairLog['repair_status'] == DISPATCHED_2_KIOSK_REPAIRED){
							$countSuccess++;
						}elseif($repairLog['repair_status'] == DISPATCHED_2_KIOSK_UNREPAIRED){
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
					}else{
						$refund = '0';
					}
					
					if((int)$sale && $sale > 0 && array_key_exists($userId, $repairFixedCost)){
						$net = $sale - $refund;
						$profit = $net - $repairFixedCost[$userId];
					}else{
						$net = '0';
						$profit = '0';
					}
					
				?>
					<tr>
						<td><?php echo $userName[$userId];?></td>
						<td><?php echo count($repairData);?></td>
						<td><?php echo $countSuccess;?></td>
						<td><?php echo $countFailure;?></td>						
						<td><?php echo $CURRENCY_TYPE.$sale;?></td>
						<td><?php echo $CURRENCY_TYPE.$refund;?></td>
						<td><?php echo $CURRENCY_TYPE.$net;?></td>
						<td><?php
							if(array_key_exists($userId,$repairCost)){
								echo $CURRENCY_TYPE.$repairCost[$userId];
							}
						?></td><?php 
						if(array_key_exists($userId, $repairFixedCost)){
							$total_cost_fixed += $repairFixedCost[$userId]; ?>
							<td><?php echo $CURRENCY_TYPE.$repairFixedCost[$userId];?></td>
						<?php }else{ ?>
						<td>--</td>
						<?php }?>
						<td><?php echo $CURRENCY_TYPE.$profit;?></td>
						<td><?php
						//echo $repairReportUrl;
						echo "<a href='$repairReportUrl'>view</a>";?>
						
					</tr>
				<?php
						$total_repairs+= count($repairData);
						$total_success+= $countSuccess;
						$total_failure+= $countFailure;
						if(array_key_exists($userId, $repairCost)){
							$total_cost+= $repairCost[$userId];
						}
						
						$total_sale+= $sale;
						$total_refund+= $refund;
						$grand_net+= $net;
						$total_profit+= $profit;
				} ?>
					<tr>
						<td><strong>Total:</strong></td>
						<td><?=$total_repairs;?></td>
						<td><?=$total_success;?></td>
						<td><?=$total_failure;?></td>
						<td><?php echo $CURRENCY_TYPE.$total_sale;?></td>
						<td><?php echo $CURRENCY_TYPE.$total_refund;?></td>
						<td><?php echo $CURRENCY_TYPE.$grand_net;?></td>
						<td><?php echo $CURRENCY_TYPE.$total_cost;?></td>
						<td><?php echo $total_cost_fixed;?></td>
						
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
		<li><?php echo $this->Html->link(__('List Mobile Repair'), array('action' => 'index')); ?></li>
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