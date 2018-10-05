<div class="settings form">
<?php echo $this->Form->create('ScreenHint'); ?>
	<fieldset>
		<legend><?php echo __('Add Screen Hints'); ?></legend>
	<?php
		echo $this->Form->input('controller',array('options' => $controllersDropDown,'empty' => '(choose one)'));
		echo $this->Form->input('action');
        //echo $this->CK->input('hint');
        echo $this->Ck->input('description');
		#echo $this->Form->input('status');
	?>
	</fieldset>
<?php echo $this->Form->submit(__('Submit'),array('name'=>'submit')); ?>
<?php echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Screen Hints'), array('action' => 'index')); ?></li>
	</ul>
</div>
<script>
	// CKEDITOR.replace( 'description');
</script>