
<div class="problem view">
<h2><?php echo __('Problem Type'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($problemType->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Problem Type'); ?></dt>
		<dd>
			<?php echo  h($problemType->problem_type);?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo  $problemType->description ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $this->Number->format($problemType->status); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo  h(date('jS M, Y g:i A',strtotime($problemType->created))); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h(date('jS M, Y g:i A',strtotime($problemType->modified))); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
        <li><?= $this->Html->link(__('Edit Problem Type'), ['action' => 'edit', $problemType->id]) ?> </li>
      
        <li><?= $this->Html->link(__('List Problem Types'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Problem Type'), ['action' => 'add']) ?> </li>
		 
	</ul>
</div>

