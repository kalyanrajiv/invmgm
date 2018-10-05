<div class="userAttendances index">
	 <?php
	$end_date ='';
	// $month = '';
	if(empty($month)){
        $month = date('Y-m');
	   $monthname = Date('F', strtotime($month)); 
	}else{
          $monthname = Date('F', strtotime($month)); 
    }
	 if(!empty($this->request->query['month'])){
		$month = $this->request->query['month'];
		 $monthname = Date('F', strtotime($month)); 
		
	}
	if(!empty($this->request->query['end_date'])){
		$end_date = $this->request->query['end_date'];
	}
	?>
	<fieldset>
			<legend>Search</legend>
			<div>
				<form action='<?php echo $this->request->webroot;?>user-attendances/kiosk-search' method = 'get'>
					<div class="search_div">
					<fieldset>
							<legend>Kiosk</legend>
							<table>
								<tr>
									<td style="width: 10px;">
									<?php
									// if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
										if(!empty($this->request->query['data']['user_attendances']['kiosk_id'])){
											 $kiosk_id = $this->request->query['data']['user_attendances']['kiosk_id'];
												echo $this->Form->input(null, array(
															  'options' => $kiosks,
															  'label' => false,
															  'div' => false,
															  'id'=> 'kioskid',
															  'value' => $kiosk_id,
															   'style' => 'width:300px;height: 33px;',
															  'name' => 'data[user_attendances][kiosk_id]',
															  'style' => 'width:200px'
															  )
													   );     
											 }else{ 
											   echo $this->Form->input(null, array(
																'options' => $kiosks,
																'label' => false,
																'div' => false,
																'id'=> 'kioskid',
																'name' => 'data[user_attendances][kiosk_id]',
																// 'empty' => 'Select Kiosk',
																'value' =>'10000',
																'style' => 'width:200px;height:33px;'
																)
																);
											 }
									//	 
											 
										 
									 
                                       
                               ?></td>
							<td style="width: 10px;">	<?php //echo "<td>";
                                        echo $this->Form->input('null',array('id' => 'datepicker1',
                                               'readonly' => 'readonly',
                                               'name' => 'month',
                                               'placeholder' => "Year-month",
                                               'label' => false,
                                               'value' => $month,
                                               'style' => "width: 90px;margin-top: 1px;height: 17px;"
                                               )
                                         );
									 ?>
									<td style="width: 10px;"><input type="submit" name='submit' value='Search Kiosk' style="height:30px;margin-top: 8px;";/></td>
									<td><input type='button' name='reset' value='Reset' style = 'width:117px;height:32px;margin-top: 8px;' onClick='reset_search();'/></td> 
								</tr>
							</table>
					</fieldset>
			</div>
					</form>
	</fieldset>
	
	<h2><?php echo __('Kiosk Attendances'); ?> for <?php echo $monthname;//date('M, Y');?></h2>
	<br/><span><i>**30 minutes will automatically be deducted if employee works for more than 4 hours</i></span>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Kiosk Name</th>
		<th>Username</th>
		<th>Hours</th>
		<th>Break time</th>
		<th>Net time</th>
		<th>Time In</th>
		<th>Day Off Time</th>
		<th>Days</th>
		
	</tr>
	</thead>
	
	<tbody>
		<?php    //pr($UserAttendances);
		$workingHours = 0;
		$totaluserworkinghours = 0;
		foreach($UserAttendances as $key => $sngUser){
		  $date = "";
			$userId = $sngUser['user_id'];
			$kioskId = $sngUser['kiosk_id'];
			$hours = $sngUser['Hours'];
			if($hours > 0){	   
			   $workingHours = $workingHours + $hours;  
			}
			
			
			$login_time = $sngUser['login_time'];
			$day_off = $sngUser['dayoff']; 
			if(empty($day_off)){
				$day_off = "--";
			} 
			$days = $sngUser['days'];
		?>
		<td><?php if(array_key_exists($kioskId,$kiosks)){
			echo $kiosks[$kioskId];
		}else{
			echo "Warehouse";
		}?></td>
		<td><?php
		if(array_key_exists($userId,$users)){
			   echo ucfirst($users[$userId]);
		}else{
		  echo "--";
		}
		?></td>
		<td><?php echo $hours;?></td>
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
				?>
				<td><?php echo $lunch_time  ?></td>
				<td><?php echo $date ?></td>
		<td><?php echo $login_time; ?></td>
		<td><?php echo $day_off;?></td>
		<td><?php echo date('d/m/y',strtotime($days));?></td>
		</tr>
	<?php } ?>
	
	<table>
	<tbody>
	 <tr><td>
		  <b>User Name</b>
	 </td>
	 <td>
		  <b> Hours</b>
	 </td>
	 </tr>
	<?php
	$kisk_hr = array();
	
		foreach($userArr as $kioskid => $Users){
			  		foreach($Users as $key =>$value){
						 $final_val = "";
						//$users[$key];
						$hour_minu = explode(":",$value);
						list($h,$m) = $hour_minu;
						$hours = intval($m / 60);  // integer division
					    $mins = $m % 60;           // modulo
						$total_hour = $hours+$h;
						$kisk_hr[] = $final_val = $total_hour.":".$mins;
		?>
		<tr><td><?php
		if(array_key_exists($key,$users)){
			   echo ucfirst($users[$key]);
		}else{
		  echo "--";
		}
		?></td>
		<td><?php   echo $final_val;?></td>
		</tr>
	<?php }} ?>
	
	<tr>
	 <?php
	 $end_hour = $end_min ="";
			   foreach($kisk_hr as $key1 => $value1){
					$final_kiosk_hrs = explode(":",$value1);
					list($h1,$m1) = $final_kiosk_hrs;
					$end_hour = $end_hour + $h1;
					$end_min = $end_min + $m1;
			   }
			   $end_val = "";
			  
			   if(!empty($end_hour) && !empty($end_min)){
					$hours1 = intval($end_min / 60);  // integer division
					$mins1 = $end_min % 60;           // modulo
					$end_hour = $end_hour + $hours1;
					$end_val = $end_hour.":".$mins1;
			   }
	 ?>
			<td><b>Total Hours:</b></td><td><b><?php echo $end_val;?></b></td></td>
		</tr>	 
		<tbody>
	</table>
	
	</tbody>
	</tbody>
	</table>
	<p>
	 
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
</div> 
 <script>
	//reference: https://jqueryui.com/resources/demos/datepicker/date-formats.html
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
	}
	
	 jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "yy-mm" });
	});
</script>