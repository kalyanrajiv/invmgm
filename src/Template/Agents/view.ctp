<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$active = Configure::read('active');
$currency = Configure::read('CURRENCY_TYPE'); 

?>
<div class="brands view">
    
<h2><?php echo __('Account Manager'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($agent->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($agent->name); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Memo'); ?></dt>
		<dd>
			<?= $this->Text->autoParagraph(h($agent->memo)); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $active[$agent->status]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($agent->created) ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($agent->modified); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		 <li><?= $this->Html->link(__('Edit Account Manager'), ['action' => 'edit', $agent->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Account Manager'), ['action' => 'delete', $agent->id], ['confirm' => __('Are you sure you want to delete # {0}?', $agent->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Account Manager'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Account Manager'), ['action' => 'add']) ?> </li>
	</ul>
</div>
 
</div>