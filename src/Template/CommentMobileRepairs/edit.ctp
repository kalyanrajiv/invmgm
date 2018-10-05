<div class="commentMobileRepairs form">
<?php echo $this->Form->create('CommentMobileRepair'); ?>
	<fieldset>
		<legend><?php echo __('Edit Comment Mobile Repair'); ?></legend>
	<?php
		echo $this->Form->input('id',['type' => 'hidden']);
		echo $this->Form->input('mobile_repair_id',array('type' => 'hidden'));
		echo $this->Form->input('brief_history');
		echo $this->Form->input('status',array('type' => 'hidden'));
	?>
	</fieldset>
	
	
<?php echo $this->Form->submit('Submit',['name'=>'submit']);
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
