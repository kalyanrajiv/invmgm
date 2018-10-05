<div class="networks form">
<?php echo $this->Form->create($network); ?>
	<fieldset>
		<legend><?php echo __('Edit Network'); ?></legend>
	<?php
		$this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('title');
		echo $this->Form->input('status');
	?>
	</fieldset>
 <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		  <li><?php /*echo $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $network->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $network->id)]
            )*/
        ?></li>
		<li><?php echo $this->Html->link(__('List Networks'), array('action' => 'index')); ?></li>
	</ul>
</div>
