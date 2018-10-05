<div class="networks view">
<h2><?php echo __('Network'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($network->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($network->name); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Title'); ?></dt>
		<dd>
			<?php echo  h($network->title); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($network->status); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h(date('jS M, Y g:i A',strtotime($network->created))); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h(date('jS M, Y g:i A',strtotime($network->modified))); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Network'), array('action' => 'edit', $network->id)); ?> </li>
		<li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $network->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $network->id)]
            )
        ?></li>
		<li><?php echo $this->Html->link(__('List Networks'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Network'), array('action' => 'add')); ?> </li>
	</ul>
</div>
