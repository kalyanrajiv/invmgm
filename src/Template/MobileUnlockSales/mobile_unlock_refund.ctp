<div class="mobileRepairLogs index">
	<h2><?php #pr($mobileUnlockRefund['MobileUnlockSale']);
	$amount = $mobileUnlockRefund['amount'];
	$kiosk_id = $mobileUnlockRefund['kiosk_id'];
	$mobileUnlockId = $mobileUnlockRefund['mobile_unlock_id'];
	$soldBy = $mobileUnlockRefund['sold_by'];
	$soldOn = $mobileUnlockRefund['sold_on'];
	$userId = $this->request->Session()->read('Auth.User.id');
	$refundOn = date('Y-m-d h:i:s A');
	echo __('Mobile Unlock Refund'); ?></h2>
	<?php echo $this->Form->create('MobileUnlockRefund');?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo ('Unlock id'); ?></th>
			<th><?php echo ('Kiosk Name'); ?></th>			
			<th><?php echo ('Sold By'); ?></th>
			<th><?php echo ('Sold On'); ?></th>
			<th><?php echo ('Sale Price'); ?></th>			
			<th><?php echo ('Refund Amount'); ?></th>
			<th><?php echo ('Refund Remarks'); ?></th>						
	</tr>
	</thead>
	<tbody>	
	<tr>		
		<td><?php echo $mobileUnlockRefund['mobile_unlock_id']?></td>
		<td><?php echo $kiosks[$mobileUnlockRefund['kiosk_id']]; ?></td>		
		<td><?php echo $users[$mobileUnlockRefund['sold_by']]; ?></td>
		<td><?php echo date('d-m-y g:i A',strtotime($mobileUnlockRefund['sold_on'])); ?></td>
		<td><?php echo $mobileUnlockRefund['amount']; ?></td>		
		<td>
			<?php echo $this->Form->input(null,array(
							'type' => 'text',
							'name' => 'MobileUnlockSale[refunded_amount]',
							'label' => false
							)
						      );
			
				echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileUnlockSale[refund_status]',
							'value' => 1,
							'label' => false
							)
						      );
				
				echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileUnlockSale[kiosk_id]',
							'value' => $kiosk_id,
							'label' => false
							)
						      );
				
				echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileUnlockSale[mobile_unlock_id]',
							'value' => $mobileUnlockId,
							'label' => false
							)
						      );
				
				echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileUnlockSale[sold_by]',
							'value' => $soldBy,
							'label' => false
							)
						      );
				
				echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileUnlockSale[sold_on]',
							'value' => $soldOn,
							'label' => false
							)
						      );
				
				echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileUnlockSale[refund_by]',
							'label' => false,
							'value' => $userId
							)
						      );
				
				echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileUnlockSale[refund_on]',
							'label' => false,
							'value' => $refundOn
							)
						      );
			
				echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => 'MobileUnlockSale[amount]',
							'label' => false,
							'value' => $amount
							)
						      );
			?>
		</td>
		<td><?php echo $this->Form->input(null,array(
							'type' => 'text',
							'name' => 'MobileUnlockSale[refund_remarks]',
							'value' => '',
							'label'=> false
							)
						  );?>
		</td>
	</tr>
	</tbody>
	</table>
	<?php
	echo $this->Form->submit('Submit',array('name'=>'submit1'));
	echo $this->Form->end();?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Mobile Repair Log'), array('action' => 'add')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('input[name = "submit1"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>
