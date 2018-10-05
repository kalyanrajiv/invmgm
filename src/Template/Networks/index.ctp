<div class="networks index">
	<h2><?php echo __('Networks'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php  foreach ($networks as $network): ?>
            <tr>
                <td><?= $this->Number->format($network->id) ?></td>
                <td><?= h($network->name) ?></td>
                <td><?= h($network->title) ?></td>
                <td><?= $this->Number->format($network->status) ?></td>
                <td><?= h(date('jS M, Y g:i A',strtotime($network->created))) ?></td>
                <td><?= h(date('jS M, Y g:i A',strtotime($network->modified))) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $network->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $network->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $network->id], ['confirm' => __('Are you sure you want to delete # {0}?', $network->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
	</tbody>
	</table>
	<?php
	$count = $this->request->params['paging']['Networks']['count'];
	$current = $this->request->params['paging']['Networks']['current'];
	if($count>$current){
		
	?>	
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	<?php
	}
	?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Network'), array('action' => 'add')); ?></li>
	</ul>
</div>
