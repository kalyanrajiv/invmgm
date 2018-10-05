<div class="userAttendances view">
	<h2><?php
			$username =  $users['username'] ;
			$currentMonth = date('F');
			$lastmonth = Date('F', strtotime($currentMonth . " last month"));
			echo "User Attendance of last Month:$lastmonth(User:$username [ID: $userID ])";
		?></h2>
<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>S.no</th>
		<th>Kiosk Name</th>
		<th>Working days</th>
	 	<th>Working   of Hours  </th>
		<th>Break time</th>
		<th>Net time</th>
		<th>Logged in Time</th>
		<th>Dayoff Time</th>
		</thead>
	<tbody> 
		 <?php $i = 1;
		 $workingHours = 0;
		$day = 0;
		$end_hour = $end_min = 0;
		$end_total_hour = $end_total_min =  0;
		//pr($kiosks);die;
		foreach($last_month as $key => $snglast_month){
			$kioskId = $snglast_month['kiosk_id'];
			//echo $kioskId;die;
			if(array_key_exists($kioskId,$kiosks)){
				$Kioskname = $kiosks[$kioskId];
			}else{
				$Kioskname = "Warehouse";
			}
			$days = $snglast_month['days'];
			$hours = $snglast_month['Hours'];
			$login_time = $snglast_month['login_time'];
			$day_off = $snglast_month['dayoff'];
			$day = $day + 1;
		 ?>
 		<tr><td><?php echo $i++;?></td>
		<td><?php echo $Kioskname;?></td>
		<td><?php  echo date('jS M, Y',strtotime($days));?></td>
		<td><?php echo $hours;
			$final_val = array();
					if($hours >0){
						$hour_minu = explode(":",$hours);
						list($h,$m) = $hour_minu;
						$end_total_hour = $end_total_hour + $h;
						$end_total_min = $end_total_min + $m;
					}
			?></td>
		
			<?php $lunch_time = ""; ?>
			<?php if($hours>=4){
						$lunch_time = "30 minutes";
						$date = date($hours);
						$time = strtotime($date);
						$time = $time - (30 * 60);
						$date = date("H:i:s", $time);
						//$hours = $hours - .30;
					}else{
						$date = $hours;
					}
					if($date>0){
						$final_user_hrs = explode(":",$date);
						list($h1,$m1) = $final_user_hrs;
						$end_hour = $end_hour + $h1;
						$end_min = $end_min + $m1;
					}
			?>
			<td><?php echo $lunch_time  ?></td>
			<td><?php echo $date ?></td>
			
			<td><?php echo $login_time; ?></td>
			<td><?php echo $day_off;?></td>
			<td><?php  ?></td>
	</tr>	  
	<?php } ?>
 
	<tr>
		<?php
		$end_val = "";
		if(!empty($end_hour) && !empty($end_min)){
			$hours1 = intval($end_min / 60);  // integer division
			$mins1 = $end_min % 60;           // modulo
			$end_hour = $end_hour + $hours1;
			$end_val = $end_hour.":".$mins1;
		}
	?>
	<td> <b>Total</b></td>
	<td></td>
	<td><?php echo "days:\t". $day ;?></td>
	
	<td>Hrs:<?php
				$total_val = "";
				if(!empty($end_total_hour) && !empty($end_total_min)){
					$hours1 = intval($end_total_min / 60);  // integer division
					$mins1 = $end_total_min % 60;           // modulo
					$end_total_hour = $end_total_hour + $hours1;
					echo $total_val = $end_total_hour.":".$mins1;
				}else{
					echo $end_total_hour;
				}
				 
				?>
	</td><td></td>
	<td><?php echo "Net Hrs:\t" .$end_val;?></td></tr>
	<tr> 	<td></td>
	</tr>
	
	 
	 
	</tbody>
	</table>
 
			 
		 </div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List User Attendances'), array('action' => 'index'),array('escape' => false,'style'=>"width: 124px;")); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'),array('escape' => false,'style'=>"width: 124px;")); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'),array('escape' => false,'style'=>"width: 124px;")); ?> </li>
	</ul>
</div>
