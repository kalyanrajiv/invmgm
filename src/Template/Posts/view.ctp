<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$active = Configure::read('active');
 

?>
<div class="brands view">
    
<h2><?php echo __('Post'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($post->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User Name'); ?></dt>
		<dd>
			<?php  echo $post->user['username']; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Title'); ?></dt>
		<dd>
			<?php echo h($post->title); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php  if(array_key_exists($post->status,$active)){
                 echo  h($active[$post->status]); 
            } else{
                echo "--";
            }
            ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('d-m-y g:i A',strtotime($post->created)); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('d-m-y g:i A',strtotime($post->modified));  ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		 <li><?= $this->Html->link(__('Edit Post'), ['action' => 'edit', $post->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Post'), ['action' => 'delete', $post->id], ['confirm' => __('Are you sure you want to delete # {0}?', $post->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Posts'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Post'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
       
	</ul>
</div>
 