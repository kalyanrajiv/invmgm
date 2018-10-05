
<div class="Mobilecondition view">
<h2><?php echo __('Mobile Conditions'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mobileconditions['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Problem Type'); ?></dt>
		<dd>
			<?php echo  h($mobileconditions['mobile_condition']);?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo  $mobileconditions['description'] ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $statusOptions[$mobileconditions['status']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobileconditions['created']));//$this->Time->format('jS M, Y g:i A',$mobileconditions['created'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobileconditions['modified']));//$this->Time->format('jS M, Y g:i A',$mobileconditions['modified'],null,null); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Mobile Condition'), array('action' => 'edit', $mobileconditions['id'])); ?> </li>
		<!--<li><?php echo $this->Form->postLink(__('Delete Mobile Condition'), array('action' => 'delete', $mobileconditions['id']), array(), __('Are you sure you want to delete # %s?', $mobileconditions['MobileCondition']['id'])); ?> </li>-->
		<li><?php echo $this->Html->link(__('List Mobile Conditions'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Condition'), array('action' => 'add')); ?></li>
	</ul>
</div>

