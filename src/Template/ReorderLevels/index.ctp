<div class="reorderLevels index">
	<h2><?php echo __('Reorder Levels'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('reorder_level'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($reorderLevels as $reorderLevel): ?>
	<tr>
		<td><?php echo h($reorderLevel->id); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($reorderLevel->kiosk['name'], array('controller' => 'kiosks', 'action' => 'view', $reorderLevel->kiosk['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($reorderLevel->product['product'], array('controller' => 'products', 'action' => 'view', $reorderLevel->product['id'])); ?>
		</td>
		<td><?php echo h($reorderLevel->reorder_level); ?>&nbsp;</td>
		<td><?php echo h($reorderLevel->status); ?>&nbsp;</td>
		<td><?php echo date('jS M, Y g:i A', strtotime($reorderLevel->created)); ?>&nbsp;</td>
		<td><?php echo date('jS M, Y g:i A',strtotime($reorderLevel->modified)); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $reorderLevel->id)); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $reorderLevel->id)); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $reorderLevel->id), array(), __('Are you sure you want to delete # %s?', $reorderLevel->id)); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Reorder Level'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
