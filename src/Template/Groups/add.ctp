<?php
/**
  * @var \App\View\AppView $this
  */
?>
<div class="groups form large-9 medium-8 columns content">
    <?= $this->Form->create($group) ?>
    <fieldset>
        <legend><?= __('Add Group') ?></legend>
        <?php
            echo $this->Form->input('name');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
 <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('New Group'), array('action' => 'add')); ?></li>
        <li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
    </ul>
</div>