<div class="reorderLevels form">
<?php echo $this->Form->create('ReorderLevel'); ?>
	<fieldset>
		<legend><?php echo __('Edit Reorder Level'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('kiosk_id');
		echo $this->Form->input('product_id');
		echo $this->Form->input('reorder_level');
		echo $this->Form->input('status');
	?>
	</fieldset>
<?php
echo $this->Form->Submit(__('Submit'),array('name'=>'submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ReorderLevel.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ReorderLevel.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Reorder Levels'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
