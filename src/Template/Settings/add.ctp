<div class="settings form">
<?php echo $this->Form->create('Setting'); ?>
	<fieldset>
		<legend><?php echo __('Add Setting'); ?></legend>
	<?php
		echo $this->Form->input('attribute_name');
		echo $this->Form->input('attribute_value');
		echo $this->Form->input('comment');
		 
        // echo $this->Form->hidden('id');
      
		echo  $this->Form->hidden('status',['value' => 1]);
	?>
	</fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		 <li><?= $this->Html->link(__('List Settings'), ['action' => 'index']) ?></li>
	</ul>
</div>
