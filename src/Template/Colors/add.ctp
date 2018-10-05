<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Color $color
 */
?>
 
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?= $this->Html->link(__('List Colors'), ['action' => 'index']) ?></li>
	</ul>
</div>
<div class="colors form large-9 medium-8 columns content">
    <?= $this->Form->create($color) ?>
    <fieldset>
        <legend><?= __('Add Color') ?></legend>
        <?php
            echo $this->Form->control('name');
            echo $this->Form->input('status',array('options' => $activeOptions));
           // echo $this->Form->control('status');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
