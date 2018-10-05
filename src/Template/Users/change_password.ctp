<div class="users form">
<?php
    echo $this->Form->create('User'); ?>
	<fieldset>
		<legend><?php echo __('Change Password'); ?></legend>
	<?php            
            echo $this->Form->input('password');
            echo $this->Form->password('confirm_password');
	    echo $this->Form->hidden('id',array('value' => $this->request->data['id']));
	?>
	</fieldset>
<?php
echo $this->Form->submit('Submit');
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('controller' => 'groups', 'action' => 'add')); ?> </li>
	</ul>
</div>
