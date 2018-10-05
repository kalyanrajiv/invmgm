<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Daily Targets'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Kiosks'), ['controller' => 'Kiosks', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Kiosk'), ['controller' => 'Kiosks', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="dailyTargets form large-9 medium-8 columns content">
    <?= $this->Form->create($dailyTarget) ?>
    <fieldset>
        <legend><?= __('Add Daily Target') ?></legend>
        <?php
            echo $this->Form->input('kiosk_id', ['options' => $kiosks]);
            echo $this->Form->input('user_id', ['options' => $users, 'empty' => true]);
            echo $this->Form->input('target');
            echo $this->Form->input('product_sale');
            echo $this->Form->input('mobile_sale');
            echo $this->Form->input('mobile_repair_sale');
            echo $this->Form->input('mobile_unlock_sale');
            echo $this->Form->input('product_refund');
            echo $this->Form->input('mobile_refund');
            echo $this->Form->input('mobile_repair_refund');
            echo $this->Form->input('mobile_unlock_refund');
            echo $this->Form->input('total_sale');
            echo $this->Form->input('total_refund');
            echo $this->Form->input('target_date');
            echo $this->Form->input('status');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
