<div class="mobileRepairLogs index">
	<h2><?php #pr($mobileRepairLogs);
	echo __('Mobile Repair Logs'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo ('id'); ?></th>
			<th><?php echo ('Kiosk Name'); ?></th>
			<th><?php echo ('User'); ?></th>
			<th><?php echo ('Repair Id'); ?></th>			
			<th><?php echo ('Repair Status'); ?></th>			
			<th><?php echo ('Last Updated On'); ?></th>			
	</tr>
	</thead>
	<tbody>
	<?php $counter = 0;
	foreach ($mobileRepairLogs as $mobileRepairLog):
	$counter++;
   //pr($mobileRepairLog); 
		if($mobileRepairLog['status']==1){
			$currentStatus = "Refunded";
		}else{
           //echo $currentStatus = $mobileRepairLog['repair_status'];
			 $currentStatus = $repairStatus[$mobileRepairLog['repair_status']];
		}
	//pr($users);die;
	?>
	<tr>
		<td><?php echo $counter; ?>&nbsp;</td>
		<td><?php echo $kiosks[$mobileRepairLog['kiosk_id']]; ?></td>
		<td><?php echo $users[$mobileRepairLog['user_id']]; ?></td>		
		<td><?php echo h($mobileRepairLog['mobile_repair_id']); ?>&nbsp;</td>
		<td><?php echo $currentStatus; ?>&nbsp;</td>
		<td><?php echo date('M jS, Y g:i A',strtotime($mobileRepairLog['created']));//$this->Time->format('M jS, Y g:i A', $mobileRepairLog['created'],null,null); ?>&nbsp;</td>		
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Mobile Repair Log'), array('action' => 'add')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Repairs'), array('controller' => 'mobile_repairs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Repair'), array('controller' => 'mobile_repairs', 'action' => 'add')); ?> </li>
	</ul>
</div>
