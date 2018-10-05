<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Color $color
 */
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		  <li><?= $this->Html->link(__('Edit Color'), ['action' => 'edit', $color->id]) ?> </li>
        <li><?php $this->Form->postLink(__('Delete Color'), ['action' => 'delete', $color->id], ['confirm' => __('Are you sure you want to delete # {0}?', $color->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Colors'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Color'), ['action' => 'add']) ?> </li>
	</ul>
</div>
 
 
    
 
<div class="brands view">
    <h3><?= h($color->name) ?></h3>
    <dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?= $this->Number->format($color->id) ?>
			&nbsp;
		</dd>
        
        <dt><?php echo __('Name'); ?></dt>
		<dd>
			<?= h($color->name) ?>
			&nbsp;
		</dd>
         
        <dt><?php echo __('Status'); ?></dt>
		<dd>
			<?= $activeOptions[$color->status] ?>
			&nbsp;
		</dd>
        <dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($color->created) ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($color->modified)); ?>
			&nbsp;
		</dd>
    </dl>
    
</div>
