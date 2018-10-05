<div class="users form">
<?php #pr($this->request['data']['User']['username']=='admin');
echo $this->Form->create('User', array('type' => 'file')); ?>
	<fieldset>
		<legend><?php echo __('Would you like to add documents to this User profile?'); ?></legend><br/>	
	<?php
		echo '<div class="inline_labels">';
		$options = array('Y'=>'Yes','N'=>'No');
		$attributes = array('legend'=>false,'value'=>'Y');
		echo $this->Form->radio('redirect', $options, $attributes);
		echo '</div>';
		
		
	?>
		
	</fieldset>
<?php
echo $this->Form->submit('submit');
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('User.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('User.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('controller' => 'groups', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Posts'), array('controller' => 'posts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Post'), array('controller' => 'posts', 'action' => 'add')); ?> </li>
	</ul>
</div>
