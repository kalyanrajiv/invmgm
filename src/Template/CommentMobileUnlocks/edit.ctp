<div class="commentMobileUnlocks form">
<?php echo $this->Form->create('CommentMobileUnlock'); ?>
	<fieldset>
		<legend><?php echo __('Edit Comment Mobile Unlock'); ?></legend>
	<?php
		echo $this->Form->input('id',array('type' => 'hidden'));
		echo $this->Form->input('mobile_unlock_id',array('type' => 'hidden'));
		echo $this->Form->input('brief_history');
		echo $this->Form->input('status',array('type' => 'hidden'));
	?>
	</fieldset>
<?php
echo $this->Form->submit('Submit');
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('CommentMobileUnlock.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('CommentMobileUnlock.id'))); ?></li>
		<li><?php #echo $this->Html->link(__('List Comment Mobile Unlocks'), array('action' => 'index')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->element('unlock_navigation'); ?></li>
	</ul>
</div>
