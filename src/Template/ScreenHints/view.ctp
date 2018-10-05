<div class="settings view">
<h2><?php echo __('Screen Hint'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($ScreenHint['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Controller'); ?></dt>
		<dd>
			<?php echo h($ScreenHint['controller']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Action'); ?></dt>
		<dd>
			<?php echo $ScreenHint['action']; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Hint'); ?></dt>
		<dd>
			<?php echo ($ScreenHint['hint']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('URL'); ?></dt>
		<dd>
			<?php echo ($ScreenHint['description']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($ScreenHint['status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($ScreenHint['created'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($ScreenHint['modified'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Screen Hint'), array('action' => 'edit', $ScreenHint['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Screen Hint'), array('action' => 'delete', $ScreenHint['id']), array(), __('Are you sure you want to delete # %s?', $ScreenHint['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Screen Hint'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Screen Hint'), array('action' => 'add')); ?> </li>
	</ul>
</div>
