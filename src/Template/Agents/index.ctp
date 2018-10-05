<div class="networks index">
	<h2><?php echo __('Account Manager'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php  foreach ($agents as $agent): ?>
            <tr>
                <td><?= $this->Number->format($agent->id) ?></td>
                <td><?= h($agent->name) ?></td>
              
                <td><?= $active[$this->Number->format($agent->status)] ?></td>
                <td><?= h(date('d-m-y g:i A',strtotime($agent->created) ))?></td>
                <td><?= h(date('d-m-y g:i A',strtotime($agent->modified) ))?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $agent->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $agent->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $agent->id], ['confirm' => __('Are you sure you want to delete # {0}?', $agent->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
	</tbody>
	</table>
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		 <li><?= $this->Html->link(__('Add Account Manager'), ['action' => 'add']) ?></li>
		 <li><?= $this->Html->link(__('List Customers'), ['controller' => 'customers' , 'action' => 'index']) ?></li>
	</ul>
</div>
