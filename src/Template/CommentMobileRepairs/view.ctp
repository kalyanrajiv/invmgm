<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Comment Mobile Repair'), ['action' => 'edit', $commentMobileRepair->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Comment Mobile Repair'), ['action' => 'delete', $commentMobileRepair->id], ['confirm' => __('Are you sure you want to delete # {0}?', $commentMobileRepair->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Comment Mobile Repairs'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Comment Mobile Repair'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Mobile Repairs'), ['controller' => 'MobileRepairs', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mobile Repair'), ['controller' => 'MobileRepairs', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="commentMobileRepairs view large-9 medium-8 columns content">
    <h3><?= h($commentMobileRepair->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $commentMobileRepair->has('user') ? $this->Html->link($commentMobileRepair->user->id, ['controller' => 'Users', 'action' => 'view', $commentMobileRepair->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mobile Repair') ?></th>
            <td><?= $commentMobileRepair->has('mobile_repair') ? $this->Html->link($commentMobileRepair->mobile_repair->id, ['controller' => 'MobileRepairs', 'action' => 'view', $commentMobileRepair->mobile_repair->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($commentMobileRepair->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Status') ?></th>
            <td><?= $this->Number->format($commentMobileRepair->status) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($commentMobileRepair->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($commentMobileRepair->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Brief History') ?></h4>
        <?= $this->Text->autoParagraph(h($commentMobileRepair->brief_history)); ?>
    </div>
    <div class="row">
        <h4><?= __('Admin Remarks') ?></h4>
        <?= $this->Text->autoParagraph(h($commentMobileRepair->admin_remarks)); ?>
    </div>
</div>
