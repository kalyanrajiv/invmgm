<div class="MobileCondition form">
 <?php echo $this->Form->create('MobileCondition'); ?>
	<fieldset>
		<legend><?php echo __('Edit Mobile Condition'); ?></legend>
	<?php
	// pr($statusOptions);
		echo $this->Form->input('mobile_condition');
		echo $this->Ck->input('description');
		
		//echo $this->Form->select('status',$activeOptions,array('empty' => false,'label' => 'Status'));
		echo $this->Form->input('status',array('options' => $statusOptions));
	?>
	</fieldset>
    <?php echo $this->Form->submit('Submit',array('name'=>'submit')); ?>
<?php echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Mobile Conditions'), array('action' => 'index')); ?></li>
			
	</ul>
</div>
<script>
      CKEDITOR.replace( 'description');
</script>  
 