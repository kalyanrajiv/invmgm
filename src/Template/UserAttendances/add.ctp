<div class="userAttendances form">
   <?php //pr($UserAttendances); ?>
      <?= $this->Form->create($UserAttendances) ?>
 
	<fieldset>
		<legend><?php echo __('Add User Attendance'); ?></legend>
	<?php
		echo $this->Form->input('kiosk_id', ['options' => $kiosks]);
		echo $this->Form->input('user_id', ['options' => $users]);
		echo $this->Form->input('logged_in');
        
       
		echo $this->Form->input('logged_out');
		echo $this->Form->input('session_ide');
		 echo $this->Form->input('status',array('options' => $activeOptions));
	?>
	</fieldset>
      <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
<?php //echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List User Attendances'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
