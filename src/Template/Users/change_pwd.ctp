<div class="users form">
<?php
   // print_r($data);
   //echo $id;die;
    echo $this->Form->create('User'); ?>
	<fieldset>
		<legend><?php echo __('Change Password'); ?></legend>
	<?php
	    echo $this->Form->input('old_password',array('type' => 'password'));
            echo $this->Form->input('password');
            echo $this->Form->input('confirm_password', array('type' => 'password','label' => 'Confirm Password'));
	    echo $this->Form->input('id',array('type' => 'hidden','value' => $id));
	?>
	</fieldset>
<?php
echo $this->Form->submit('Submit');
echo $this->Form->end(); ?>
</div>