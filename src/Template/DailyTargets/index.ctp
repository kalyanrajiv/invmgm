<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Daily Target'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Kiosks'), ['controller' => 'Kiosks', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Kiosk'), ['controller' => 'Kiosks', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="dailyTargets index large-9 medium-8 columns content">
    <h3><?= __('Daily Targets') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('kiosk_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('target') ?></th>
                <th scope="col"><?= $this->Paginator->sort('product_sale') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile_sale') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile_repair_sale') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile_unlock_sale') ?></th>
                <th scope="col"><?= $this->Paginator->sort('product_refund') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile_refund') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile_repair_refund') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile_unlock_refund') ?></th>
                <th scope="col"><?= $this->Paginator->sort('total_sale') ?></th>
                <th scope="col"><?= $this->Paginator->sort('total_refund') ?></th>
                <th scope="col"><?= $this->Paginator->sort('target_date') ?></th>
                <th scope="col"><?= $this->Paginator->sort('status') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dailyTargets as $dailyTarget): ?>
            <tr>
                <td><?= $this->Number->format($dailyTarget->id) ?></td>
                <td><?= $dailyTarget->has('kiosk') ? $this->Html->link($dailyTarget->kiosk->name, ['controller' => 'Kiosks', 'action' => 'view', $dailyTarget->kiosk->id]) : '' ?></td>
                <td><?= $dailyTarget->has('user') ? $this->Html->link($dailyTarget->user->id, ['controller' => 'Users', 'action' => 'view', $dailyTarget->user->id]) : '' ?></td>
                <td><?= $this->Number->format($dailyTarget->target) ?></td>
                <td><?= $this->Number->format($dailyTarget->product_sale) ?></td>
                <td><?= $this->Number->format($dailyTarget->mobile_sale) ?></td>
                <td><?= $this->Number->format($dailyTarget->mobile_repair_sale) ?></td>
                <td><?= $this->Number->format($dailyTarget->mobile_unlock_sale) ?></td>
                <td><?= $this->Number->format($dailyTarget->product_refund) ?></td>
                <td><?= $this->Number->format($dailyTarget->mobile_refund) ?></td>
                <td><?= $this->Number->format($dailyTarget->mobile_repair_refund) ?></td>
                <td><?= $this->Number->format($dailyTarget->mobile_unlock_refund) ?></td>
                <td><?= $this->Number->format($dailyTarget->total_sale) ?></td>
                <td><?= $this->Number->format($dailyTarget->total_refund) ?></td>
                <td><?= h($dailyTarget->target_date) ?></td>
                <td><?= $this->Number->format($dailyTarget->status) ?></td>
                <td><?= h($dailyTarget->created) ?></td>
                <td><?= h($dailyTarget->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $dailyTarget->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $dailyTarget->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $dailyTarget->id], ['confirm' => __('Are you sure you want to delete # {0}?', $dailyTarget->id)]) ?>
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
