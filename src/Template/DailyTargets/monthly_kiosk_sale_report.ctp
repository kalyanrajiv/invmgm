<?php //$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$selectedKiosks = array();
	if(isset($this->request->query['kiosk'])){
		$selectedKiosks = $this->request->query['kiosk'];
	}
	if(isset($this->request->query['start'])){
		$month = $this->request->query['start'];
	}else{
		$month = date("01-M-Y");
	}
	if(!isset($monthEndDay)){
		$monthEndDay = "";
	}else{
		$monthEndDay = date("d-M-Y",strtotime($monthEndDay));
	}
?>   
<div class="kiosks index">
	<h2>Monthly Kiosk Sale Report</h2>
	<?php echo $this->Form->create('KioskSearch',array('url'=>array('controller'=>'daily_targets','action'=>'search_monthly_kiosk_sale_report'),'type'=>'get'));?>
		<fieldset style="padding: 0px;">
		<legend>Search</legend>
			<table style="width: 50%;">
				<tr>
					<td>
				<input type = "text" id='KioskSearchMonth' readonly='readonly' name="start" placeholder = "Year-month" style = "width:100px;height: 25px;"value='<?php echo $month;?>' />
				<input type = "text" id='KioskSearchMonth1' readonly='readonly' name="end" placeholder = "Year-month" style = "width:100px;height: 25px;"value='<?php echo $monthEndDay;?>' />
					</td>
					<td><?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'multiple'=>true,'default'=>$selectedKiosks))?></td>
					<td><?php
					echo $this->Form->submit('Search',array('name'=>'submit'));
					echo $this->Form->end();?></td>
				</tr>
			</table>
		</fieldset>
	<table>
		<tr>
			<th>Kiosk Name</th>
			<th>Monthly Target</th>
			<th>Target achieved</th>
			<th>Accessory Sale</th>
			<th>Blk Mobile Sale</th>
			<th>Unlock Sale</th>
			<th>Repair Sale</th>
			<th>Phone Sale</th>
			<th>Gain/Loss</th>
		</tr>
		<?php //pr($userTargetData);
		$totalMonthlyTarget = 0;
		$totalTargetAchieved = 0;
		$totalAccessorySale = 0;
		$totalUnlockSale = 0;
		$totalRepairSale = 0;
		$totalMobileSale = 0;
		$totalGainLoss = 0;
		$totalBulkMobileSale = 0;
		$kioskTargetData = json_encode($kioskTargetData);
		$kioskTargetData = json_decode($kioskTargetData);
		foreach($kioskTargetData as $key=>$dailyTarget){
			//pr($dailyTarget);die;
			if((int)$dailyTarget[0]->kiosk_id){
				
			$monthlyTarget = $dailyTarget[0]->monthly_target;
			$targetAchieved = $dailyTarget[0]->monthly_sale-$dailyTarget[0]->monthly_refund;
			$accessorySale = $dailyTarget[0]->monthly_product_sale-$dailyTarget[0]->monthly_product_refund;
			$mobileBlkSale = $dailyTarget[0]->monthly_mobile_blk_sale-$dailyTarget[0]->monthly_mobile_blk_refund;
			$unlockSale = $dailyTarget[0]->monthly_mobile_unlock_sale-$dailyTarget[0]->monthly_mobile_unlock_refund;
			$repairSale = $dailyTarget[0]->monthly_mobile_repair_sale-$dailyTarget[0]->monthly_mobile_repair_refund;
			$mobileSale = $dailyTarget[0]->monthly_mobile_sale-$dailyTarget[0]->monthly_mobile_refund;
			$targetAchieved = $dailyTarget[0]->monthly_sale-$dailyTarget[0]->monthly_refund;
			$gainLoss = $targetAchieved-$monthlyTarget;
			
			
			
			$totalBulkMobileSale += $mobileBlkSale;
			$totalMonthlyTarget+=$monthlyTarget;
			$totalTargetAchieved+=$targetAchieved;
			$totalAccessorySale+=$accessorySale;
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
			<td><?php echo $kiosks[$dailyTarget[0]->kiosk_id];?></td>
			<td><?php echo $CURRENCY_TYPE.$monthlyTarget;?></td>
			<td><?php echo $CURRENCY_TYPE.$targetAchieved;?></td>
			<td><?php echo $CURRENCY_TYPE.$accessorySale;?></td>
			<td><?php echo $CURRENCY_TYPE.$mobileBlkSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$unlockSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$repairSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$mobileSale;?></td>
			<td><?php echo $gainLoss_new;?></td>
		</tr>
		<?php }
		} ?>
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
			<td><strong><?php echo $CURRENCY_TYPE.$totalMonthlyTarget;?></strong></td>
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
		jQuery( "#KioskSearchMonth" ).datepicker({ dateFormat: "d-M-yy" });
		jQuery( "#KioskSearchMonth1" ).datepicker({ dateFormat: "d-M-yy" });
	});
</script>