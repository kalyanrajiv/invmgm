<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Comment Mobile Repair'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Mobile Repairs'), ['controller' => 'MobileRepairs', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mobile Repair'), ['controller' => 'MobileRepairs', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="commentMobileRepairs index large-9 medium-8 columns content">
    <h3><?= __('Comment Mobile Repairs') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile_repair_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('status') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commentMobileRepairs as $commentMobileRepair): ?>
            <tr>
                <td><?= $this->Number->format($commentMobileRepair->id) ?></td>
                <td><?= $commentMobileRepair->has('user') ? $this->Html->link($commentMobileRepair->user->id, ['controller' => 'Users', 'action' => 'view', $commentMobileRepair->user->id]) : '' ?></td>
                <td><?= $commentMobileRepair->has('mobile_repair') ? $this->Html->link($commentMobileRepair->mobile_repair->id, ['controller' => 'MobileRepairs', 'action' => 'view', $commentMobileRepair->mobile_repair->id]) : '' ?></td>
                <td><?= $this->Number->format($commentMobileRepair->status) ?></td>
                <td><?= h($commentMobileRepair->created) ?></td>
                <td><?= h($commentMobileRepair->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $commentMobileRepair->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $commentMobileRepair->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $commentMobileRepair->id], ['confirm' => __('Are you sure you want to delete # {0}?', $commentMobileRepair->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
