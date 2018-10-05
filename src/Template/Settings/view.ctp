<div class="settings view">
<h2><?php echo __('Setting'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h(h($setting->id)); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Attribute Name'); ?></dt>
		<dd>
			<?php echo h(h($setting->attribute_name)); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Attribute Value'); ?></dt>
		<dd>
			<?php echo h($setting->attribute_value); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Comment'); ?></dt>
		<dd>
			<?php echo h($setting->comment); ?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($setting->status); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($setting->created)); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($setting->modified)); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		 <li><?= $this->Html->link(__('Edit Setting'), ['action' => 'edit', $setting->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Setting'), ['action' => 'delete', $setting->id], ['confirm' => __('Are you sure you want to delete # {0}?', $setting->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Settings'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Setting'), ['action' => 'add']) ?> </li>
	</ul>
</div>
