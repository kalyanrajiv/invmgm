
<div class="functioncondition view">
<h2><?php echo __('Function Tests'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($functionconditions['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Function Test'); ?></dt>
		<dd>
			<?php echo  h($functionconditions['function_condition']);?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo  $functionconditions['description'] ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $statusOptions[$functionconditions['status']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($functionconditions['created']));//$this->Time->format('jS M, Y g:i A',$functionconditions['created'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($functionconditions['modified']));//$this->Time->format('jS M, Y g:i A',$functionconditions['modified'],null,null); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Function Test'), array('action' => 'edit', $functionconditions['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Function Test'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Function Test'), array('action' => 'add')); ?></li>
	</ul>
</div>

