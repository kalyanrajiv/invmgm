<div class="functioncondition form">
 <?php echo $this->Form->create(); ?>
	<fieldset>
		<legend><?php echo __('Add Faulty Condition'); ?></legend>
	<?php
	 	echo $this->Form->input('faulty_condition');
		echo $this->Ck->input('description');
		echo $this->Form->input('status',array('options' => $statusOptions));
	?>
	</fieldset>
<?php echo $this->Form->submit(__('Submit'),array('name'=>'submit')); ?>
<?php echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Faulty Conditions'), array('action' => 'index')); ?></li>
	</ul>
</div>
 <script>
    //  CKEDITOR.replace( 'data[FuctionCondition][description]');
</script>  