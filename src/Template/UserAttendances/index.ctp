<?php
/**
  * @var \App\View\AppView $this
  */
if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}
	if(!empty($this->request->query['month']['month'])){
		$mnth = $this->request->query['month']['month'];
		 $month = Date('F', strtotime($mnth)); 
	}else{
		$month = '';
		$mnth = '';
	}
?>

<div class="users index large-9 medium-8 columns content">
    
    <?php echo $this->Form->create('UserSearch',array('url'=>array('controller'=>'user-attendances','action'=>'search'),'type'=>'get'));?>
    <fieldset style="padding: 0px;">
		<legend>Search</legend>
		<table style="width: 50%;">
		<tr>
			<td><div id='remote'>
			<input name='search_kw' id ='search_kw' class='typeahead' type="text" value = '<?= $value;?>' placeholder="username" style = "width:350px;margin-top: 14px;"autofocus /></div></td>
			 <td>
				<?php #echo $this->Form->input('month',array('options'=>$monthOptions,'default'=>$month))?>
				<input type = "text" id='UserSearchMonth' readonly='readonly' name="month[month]" placeholder = "Year-month" style = "width:100px;height: 25px;margin-top: 14px;" value='<?php echo $mnth;?>' />
				
			</td>
			 <td><?php //echo $this->Form->end(array('Search'));
                echo $this->Form->submit("submit",array('name'=>'submit'));
                 echo $this->Form->end();
             ?></td>
			 <td><input type='button' name='reset' value='Reset Search' style='padding:4px 8px;color:#333;border:1px solid #bbb;border-radius:4px;margin-top:14px;height: 35px;' onClick='reset_search();'/></td>
			
			
			
		</tr>
	</table>
	</fieldset>
     <?php
	echo "<table>";
	echo "<tr>";
	$currentMonth = date('F');
	$lastmonth = Date('F', strtotime($currentMonth . " last month"));
	if(empty($this->request->query['month']['month'])){
		echo "<td>";	$date =  date("F"); echo "<h2>"; echo __('User Attendances of '.$currentMonth)  ;
		echo "</h2>";echo "</td>";
		echo "<td style='margin-right:5px;font-color'>"; echo $this->Html->link(__( 'View Attendances for last Month'),array( 'action' => 'viewlastmonth'), array('style' => 'font-size:15px;margin-right: -96px;')); echo "</td>";
		?>
		<?php
			echo "<td style='margin-right:5px;font-color'>"; $this->Html->link(__( 'View Attendances for last Month'),array( 'action' => 'view_last_month'), array('style' => 'font-size:15px;margin-right: -120px;')); echo "</td>"; ?> 
		<?php }else{
					$yrdata= strtotime($mnth);
					$mnthname =  date('M', $yrdata);
					$date =  date("F");
				    echo "<td>";echo "<h2>";echo __('User Attendances of '. $month)  ;
					echo "</h2>";echo "</td>"; ?>
					<?php echo "<td style='margin-right:5px;font-color'>"; echo $this->Html->link(__( 'Current Month : '.$date),array( 'action' => 'index'), array('style' => 'font-size:15px;margin-right: -90px;')); echo "</td>";  	}
	echo "</tr>";
		echo "</table>";
	?>
    <span><i>**30 minutes will be automatically excluded for total hours equivalent or more than 4 hours</i></span>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
				<th> <?php echo __('Id'); ?>  </th>
				<th><?php echo __('User Name'); ?></th>
				<th><?php echo __('Hours'); ?></th>
				<th><?php echo __('Days '); ?></th> 
				<th class="actions"><?php echo __('Actions'); ?></th>
			</tr>
        </thead>
        <tbody>
            <?php
            $final_val = array();
            foreach ($userArr as $sngUser):
                    $hours = $mins = 0;
					$userId = $sngUser['id'];
					$userName = $sngUser['username'];
					$hours = $sngUser['hours'];
                    if($hours>0){
						$hour_minu = explode(":",$hours);
						list($h,$m) = $hour_minu;
						$hours = intval($m / 60);  // integer division
						$mins = $m % 60;           // modulo
						$total_hour = $hours+$h;
						$final_val[$userId]  = $total_hour.":".$mins;
						//pr($final_val[$userId]);
					}
					$days = $sngUser['Days'];
            ?>
            <tr><td><?php echo $userId;?></td>
			<td><?php echo $userName;?></td>
			<td><?php if(array_key_exists($userId,$final_val)){
					echo $final_val[$userId];
				}else{
				echo $final_val[$userId] = "0";
			}?></td>
			<td><?php echo $days;?></td>
			<td class="actions">
			<?php
			if(!empty($this->request->query['month']['month'])){
			   echo $this->Html->link(__('View'), array('action' => 'search_last_month', $userId,$mnth));
			}else{
			   echo $this->Html->link(__('View'), array('action' => 'view', $userId));
			}
			
			 ?>
			</td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
	<?php //pr($this->request->query);
    if(empty($this->request->query) || array_key_exists('page',$this->request->query)){ ?>
    <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	<?php } ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New User Attendance'), array('action' => 'add'),array('escape' => false,'style'=>"width: 124px;")); ?></li>
		<li><?php echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index'),array('escape' => false,'style'=>"width: 124px;")); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add'),array('escape' => false,'style'=>"width: 124px;")); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'),array('escape' => false,'style'=>"width: 124px;")); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'),array('escape' => false,'style'=>"width: 124px;")); ?> </li>
	</ul>
</div>
<script>
    
	function reset_search(){
        jQuery("#search_kw").val("");
		jQuery( "#UserSearchMonth" ).val("");
	}
 	jQuery(function() {
 		jQuery( "#UserSearchMonth" ).datepicker({
		 dateFormat: "yy-mm",
		 minDate:"0-199",
		 maxDate: "0+1",
		 });
 		
 	});
 
	 

</script>
<script>
 var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/users/kiosk_users/?search=%QUERY",
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