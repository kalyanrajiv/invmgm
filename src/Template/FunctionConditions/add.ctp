<div class="functioncondition form">
 <?php echo $this->Form->create('FunctionCondition'); ?>
	<fieldset>
		<legend><?php echo __('Add Function Test'); ?></legend>
	<?php
	//pr($statusOptions);
		echo $this->Form->input('function_condition');
		echo $this->Ck->input('description');
		echo $this->Form->input('status',array('options' => $statusOptions));
	?>
	</fieldset>
   <?php echo $this->Form->submit('Submit'); ?>
<?php echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Function Tests'), array('action' => 'index')); ?></li>
	</ul>
</div>
 <script>
     // CKEDITOR.replace( 'data[FuctionCondition][description]');
</script>  