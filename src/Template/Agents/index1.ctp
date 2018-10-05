  <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
?>
<div class="brands index large-9 medium-8 columns content">
    
    
    <h2><?php echo __('Agents'); ?>
     
    <table cellpadding="0" cellspacing="0">
        <thead>
           <tr>
                 <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                 <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                 <th scope="col"><?= $this->Paginator->sort('status') ?></th>
                 <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                 <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
           <?php foreach ($agents as $agent): ?>
            <tr>
                <td><?= $this->Number->format($agent->id) ?></td>
                <td><?= h($agent->name) ?></td>
                <td><?= $active[$this->Number->format($agent->status)] ?></td>
                <td><?= h($agent->created) ?></td>
                <td><?= h($agent->modified) ?></td>
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
		 <li><?= $this->Html->link(__('New Agent'), ['action' => 'add']) ?></li>
		 <li><?= $this->Html->link(__('List Customers'), ['controller' => 'customers' , 'action' => 'index']) ?></li>
	</ul>
</div>