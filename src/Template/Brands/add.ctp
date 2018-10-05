<?php
//pr($brand);die;
?>
<div class="brands form large-9 medium-8 columns content">
    <?= $this->Form->create($brand) ?>
    <fieldset>
        <legend><?= __('Add Brand') ?></legend>
        <?php
            echo $this->Form->input('brand');
            echo $this->Form->input('company');
            echo $this->Form->input('status',array('options' => $activeOptions));
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Brands'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>