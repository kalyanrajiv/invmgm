<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$username = '';
	if(isset($this->request->query['kiosk'])){
		$kiosk = $this->request->query['kiosk'];
	}
	if(isset($this->request->query['month'])){
		$month = $this->request->query['month']['month'];
	}
?>   
<div class="kiosks index">
	<h2>Kiosk Sale Report</h2>
	<?php echo $this->Form->create('KioskSearch',array('url'=>array('controller'=>'daily_targets','action'=>'search_kiosk_sale_report'),'type'=>'get'));?>
	<fieldset style="padding: 0px;">
		<legend>Search</legend>
		<table style="width: 50%;">
		<tr>
			<td><?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'default'=>$kiosk))?></td>
			<td>
				<?php #echo $this->Form->input('month',array('options'=>$monthOptions,'default'=>$month))?>
				<input type = "text" id='KioskSearchMonth' readonly='readonly' name="month[month]" placeholder = "Year-month" style = "width:100px;height: 25px;margin-top: 19px;"value='<?php echo $month;?>' />
			</td>
			<td><?php
			echo $this->Form->submit('Search',array('name'=>'submit','style'=>"margin-top: 4px;"));
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
			<th>User Name</th>
			<th>Gain/Loss</th>
		</tr>
		<?php //pr($userTargetData);
        //pr($users);
		$totalDayTarget = 0;
		$totalTargetAchieved = 0;
		$totalAccessorySale = 0;
		$totalUnlockSale = 0;
		$totalRepairSale = 0;
		$totalMobileSale = 0;
		$totalGainLoss = 0;
		$totalBulkMobileSale = 0;
		foreach($kioskTargetData as $key=>$dailyTarget){
			if((int)$dailyTarget->user_id){
                if(array_key_exists($dailyTarget->user_id,$users)){
                    $userName = $users[$dailyTarget->user_id];
                }else{
                    $userName = '';
                }
				
			}else{
				$userName = '';
			}
			//showing net sale by subtracting the refund from each value
			$accessorySale = $dailyTarget->product_sale - $dailyTarget->product_refund;
			//accessory sale coming from receipt table, so already has net amount
			$bulkMobileSale = $dailyTarget->mobile_blk_sale - $dailyTarget->mobile_blk_refund;
			$unlockSale = $dailyTarget->mobile_unlock_sale-$dailyTarget->mobile_unlock_refund;
			$repairSale = $dailyTarget->mobile_repair_sale-$dailyTarget->mobile_repair_refund;
			$mobileSale = $dailyTarget->mobile_sale-$dailyTarget->mobile_refund;
			$targetAchieved = $dailyTarget->total_sale-$dailyTarget->total_refund;
			$gainLoss = $targetAchieved-$dailyTarget->target;
			
			$totalDayTarget+=$dailyTarget->target;
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
			<td><?php echo date("d-m-Y",strtotime($dailyTarget->target_date));?></td>
			<td><?php echo $CURRENCY_TYPE.$dailyTarget->target;?></td>
			<td><?php echo $CURRENCY_TYPE.$targetAchieved;?></td>
			<td><?php echo $CURRENCY_TYPE.$accessorySale;?></td>
			<td><?php echo $CURRENCY_TYPE.$bulkMobileSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$unlockSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$repairSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$mobileSale;?></td>
			<td><?php echo $userName;?></td>
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
			<td>&nbsp;</td>
			<td><strong><?php echo $totalGainLoss_new;?></strong></td>
		</tr>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php echo $this->element('target_navigation');?>
	<!--<ul>
		<li><?php echo $this->Html->link(__('User Sale Report'), array('controller' => 'daily_targets', 'action' => 'user_sale_report'));?></li>
		<li><?php echo $this->Html->link(__('Kiosk sale Report'), array('controller' => 'daily_targets', 'action' => 'kiosk_sale_report'));?></li>
		<li><?php echo $this->Html->link(__('Monthly Kiosk Sale Report'), array('controller' => 'daily_targets', 'action' => 'monthly_kiosk_sale_report'));?></li>
		<li><?php echo $this->Html->link(__('Daily All Kiosk Sale'), array('controller' => 'daily_targets', 'action' => 'all'));?></li>
	</ul>-->
</div>
<script>
	//reference: https://jqueryui.com/resources/demos/datepicker/date-formats.html
	function reset_search(){
		jQuery( "#KioskSearchMonth" ).val("");
	}
	jQuery(function() {
		jQuery( "#KioskSearchMonth" ).datepicker({ dateFormat: "yy-mm" });
	});
</script>