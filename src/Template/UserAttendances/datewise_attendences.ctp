<div class="userAttendances index">
	 
	<?php
	 $month   ='';
	 if(!empty($this->request->query['date'])){
		$month = $this->request->query['date'];
	}
	 
	?>
	<fieldset>
			<legend>Search</legend>
			<div>
				<form action='<?php echo $this->request->webroot;?>user-attendances/date_search' method = 'get'>
					<div class="search_div">
					<fieldset>
							<legend>Users</legend>
							<table>
								<tr>
									<td style="width: 250px;"><?php if(!empty($start)){?>
									 <input type = "text" id='datepicker1' readonly='readonly' name="date" placeholder = "date" style = "width:200px;height: 40px;"
									 value='<?php  echo date('jS M, Y', strtotime($start))?>' />
										
									<?php }else{?>
									<input type = "text" id='datepicker1' readonly='readonly' name="date" placeholder = "date" style = "width:200px;height: 20px;margin-left: 15px;"value='<?php echo $month;?>' />
									<?php } ?>
									</td>
									 
									<td style="width: 80px;"><input type="submit" name='submit' value='Search' style="height: 35px;width: 78px;" /></td>
									<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;width:85px ;height: 35px;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
								</tr>
							</table>
					</fieldset>
			</div>
					</form>
					<div>**30 minutes will be automatically excluded for total hours equivalent or more than 4 hours</div>
	</fieldset>
	 
	
 
	</h2>
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th>User Name</th>
				<th>Kiosk Name</th>
				<th>Day</th>
				<th>Hours Worked</th>
				<th>Break time</th>
				<th>Net time</th>
				<th>Time In</th>
				<th>Day Off Time</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$end_min = $end_hour = $total_hours =   0;
			$end_total_hour = $end_total_min =  0;
			 $day = 0;
			foreach($UserAttendances as $key => $sngcurrent_month){
				$username = $sngcurrent_month['user_id'];
				$kioskname = $sngcurrent_month['kiosk_id'];
				 
				if(empty($kioskname)){
				   $kioskname ="10000";
				}
			   	$days = $sngcurrent_month['days'];
				$hours = $sngcurrent_month['Hours'];
				$total_hours += $hours;
				 
				$login_time = $sngcurrent_month['login_time'];
				$day_off = $sngcurrent_month['dayoff']; 
				if(empty($day_off)){
					 $day_off = "--";
				} 
				$day = $day + 1;
			 
			?>
			<tr>
				<td><?php if(array_key_exists($username,$users)){
                    echo $users[$username];
                    }else{
                        echo "--";
                    }?></td>
			<td><?php if($kioskname == 10000){ echo "warehouse" ; }else{ echo $kiosks[$kioskname]; } ?></td>
				<td><?php echo date('d/m/y',strtotime($days));?></td>
				<td><?php echo $hours;
					if($hours >0){
						$hour_minu = explode(":",$hours);
						if(is_array($hour_minu)){
							list($h,$m) = $hour_minu;
						}else{
							$m = $h = 0;
						}
						
						$end_total_hour = $end_total_hour + $h;
						$end_total_min = $end_total_min + $m;
					}
			?></td>
				<?php $lunch_time = ""; ?>
				<?php if($hours >=4){
					$lunch_time = "30 minutes";
					$date = date($hours);
					$time = strtotime($date);
					$time = $time - (30 * 60);
					$date = date("H:i:s", $time);
				}else{
					$date = $hours;
				}
				if($date>0){
                   // pr($date);
					$final_user_hrs = explode(":",$date);
                   // pr($final_user_hrs);
					list($h1,$m1) = $final_user_hrs;
					$end_hour = $end_hour + $h1;
					$end_min = $end_min + $m1;
				}
				?>
				<td><?php echo $lunch_time  ?></td>
				<td><?php echo $date ?></td>
				<td><?php echo $login_time; ?></td>
				<td><?php echo $day_off;?></td>
			 
		</tr>
			
	<?php  }
	?>
			<tr>
				<td><b>Total</b></td>
				<td></td>
				<td></td>
				
				<td>Hrs:<?php
				$total_val = "";
				if(!empty($end_total_hour) && !empty($end_total_min)){
					$hours1 = intval($end_total_min / 60);   
					$mins1 = $end_total_min % 60;            
					$end_total_hour = $end_total_hour + $hours1;
					echo $total_val = $end_total_hour.":".$mins1;
				}else{
					echo $end_total_hour;
				}
				 
				?>
	</td><td></td>
				<td >Net Hrs:<?php
				
					$end_val = "";
					if(!empty($end_hour) && !empty($end_min)){
						$hours1 = intval($end_min / 60);  
						$mins1 = $end_min % 60;            
						$end_hour = $end_hour + $hours1;
						$end_val = $end_hour.":".$mins1;
					}
					echo $end_val;
					
				?></td>
			</tr>
		</tbody>
	</table>
	 
	 
	 
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		
		<li><?php echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
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
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		 
		 
	}
	 
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
	});
</script>