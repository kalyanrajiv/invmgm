
  <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
?>
<div class="users view large-9 medium-8 columns content">
    <div class="related">
        <h2>
		<?php
			//echo $query."<br/>";
			$username =  $users['username'] ;  
			echo "User Attendance (User:$username [ID: $userID ])";
             $rootURL = Router::url('/', true);
		?>
		<a href="<?php echo $rootURL;?>user-attendances/export/<?=$userID;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a>
		 
	</h2>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('S.no') ?></th>
                <th scope="col"><?= __('Kiosk Id') ?></th>
                <th scope="col"><?= __('Day') ?></th>
                <th scope="col"><?= __('Hours Worked') ?></th>
                <th scope="col"><?= __('Break time') ?></th>
                <th scope="col"><?= __('Net time') ?></th>
                <th scope="col"><?= __('Time In') ?></th>
                <th scope="col"><?= __('Day Off Time') ?></th>
                
                
            </tr>
            <?php
            $i = 1;
            $day = 0;
            $end_hour = $end_min = 0;
            $end_total_hour = $end_total_min =  0;
            foreach($current_month as $key => $sngcurrent_month){
                $kioskname = $sngcurrent_month['Kiosk_id'];
                $days = $sngcurrent_month['days'];
                $hours = $sngcurrent_month['Hours'];
                $login_time = $sngcurrent_month['login_time'];
                $day_off = $sngcurrent_month['dayoff'];
                //$workingHours = $workingHours + $hours;
                $day = $day + 1;
            ?>
            <tr>
                <td><?php echo $i++;?></td>
                <td><?php if(array_key_exists($kioskname,$kiosks)){
                    echo $kiosks[$kioskname];
                }
                    else{
                        echo "Warehouse";
                    }?></td>
                <td><?php  echo date('jS M, Y',strtotime($days));  ?></td>
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
                <?php if($hours >=4){
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
                <td><?php //echo $this->Html->link('Device History', array('controller' => 'devices', 'action' => 'device_wrt_attendance', strtotime($days)));?></td>
                <td></td>
            </tr>	
            <?php }?>
            <?php
                    $end_val = "";
                     $end_min;
                    if(!empty($end_hour) && !empty($end_min)){
                        $hours1 = intval($end_min / 60);  // integer division
                        $mins1 = $end_min % 60;           // modulo
                        $end_hour = $end_hour + $hours1;
                        $end_val = $end_hour.":".$mins1;
                    }
                ?>
                <tr>
                    <td><b>Total</b></td>
                    <td></td>
                    <td><b>Days:</b><?php echo $day;?></td>
                    <td><b>Hrs:</b><?php
                        $total_val = "";
                        if(!empty($end_total_hour) && !empty($end_total_min)){
                            $hours1 = intval($end_total_min / 60);  // integer division
                            $mins1 = $end_total_min % 60;           // modulo
                            $end_total_hour = $end_total_hour + $hours1;
                            echo $total_val = $end_total_hour.":".$mins1;
                        }else{
                            echo $end_total_hour;
                        }
                         
                        ?></td></td><td></td><td><b>Net Hrs:</b><?php echo $end_val;?></td></td>
                </tr>
        </table>
    </div>    
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
        	    
		<li><?php echo $this->Html->link(__('List User Attendances'), array('action' => 'index'),array('style' => 'width: 124px;')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'),array('style' => 'width: 124px;')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'),array('style' => 'width: 124px;')); ?> </li>
	</ul>
</div>
