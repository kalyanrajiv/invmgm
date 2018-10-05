<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Daily Target'), ['action' => 'edit', $dailyTarget->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Daily Target'), ['action' => 'delete', $dailyTarget->id], ['confirm' => __('Are you sure you want to delete # {0}?', $dailyTarget->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Daily Targets'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Daily Target'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Kiosks'), ['controller' => 'Kiosks', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Kiosk'), ['controller' => 'Kiosks', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="dailyTargets view large-9 medium-8 columns content">
    <h3><?= h($dailyTarget->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Kiosk') ?></th>
            <td><?= $dailyTarget->has('kiosk') ? $this->Html->link($dailyTarget->kiosk->name, ['controller' => 'Kiosks', 'action' => 'view', $dailyTarget->kiosk->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $dailyTarget->has('user') ? $this->Html->link($dailyTarget->user->id, ['controller' => 'Users', 'action' => 'view', $dailyTarget->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($dailyTarget->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Target') ?></th>
            <td><?= $this->Number->format($dailyTarget->target) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Product Sale') ?></th>
            <td><?= $this->Number->format($dailyTarget->product_sale) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mobile Sale') ?></th>
            <td><?= $this->Number->format($dailyTarget->mobile_sale) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mobile Repair Sale') ?></th>
            <td><?= $this->Number->format($dailyTarget->mobile_repair_sale) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mobile Unlock Sale') ?></th>
            <td><?= $this->Number->format($dailyTarget->mobile_unlock_sale) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Product Refund') ?></th>
            <td><?= $this->Number->format($dailyTarget->product_refund) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mobile Refund') ?></th>
            <td><?= $this->Number->format($dailyTarget->mobile_refund) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mobile Repair Refund') ?></th>
            <td><?= $this->Number->format($dailyTarget->mobile_repair_refund) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mobile Unlock Refund') ?></th>
            <td><?= $this->Number->format($dailyTarget->mobile_unlock_refund) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Total Sale') ?></th>
            <td><?= $this->Number->format($dailyTarget->total_sale) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Total Refund') ?></th>
            <td><?= $this->Number->format($dailyTarget->total_refund) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Status') ?></th>
            <td><?= $this->Number->format($dailyTarget->status) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Target Date') ?></th>
            <td><?= h($dailyTarget->target_date) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($dailyTarget->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($dailyTarget->modified) ?></td>
        </tr>
    </table>
</div>
