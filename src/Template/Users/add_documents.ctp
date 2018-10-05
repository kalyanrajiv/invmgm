<div class="users form">
<?php #pr($this->request['data']['User']['username']=='admin');
echo $this->Form->create('User', array('type' => 'file')); ?>
	<fieldset>
		<legend><?php echo __('Add Documents'); ?></legend><br/>	
	<?php
		echo $this->Form->input('Image.0.attachment', array('type' => 'file', 'label' => 'Document 1'));
		echo $this->Form->input('Image.0.model', array('type' => 'hidden', 'value' => 'User'));
		echo $this->Form->input('Image.1.attachment', array('type' => 'file', 'label' => 'Document 2'));
		echo $this->Form->input('Image.1.model', array('type' => 'hidden', 'value' => 'User'));
		echo $this->Form->input('Image.2.attachment', array('type' => 'file', 'label' => 'Document 3'));
		echo $this->Form->input('Image.2.model', array('type' => 'hidden', 'value' => 'User'));
		echo $this->Form->input('Image.3.attachment', array('type' => 'file', 'label' => 'Document 4'));
		echo $this->Form->input('Image.3.model', array('type' => 'hidden', 'value' => 'User'));
		echo $this->Form->input('Image.4.attachment', array('type' => 'file', 'label' => 'Document 5'));
		echo $this->Form->input('Image.4.model', array('type' => 'hidden', 'value' => 'User'));
	?>
	</fieldset>
<?php
echo $this->Form->submit('Submit');
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
