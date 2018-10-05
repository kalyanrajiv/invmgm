<?php //pr($reorderLevel);die; ?>
<div class="reorderLevels view">
<h2><?php echo __('Reorder Level'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($reorderLevel['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Kiosk'); ?></dt>
		<dd>
			<?php echo $this->Html->link($reorderLevel['kiosk']['name'], array('controller' => 'kiosks', 'action' => 'view', $reorderLevel['kiosk']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product'); ?></dt>
		<dd>
			<?php echo $this->Html->link($reorderLevel['product']['id'], array('controller' => 'products', 'action' => 'view', $reorderLevel['product']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Reorder Level'); ?></dt>
		<dd>
			<?php echo h($reorderLevel['reorder_level']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($reorderLevel['status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($reorderLevel['created']));//$this->Time->format('jS M, Y g:i A', $reorderLevel['created'],null,null);  ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($reorderLevel['modified']));//echo $this->Time->format('jS M, Y g:i A',$reorderLevel['modified'],null,null); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Reorder Level'), array('action' => 'edit', $reorderLevel['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Reorder Level'), array('action' => 'delete', $reorderLevel['id']), array(), __('Are you sure you want to delete # %s?', $reorderLevel['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Reorder Levels'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Reorder Level'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
