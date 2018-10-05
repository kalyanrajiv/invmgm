<?php
//pr($brand);die;
?>
<div class="brands form large-9 medium-8 columns content">
    <?= $this->Form->create($agent) ?>
    <fieldset>
        <legend><?= __('Add Account Manager') ?></legend>
        <?php
            echo $this->Form->input('name');
            echo $this->Form->input('memo');
          echo $this->Form->input('status',array('options' => $activeOptions));
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit','style'=>"width: 108px;height: 46px;")) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		 <li><?= $this->Html->link(__('List Account Manager'), ['action' => 'index']) ?></li>
          <li><?= $this->Html->link(__('List Customers'), ['action' => 'index']) ?></li>
	</ul>
</div>