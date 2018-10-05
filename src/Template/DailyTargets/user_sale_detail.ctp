<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$username = '';
	if(isset($this->request->query['username'])){
		$username = $this->request->query['username'];
	}
	if(is_array($this->request->query['month']) && array_key_exists('month',$this->request->query['month'])){
		$mnth = $this->request->query['month']['month'];
	}
?>   
<div class="kiosks index">
	<h2>User Sale Detail (<?php if(array_key_exists($userId,$users)){echo $users[$userId];}?>)</h2>
	<table>
		<tr>
			<th>Date</th>
			<th>Username</th>
			<th>Kiosk</th>
			<th>Target</th>
			<th>Achieved</th>
			<th>Accessory Sale</th>
			<th>Bulk Mobile Sale</th>
			<th>Unlock Sale</th>
			<th>Repair Sale</th>
			<th>Phone Sale</th>
			<th>Gain/Loss</th>
		</tr>
		<?php //pr($userTargetData);
		$totalTarget = 0.00;
		$totalTargetAchieved = 0.00;
		$totalAccessorySale = 0.00;
		$totalUnlockSale = 0.00;
		$totalRepairSale = 0.00;
		$totalMobileSale = 0.00;
		$totalGainLoss = 0.00;
		$finalBlkSale = 0.00;
		//pr($userTargetData);die;
		foreach($userTargetData as $key => $dailyTarget){
			$mobileUnlockSale = (float)$dailyTarget['mobile_unlock_sale'];
			$mobileUnlockRefund = (float)$dailyTarget['mobile_unlock_refund'];
			$mobileRepairSale = (float)$dailyTarget['mobile_repair_sale'];
			$mobileRepairRefund = (float)$dailyTarget['mobile_repair_refund'];
			$mobile_sale = (float)$dailyTarget['mobile_sale'];
			$mobile_refund = (float)$dailyTarget['mobile_refund'];
			$totalSale = (float)$dailyTarget['total_sale'];
			$totalRefund = (float)$dailyTarget['total_refund'];
			//showing net sale by subtracting the refund from each value
			$accessorySale = (float)$dailyTarget['product_sale'];
			$accessoryRefund = (float)$dailyTarget['product_refund'];
			$bulk_m_sale = (float)$dailyTarget['mobile_blk_sale'];
			$bulk_m_refund = (float)$dailyTarget['mobile_blk_refund'];
			$total_blk_sale = $bulk_m_sale - $bulk_m_refund;
			
			$accessoryTotalSale = $accessorySale - $accessoryRefund;
			//accessory sale coming from receipt table, so already has net amount
			$unlockSale = $mobileUnlockSale-$mobileUnlockRefund;
			$repairSale = $mobileRepairSale-$mobileRepairRefund;
			$mobileSale = $mobile_sale-$mobile_refund;
			$targetAchieved = $totalSale-$totalRefund;
			$gainLoss = $targetAchieved-$dailyTarget['target'];
            if($gainLoss<0){
                $gain_loss_new = (-1)*($gainLoss);
                $gain_loss_new = '-'.$CURRENCY_TYPE.$gain_loss_new;
            }else{
                $gain_loss_new = $CURRENCY_TYPE.$gainLoss;
            }
            //pr($gain_loss_new);die;
			$totalTarget+=(float)$dailyTarget['target'];
			$totalTargetAchieved+=$targetAchieved;
			$totalAccessorySale+=$accessoryTotalSale;
			$totalUnlockSale+=$unlockSale;
			$totalRepairSale+=$repairSale;
			$totalMobileSale+=$mobileSale;
			$totalGainLoss+=$gainLoss;
			$finalBlkSale+=$total_blk_sale;
			//continue
		?>
		<tr>
			<td><?php echo date("d-m-Y",strtotime($dailyTarget['target_date']));?></td>
			<td><?php echo !empty($dailyTarget['user_id']) ? $users[$dailyTarget['user_id']]:'---';?></td>
			<td><?php echo $kiosks[$dailyTarget['kiosk_id']];?></td>
			<td><?php echo $CURRENCY_TYPE.$dailyTarget['target'];?></td>
			<td><?php echo $CURRENCY_TYPE.$targetAchieved;?></td>
			<td><?php echo $CURRENCY_TYPE.$accessoryTotalSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$total_blk_sale;?></td>
			<td><?php echo $CURRENCY_TYPE.$unlockSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$repairSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$mobileSale;?></td>
			<td><?php echo $gain_loss_new;?></td>
		</tr>
		<?php } ?>
        <?php
        if($totalGainLoss<0){
            $totalGainLoss_new = (-1)*($totalGainLoss);
            $totalGainLoss_new = '-'.$CURRENCY_TYPE.$totalGainLoss_new;
        }else{
            $totalGainLoss_new = $CURRENCY_TYPE.$totalGainLoss;
        }
        ?>
		<tr>
			<td colspan="3" style="text-align: center;"><strong>Total</strong></td>
			<td><?php echo $CURRENCY_TYPE.$totalTarget;?></td>
			<td><?php echo $CURRENCY_TYPE.$totalTargetAchieved;?></td>
			<td><?php echo $CURRENCY_TYPE.$totalAccessorySale;?></td>
			<td><?php echo $CURRENCY_TYPE.$finalBlkSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$totalUnlockSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$totalRepairSale;?></td>
			<td><?php echo $CURRENCY_TYPE.$totalMobileSale;?></td>
			<td><?php echo $totalGainLoss_new;?></td>
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
 var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/users/kiosk_users?search=%QUERY",
    wildcard: "%QUERY"
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'username',
  display: 'username',
  source: user_dataset,
  limit:120,
  minlength:3,
  classNames: {
    input: 'Typeahead-input',
    hint: 'Typeahead-hint',
    selectable: 'Typeahead-selectable'
  },
  highlight: true,
  hint:true,
  templates: {
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{username}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>
<script>
	//reference: https://jqueryui.com/resources/demos/datepicker/date-formats.html
	function reset_search(){
		jQuery( "#UserSearchMonth" ).val("");
	}
	jQuery(function() {
		jQuery( "#UserSearchMonth" ).datepicker({ dateFormat: "yy-mm" });
	});
</script>