<div class="commentMobileRepairs form">
<?php echo $this->Form->create('CommentMobileRepair'); ?>
	<fieldset>
		<legend><?php echo __('Add Comment for Mobile Repair'); ?></legend>
		<h3>Customer: <?php echo $this->Html->link($mobileRepairs[$mobile_repair_id], array('controller'=>'mobile_repairs','action' => 'edit', $mobile_repair_id)); ?></h3>
	<?php		
		echo $this->Form->input('mobile_repair_id',array('type' => 'hidden','value' => $mobile_repair_id));
		echo $this->Form->input('brief_history');		
		echo $this->Form->input('status',array('type' => 'hidden','value' => 1));
	?>
	</fieldset>
<?php 
echo $this->Form->submit('Submit',['name'=>'submit']);
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php #echo $this->Html->link(__('List Comment Mobile Repair'), array('action' => 'index')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Repars'), array('controller' => 'mobile_repairs', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New Mobile Unlock'), array('controller' => 'mobile_repairs', 'action' => 'add')); ?> </li>	
		<li><?php echo $this->element('repair_navigation'); ?></li>				
	</ul>
</div>
