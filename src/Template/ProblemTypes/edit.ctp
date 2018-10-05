<div class="problemtypes form">
 <?= $this->Form->create($problemType) ?>
	<fieldset>
		<legend><?php echo __('Edit Problem Type'); ?></legend>
	<?php
	// pr($statusOptions);
		echo $this->Form->input('problem_type');
		echo $this->Ck->input('description');
		
		//echo $this->Form->select('status',$activeOptions,array('empty' => false,'label' => 'Status'));
		echo $this->Form->input('status',array('options' => $statusOptions));
	?>
	</fieldset>
 <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Problem Types'), array('action' => 'index')); ?></li>
			
	</ul>
</div>
 <script>
     // CKEDITOR.replace( 'description');
</script> 