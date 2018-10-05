<div class="mobileRepairLogs index">
	<h2><?php
		$kiosk_id = $mobileRepairRefund['kiosk_id'];
		$mobileRepairId = $mobileRepairRefund['mobile_repair_id'];
		$soldBy = $mobileRepairRefund['sold_by'];
		$soldOn = $mobileRepairRefund['sold_on'];
		$amount = $mobileRepairRefund['amount'];		     
		$userId = $this->request->Session()->read('Auth.User.id');
		$refundOn = date('Y-m-d h:i:s A');
	echo __('Mobile Repair Refund'); ?></h2>
	<?php echo $this->Form->create('MobileRepairRefund');?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo ('Repair id'); ?></th>
			<th><?php echo ('Kiosk Name'); ?></th>
			<th><?php echo ('Sold By'); ?></th>
			<th><?php echo ('Sold On'); ?></th>
			<th><?php echo ('Sale Price'); ?></th>			
			<th><?php echo ('Refund Amount'); ?></th>
			<th><?php echo ('Refund Remarks'); ?></th>						
	</tr>
	</thead>
	<tbody>
	<?php 	
	$repairSaleId = $mobileRepairRefund['id'];?>
	<tr>
		<td><?php echo $mobileRepairRefund['mobile_repair_id']; ?></td>
		<td><?php echo $kiosks[$mobileRepairRefund['kiosk_id']]; ?></td>
		<td><?php echo $users[$mobileRepairRefund['sold_by']]; ?></td>
		<td><?php echo date('d-m-y g:i A',strtotime($mobileRepairRefund['sold_on']));  ?></td>
		<td><?php echo $mobileRepairRefund['amount']; ?></td>
		<td><?php echo $this->Form->input(null,array(
							'type' => 'text',
							'name' => 'MobileRepairSale[refunded_amount]',							
							'label'=> false
							)
						  );
			
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileRepairSale[refund_status]',
							'value' => 1,
							'label'=> false
							)
						  );
			
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileRepairSale[kiosk_id]',
							'value' => $kiosk_id,
							'label'=> false
							)
						  );
			
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileRepairSale[mobile_repair_id]',
							'value' => $mobileRepairId,
							'label'=> false
							)
						  );
			
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileRepairSale[sold_by]',
							'value' => $soldBy,
							'label'=> false
							)
						  );
			
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileRepairSale[sold_on]',
							'value' => $soldOn,
							'label'=> false
							)
						  );
			
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileRepairSale[amount]',
							'value' => $amount,
							'label'=> false
							)
						  );
			
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileRepairSale[refund_by]',
							'value' => $userId,
							'label'=> false
							)
						  );
			
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileRepairSale[refund_on]',
							'value' => $refundOn,
							'label'=> false
							)
						  );?>
		</td>						
		<td><?php echo $this->Form->input(null,array(
							'type' => 'text',
							'name' => 'MobileRepairSale[refund_remarks]',
							'value' => '',
							'label'=> false
							)
						  );?></td>
	</tr>
	</tbody>
	</table>
	<?php
	echo $this->Form->submit('submit',['name'=>'submit1']);
	echo $this->Form->end();?>
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
<script>
	$('input[name = "submit1"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>
