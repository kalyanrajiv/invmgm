<div class="FunctionCondition form">
 <?php
   $description = $this->request->data['description'];
 echo $this->Form->create(); ?>
	<fieldset>
		<legend><?php echo __('Edit Faulty Condition'); ?></legend>
	<?php
	// pr($statusOptions);
		echo $this->Form->input('faulty_condition');
		echo $this->Ck->input('description',array('value'=>$description));
		
		//echo $this->Form->select('status',$activeOptions,array('empty' => false,'label' => 'Status'));
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
 
<!--  <script>
      CKEDITOR.replace('data[FaultyCondition][description]');
</script> --> 