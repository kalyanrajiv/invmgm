<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Color[]|\Cake\Collection\CollectionInterface $colors
 */
?>
 
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		  <li><?= $this->Html->link(__('New Color'), ['action' => 'add']) ?></li>
	</ul>
</div>
<div class="colors index large-9 medium-8 columns content">
    <h3><?= __('Colors') ?></h3>
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
            <?php foreach ($colors as $color): ?>
            <tr>
                <td><?= $this->Number->format($color->id) ?></td>
                <td><?= h($color->name) ?></td>
                <td><?= $activeOptions[$color->status] ?></td>
                <td><?= date('d-m-y g:i A',strtotime($color->created)) ?></td>
                <td><?= date('d-m-y g:i A',strtotime($color->modified)) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $color->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $color->id]) ?>
                    <?php //$this->Form->postLink(__('Delete'), ['action' => 'delete', $color->id], ['confirm' => __('Are you sure you want to delete # {0}?', $color->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>

