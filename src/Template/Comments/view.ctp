<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$active = Configure::read('active');
$currency = Configure::read('CURRENCY_TYPE'); 

?>
<div class="brands view">
    
<h2><?php echo __('Comment'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($comment->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User name'); ?></dt>
		<dd>
			 <?= $comment->has('user') ? $this->Html->link($comment->user['username'], ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Post'); ?></dt>
		<dd>
			<?= $comment->has('post') ? $this->Html->link($comment->post->title, ['controller' => 'Posts', 'action' => 'view', $comment->post->id]) : '' ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $active[$comment->status]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($comment->created) ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($comment->modified)); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		 <li><?= $this->Html->link(__('Edit Comment'), ['action' => 'edit', $comment->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Comment'), ['action' => 'delete', $comment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $comment->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Comments'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Comment'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Posts'), ['controller' => 'Posts', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Post'), ['controller' => 'Posts', 'action' => 'add']) ?> </li>
	</ul>
</div>
 