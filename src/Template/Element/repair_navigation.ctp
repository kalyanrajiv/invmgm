<?php if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){ ?>
    <table>
	<tr>
		<th>For Technician</th>
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Phone Received'), array('controller' => 'mobile_repairs', 'action' => 'received_by_technician'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	
	<tr>
	    <td><?php echo $this->Html->link(__('Dispatched to Kiosk'), array('controller' => 'mobile_repairs', 'action' => 'dispatched_to_kiosk'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	
	<tr>
		<th>For Kiosk Users</th>
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('All'), array('controller' => 'mobile_repairs', 'action' => 'all'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Booked'), array('controller' => 'mobile_repairs', 'action' => 'booked'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Rebooked'), array('controller' => 'mobile_repairs', 'action' => 'rebooked'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Dispatched <br/>to Technician'), array('controller' => 'mobile_repairs', 'action' => 'dispatched'),array('style' => 'width: 110px;','escape' => false) ); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Received Repaired'), array('controller' => 'mobile_repairs', 'action' => 'received_repair'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Received Unrepaired'), array('controller' => 'mobile_repairs', 'action' => 'received_unrepaired'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Delivered'), array('controller' => 'mobile_repairs', 'action' => 'delivered'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>		
</table>
<?php }else{ ?>
    <table>
	
	<tr>
		<th>For Kiosk Users</th>
	</tr>
	<tr>
		<td><?php echo $this->Html->link(__('All'), array('controller' => 'mobile_repairs', 'action' => 'all'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Booked'), array('controller' => 'mobile_repairs', 'action' => 'booked'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Rebooked'), array('controller' => 'mobile_repairs', 'action' => 'rebooked'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Dispatched <br /> to Technician'), array('controller' => 'mobile_repairs', 'action' => 'dispatched'),array('style' => 'width: 110px;','escape' => false)); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Received Repaired'), array('controller' => 'mobile_repairs', 'action' => 'received_repair'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Received Unrepaired'), array('controller' => 'mobile_repairs', 'action' => 'received_unrepaired'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Delivered'), array('controller' => 'mobile_repairs', 'action' => 'delivered'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	
	<tr>
		<th>For Technician</th>
	</tr>
	<tr>
	    <td><?php echo $this->Html->link(__('Phone Received'), array('controller' => 'mobile_repairs', 'action' => 'received_by_technician'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
	
	<tr>
	    <td><?php echo $this->Html->link(__('Dispatched to Kiosk'), array('controller' => 'mobile_repairs', 'action' => 'dispatched_to_kiosk'),array('style' => 'width: 110px;')); ?></td>	    
	</tr>
				
</table>
<?php } ?>