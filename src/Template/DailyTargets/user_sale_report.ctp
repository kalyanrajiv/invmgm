<?php $currency = "";//Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
//pr($this->request->query);
 
	$username = $usernme = $month = $mnth = '';
	//$username = $usernme = '';
	if($this->request->query){
		if(is_array($this->request->query['month']) && array_key_exists('month',$this->request->query['month'])){
		 $mnth = $this->request->query['month']['month'];
		}
		if(isset($this->request->query['username']) && !empty($this->request->query['username'])){
			$usernme = $this->request->query['username'];
		}
	}
?>
 
<div class="kiosks index">
	<h2>User Sale Report</h2>
	<?php echo $this->Form->create('UserSearch',array('url'=>array('controller'=>'daily_targets','action'=>'search_user_sale_report'),'type'=>'get'));?>
	<fieldset style="padding: 0px;">
		<legend>Search</legend>
		<table style="width: 50%;">
		<tr>
			<td><div id='remote'><?php echo $this->Form->input('username',array('placeholder' => 'Enter Username','value' => $usernme,'class' => 'typeahead'))?></div></td>
			 <td>
				<?php #echo $this->Form->input('month',array('options'=>$monthOptions,'default'=>$month))?>
				<input type = "text" id='UserSearchMonth' readonly='readonly' name="month[month]" placeholder = "Year-month" style = "width:100px;height: 25px;margin-top: 40px;" value='<?php echo $mnth;?>' />
				
			</td> 
			
				<?php ///echo $this->Form->input('month',array('options'=>$monthOptions,'default'=>$month))?>
				
			
			<td><?php
            echo $this->Form->submit("Submit",array('name'=>'submit','style' => "margin-top: 27px;"));
            echo $this->Form->end();?></td>
		</tr>
	</table>
	</fieldset>
	
	<table>
		<tr>
			<th>Username</th>
			
			<th>Target</th>
			<th>Achieved</th>
			<th>Accessory Sale</th>
			<th>Blk Mobile Sale</th>
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
		$totalBulkMobileSale = 0.00;
		
		//pr($users);die;
		if(array_key_exists('0',$userTargetData) && $userTargetData[0]['user_id']>0){
			foreach($userTargetData as $key => $dailyTarget){
				//pr($dailyTarget);die;
				$user_id = $dailyTarget['user_id'];
				if(isset($this->request->query['username']) && !empty($this->request->query['username'])){
					$username = $this->request->query['username'];
				}else{
					if(array_key_exists($user_id,$users)){
						$username = $users[$user_id];
					}else{
						$username = "--";
					}
				}
				if(isset($this->request->query['month'])){
					$month = $this->request->query['month']['month'];
				}
				//$this->Html->link($mobileRepairPrice->id, ['controller' => 'Brands', 'action' => 'edit', $mobileRepairPrice->id]) ;
				
				//$user_sale_detail_rel = $this->Html->link(array('controller' => 'daily_targets', 'action' => 'user_sale_detail'));
				//$user_sale_detail_url = $user_sale_detail_rel."?username=".$username."&month=".$month;
				//pr($dailyTarget);
				$totalBulkMobileSale +=$dailyTarget['mobile_blk_sale'];
				$totalTarget+=$dailyTarget['sumtarget'];
				$totalTargetAchieved+=$dailyTarget['sumtargetachieved'];
				$totalAccessorySale+=$dailyTarget['sumaccessale'];
				$totalUnlockSale+=$dailyTarget['sumunlocksale'];
				$totalRepairSale+=$dailyTarget['sumrepairsale'];
				$totalMobileSale+=$dailyTarget['summobilesale'];
				$totalGainLoss+=$dailyTarget['sumgainloss'];
                if($dailyTarget['sumgainloss']<0){
                    $dailyTarget_new['sumgainloss'] = (-1)*$dailyTarget['sumgainloss'];
                    $dailyTarget_new['sumgainloss'] = '-'.$CURRENCY_TYPE.$dailyTarget_new['sumgainloss'];
                }else{
                    $dailyTarget_new['sumgainloss'] = $CURRENCY_TYPE.$dailyTarget['sumgainloss'];
                }
				//continue
			?>
			<tr>
				<td><?php echo $username;?></td>
				
				<td><?php echo $CURRENCY_TYPE.$dailyTarget['sumtarget'];?></td>
				<td><?php echo $CURRENCY_TYPE.$dailyTarget['sumtargetachieved'];?></td>
				<td><?php echo $CURRENCY_TYPE.$dailyTarget['sumaccessale'];?></td>
				<td><?php echo $CURRENCY_TYPE.$dailyTarget['mobile_blk_sale'];?></td>
				<td><?php echo $CURRENCY_TYPE.$dailyTarget['sumunlocksale'];?></td>
				<td><?php echo $CURRENCY_TYPE.$dailyTarget['sumrepairsale'];?></td>
				<td><?php echo $CURRENCY_TYPE.$dailyTarget['summobilesale'];?></td>
				<td><?php echo $dailyTarget_new['sumgainloss'];?></td>
				<?php if($username != '--'){?>
					<td><a href="user_sale_detail?username=<?php echo $username; ?>&month=<?php echo $month;?>">view</a></td>
				<?php } ?>
				
			</tr>
		<?php }
		}	?>
        <?php
            //pr($totalGainLoss);die;
            if($totalGainLoss<0){
                $totalGainLoss_new = (-1)*$totalGainLoss;
                $totalGainLoss_new = '-'.$CURRENCY_TYPE.$totalGainLoss_new;
            }else{
                $totalGainLoss_new = $CURRENCY_TYPE.$totalGainLoss;
            }
            //pr($totalGainLoss_new);die;
        ?>
		<tr>
			<td><strong>Total</strong></td>
			<td><?php echo $CURRENCY_TYPE.$totalTarget;?></td>
			<td><?php echo $CURRENCY_TYPE.$totalTargetAchieved;?></td>
			<td><?php echo $CURRENCY_TYPE.$totalAccessorySale;?></td>
			<td><?php echo $CURRENCY_TYPE.$totalBulkMobileSale;?></td>
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