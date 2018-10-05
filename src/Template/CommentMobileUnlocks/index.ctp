<div class="commentMobileUnlocks index">
	<h2><?php echo __('Comment Mobile Unlocks'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile_unlock_id'); ?></th>
			<th><?php echo $this->Paginator->sort('brief_history'); ?></th>
			<th><?php echo $this->Paginator->sort('admin_remarks'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($commentMobileUnlocks as $commentMobileUnlock): ?>
	<tr>
		<td><?php echo h($commentMobileUnlock['CommentMobileUnlock']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($commentMobileUnlock['User']['id'], array('controller' => 'users', 'action' => 'view', $commentMobileUnlock['User']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($commentMobileUnlock['MobileUnlock']['id'], array('controller' => 'mobile_unlocks', 'action' => 'view', $commentMobileUnlock['MobileUnlock']['id'])); ?>
		</td>
		<td><?php echo h($commentMobileUnlock['CommentMobileUnlock']['brief_history']); ?>&nbsp;</td>
		<td><?php echo h($commentMobileUnlock['CommentMobileUnlock']['admin_remarks']); ?>&nbsp;</td>
		<td><?php echo h($commentMobileUnlock['CommentMobileUnlock']['status']); ?>&nbsp;</td>
		<td><?php echo h($commentMobileUnlock['CommentMobileUnlock']['created']); ?>&nbsp;</td>
		<td><?php echo h($commentMobileUnlock['CommentMobileUnlock']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $commentMobileUnlock['CommentMobileUnlock']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $commentMobileUnlock['CommentMobileUnlock']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $commentMobileUnlock['CommentMobileUnlock']['id']), array(), __('Are you sure you want to delete # %s?', $commentMobileUnlock['CommentMobileUnlock']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Comment Mobile Unlock'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'add')); ?> </li>
	</ul>
</div>
