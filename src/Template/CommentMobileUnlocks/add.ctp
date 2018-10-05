<div class="commentMobileUnlocks form">
<?php echo $this->Form->create('CommentMobileUnlock'); ?>
	<fieldset>
		<legend><?php echo __('Add Comment for Mobile Unlock'); ?></legend>
		<h3>Customer: <?php echo $this->Html->link($mobileUnlocks[$mobile_unlock_id], array('controller'=>'mobile_unlocks','action' => 'edit', $mobile_unlock_id)); ?></h3>
	<?php		
		echo $this->Form->input('mobile_unlock_id',array('type' => 'hidden','value' => $mobile_unlock_id));
		echo $this->Form->input('brief_history');		
		echo $this->Form->input('status',array('type' => 'hidden','value' => 1));
	?>
	</fieldset>
<?php
echo $this->Form->submit('Submit',array('name'=>'submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php #echo $this->Html->link(__('List Comment Mobile Unlocks'), array('action' => 'index')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->element('unlock_navigation'); ?></li>
	</ul>
</div>
