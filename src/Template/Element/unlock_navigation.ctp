<?php
	ob_start();  
?>

	<tr>
		<th>For Kiosk</th>
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('All'), array('controller' => 'mobile_unlocks', 'action' => 'all'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Physically booked'), array('controller' => 'mobile_unlocks', 'action' => 'booked'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Virtually Booked'), array('controller' => 'mobile_unlocks', 'action' => 'virtually_booked'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Request Sent <br/>to Center'), array('controller' => 'mobile_unlocks', 'action' => 'unlock_request_sent'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Dispatched to Center'), array('controller' => 'mobile_unlocks', 'action' => 'dispatched'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>	
	<tr>
	    <td><?php echo $this->Html->link(__('Unlocked'), array('controller' => 'mobile_unlocks', 'action' => 'unlocked'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Unlocking Failed'), array('controller' => 'mobile_unlocks', 'action' => 'unlocking_failed'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	</hr>
	<tr>
	    <td><?php echo $this->Html->link(__('Received Unlocked'), array('controller' => 'mobile_unlocks', 'action' => 'received_unlocked'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Received <br/> Unprocessed'), array('controller' => 'mobile_unlocks', 'action' => 'received_unprocessed'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Refund Raised'), array('controller' => 'mobile_unlocks', 'action' => 'refund_raised'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Delivered'), array('controller' => 'mobile_unlocks', 'action' => 'delivered'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	
<?php
	$forKiosk = ob_get_contents();
	ob_end_clean();
?>
<?php
	ob_start();  
?>	
	<tr>
		<th>For unlocking Center</th>
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Request Received'), array('controller' => 'mobile_unlocks', 'action' => 'unlock_request_received'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Phone Received'), array('controller' => 'mobile_unlocks', 'action' => 'phone_received'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	
	<tr>
	    <td><?php echo $this->Html->link(__('Unlock Processed'), array('controller' => 'mobile_unlocks', 'action' => 'unlock_processed'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Unlock Failed'), array('controller' => 'mobile_unlocks', 'action' => 'unlock_failed'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Dispatched'), array('controller' => 'mobile_unlocks', 'action' => 'dispatched_to_kiosk'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>	
<?php
	$forUnlocking = ob_get_contents();
	ob_end_clean();
?>
<table>
<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
	echo $forUnlocking;
	//echo $forKiosk;
 }
 if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
	echo $forKiosk;
	//echo $forUnlocking;
 }
?>
</table>