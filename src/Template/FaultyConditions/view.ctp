
<div class="FaultyCondition view">
<h2><?php echo __('Faulty Conditions'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($faultyconditions['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Problem Type'); ?></dt>
		<dd>
			<?php echo  h($faultyconditions['faulty_condition']);?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo  $faultyconditions['description'] ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $statusOptions[$faultyconditions['status']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($faultyconditions['created'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($faultyconditions['modified'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Faulty <br/>Condition'), array('action' => 'edit', $faultyconditions['id']),array('escape'=>false)); ?> </li>
		<?php //echo $this->Form->postLink(__('Delete Faulty <br/> Condition'), array('action' => 'delete', $faultyconditions['id']), array(), __('Are you sure you want to delete # %s?', $functionconditions['id'])); ?>
		<li><?php echo $this->Html->link(__('List Faulty <br/>Conditions'), array('action' => 'index'),array('escape'=>false)); ?> </li>
		<li><?php echo $this->Html->link(__('New Faulty <br/>Condition'), array('action' => 'add'),array('escape'=>false)); ?></li>
	</ul>
</div>

