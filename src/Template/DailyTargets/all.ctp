<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$username = '';
	if(isset($this->request->query['kiosk'])){
		$kiosk = $this->request->query['kiosk'];
	}
	
	if(isset($this->request->data['end_date'])){
		$end_date = $this->request->data['end_date'];
		$end_date = date('d-M-Y',strtotime($end_date));
	}
	if(isset($this->request->data['start_date'])){
		$start_date = $this->request->data['start_date'];
		$start_date = date('d-M-Y',strtotime($start_date));
	}
	
	if(isset($end_date) && !empty($end_date)){
		$end_date = date('d-M-Y',strtotime($end_date));
	}
	if(isset($start_date) && !empty($start_date)){
		$start_date = date('d-M-Y',strtotime($start_date));
	}
?>   
<div class="kiosks index">
	<h2>Kiosk Sale Report</h2>
	<?php echo $this->Form->create('KioskSearch',array('url'=>array('controller'=>'daily-targets','action'=>'all'),'type'=>'post'));?>
	<fieldset style="padding: 0px;">
		<legend>Search</legend>
		<table style="width: 50%;">
		<tr>
			<td>
				<?php #echo $this->Form->input('month',array('options'=>$monthOptions,'default'=>$month))?>
				<input type = "text" id='start_date' readonly='readonly' name="start_date" placeholder = "Year-month" style = "width:100px;height: 25px;"value='<?php echo $start_date;?>' />
				<input type = "text" id='end_date' readonly='readonly' name="end_date" placeholder = "Year-month" style = "width:100px;height: 25px;"value='<?php echo $end_date;?>' />
			</td>
			<td><?php
			echo $this->Form->submit('submit',array('name'=>'submit','style'=>"margin-top: -10px;"));
			echo $this->Form->end();?></td>
		</tr>
	</table>
	</fieldset>
	
	<table>
		<tr>
			<th>Date</th>
			<th>Day Target</th>
			<th>Target achieved</th>
			<th>Accessory Sale</th>
			<th>Blk Mobile Sale</th>
			<th>Unlock Sale</th>
			<th>Repair Sale</th>
			<th>Phone Sale</th>
		
			<th>Gain/Loss</th>
		</tr>
		<?php //pr($userTargetData);
		$totalDayTarget = 0;
		$totalTargetAchieved = 0;
		$totalAccessorySale = 0;
		$totalUnlockSale = 0;
		$totalRepairSale = 0;
		$totalMobileSale = 0;
		$totalGainLoss = 0;
		$totalBulkMobileSale = 0;
		foreach($kioskTargetData as $key=>$dailyTarget){
			//showing net sale by subtracting the refund from each value
			$accessorySale = $dailyTarget['product_sale'] - $dailyTarget['product_refund'];
			//accessory sale coming from receipt table, so already has net amount
			$bulkMobileSale = $dailyTarget['mobile_blk_sale'] - $dailyTarget['mobile_blk_refund'];
			$unlockSale = $dailyTarget['mobile_unlock_sale']-$dailyTarget['mobile_unlock_refund'];
			$repairSale = $dailyTarget['mobile_repair_sale']-$dailyTarget['mobile_repair_refund'];
			$mobileSale = $dailyTarget['mobile_sale']-$dailyTarget['mobile_refund'];
			$targetAchieved = $dailyTarget['total_sale']-$dailyTarget['total_refund'];;
			$gainLoss = $targetAchieved-$dailyTarget['target'];
			
			$totalDayTarget+=$dailyTarget['target'];
			$totalTargetAchieved+=$targetAchieved;
			$totalAccessorySale+=$accessorySale;
			$totalBulkMobileSale+=$bulkMobileSale;
			$totalUnlockSale+=$unlockSale;
			$totalRepairSale+=$repairSale;
			$totalMobileSale+=$mobileSale;
			$totalGainLoss+=$gainLoss;
            if($gainLoss<0){
                $gainLoss_new = (-1)*$gainLoss;
                $gainLoss_new = '-'.$CURRENCY_TYPE.$gainLoss_new;
            }else{
                $gainLoss_new = $CURRENCY_TYPE.$gainLoss;
            }
		?>
		<tr>
			<td><?php echo date("d-m-Y",strtotime($dailyTarget['target_date']));?></td>
			<td><?php echo $CURRENCY_TYPE.$dailyTarget['target'];?></td>
			<td><?php echo $CURRENCY_TYPE.$targetAchieved;?></td>
			<td><?php echo $CURRENCY_TYPE.$accessorySale;?></td>
			<td><?php echo $CURRENCY_TYPE.$bulkMobileSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$unlockSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$repairSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$mobileSale;?></td>
			<td><?php echo $gainLoss_new;?></td>
		</tr>
		<?php } ?>
        <?php
            if($totalGainLoss<0){
                $totalGainLoss_new = (-1)*$totalGainLoss;
                $totalGainLoss_new = '-'.$CURRENCY_TYPE.$totalGainLoss_new;
            }else{
                $totalGainLoss_new = $CURRENCY_TYPE.$totalGainLoss;
            }
        ?>
		<tr>
			<td><strong>Total</strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalDayTarget;?></strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalTargetAchieved;?></strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalAccessorySale;?></strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalBulkMobileSale;?></strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalUnlockSale;?></strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalRepairSale;?></strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalMobileSale;?></strong></td>
			<td><strong><?php echo $totalGainLoss_new;?></strong></td>
		</tr>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php echo $this->element('target_navigation');?>
	
</div>
<script>
	//reference: https://jqueryui.com/resources/demos/datepicker/date-formats.html
	function reset_search(){
		jQuery( "#KioskSearchMonth" ).val("");
	}
	jQuery(function() {
		jQuery( "#start_date" ).datepicker({ dateFormat: "d-M-yy" });
		jQuery( "#end_date" ).datepicker({ dateFormat: "d-M-yy" });
	});
</script>