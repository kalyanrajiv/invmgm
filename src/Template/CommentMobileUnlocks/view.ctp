<div class="commentMobileUnlocks view">
<h2><?php echo __('Comment Mobile Unlock'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($commentMobileUnlock['CommentMobileUnlock']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($commentMobileUnlock['User']['id'], array('controller' => 'users', 'action' => 'view', $commentMobileUnlock['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile Unlock'); ?></dt>
		<dd>
			<?php echo $this->Html->link($commentMobileUnlock['MobileUnlock']['id'], array('controller' => 'mobile_unlocks', 'action' => 'view', $commentMobileUnlock['MobileUnlock']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Brief History'); ?></dt>
		<dd>
			<?php echo h($commentMobileUnlock['CommentMobileUnlock']['brief_history']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Admin Remarks'); ?></dt>
		<dd>
			<?php echo h($commentMobileUnlock['CommentMobileUnlock']['admin_remarks']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($commentMobileUnlock['CommentMobileUnlock']['status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($commentMobileUnlock['CommentMobileUnlock']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($commentMobileUnlock['CommentMobileUnlock']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Comment Mobile Unlock'), array('action' => 'edit', $commentMobileUnlock['CommentMobileUnlock']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Comment Mobile Unlock'), array('action' => 'delete', $commentMobileUnlock['CommentMobileUnlock']['id']), array(), __('Are you sure you want to delete # %s?', $commentMobileUnlock['CommentMobileUnlock']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Comment Mobile Unlocks'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Comment Mobile Unlock'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'add')); ?> </li>
	</ul>
</div>
