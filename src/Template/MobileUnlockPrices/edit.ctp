<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <div class="actions">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $mobileUnlockPrice->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $mobileUnlockPrice->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Mobile Unlock <br>Prices'), ['action' => 'index'],['escape'=>false]) ?></li>
        <li><?= $this->Html->link(__('List Brands'), ['controller' => 'Brands', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Brand'), ['controller' => 'Brands', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Mobile Models'), ['controller' => 'MobileModels', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mobile Model'), ['controller' => 'MobileModels', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Networks'), ['controller' => 'Networks', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Network'), ['controller' => 'Networks', 'action' => 'add']) ?></li>
    </ul>
    </div>
</nav>
<div class="mobileUnlockPrices form large-9 medium-8 columns content">
    <?= $this->Form->create($mobileUnlockPrice) ?>
    <fieldset>
        <legend><?= __('Edit Mobile Unlock Price') ?></legend>
        <?php
            echo $this->Form->input('brand_id', ['options' => $brands]);
            echo $this->Form->input('mobile_model_id', ['options' => $mobileModels]);
            echo $this->Form->input('network_id', ['options' => $networks]);
            echo $this->Form->input('unlocking_cost');
            echo $this->Form->input('unlocking_price');
            echo $this->Form->input('unlocking_days');
            echo $this->Form->input('unlocking_minutes');
            echo $this->Form->input('status');
            echo $this->Form->input('status_change_date', ['empty' => true]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
