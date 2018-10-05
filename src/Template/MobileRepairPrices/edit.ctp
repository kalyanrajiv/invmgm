 
<div class="mobileRepairPrices form">
    <?= $this->Form->create($mobileRepairPrice) ?>
    <fieldset>
        <legend><?= __('Edit Mobile Repair Price') ?></legend>
        <?php
            echo $this->Form->input('brand_id', ['options' => $brands]);
            echo $this->Form->input('mobile_model_id', ['options' => $mobileModels]);
            echo $this->Form->input('problem_type',['options'=>$problemtype]);
            echo $this->Form->input('problem');
            echo $this->Form->input('repair_cost',['type'=>'text']);
            echo $this->Form->input('repair_price',['type'=>'text']);
            echo $this->Form->input('repair_days',['type'=>'text']);
           echo $this->Form->input('status', ['options' => $active,'type'=>'hidden']);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		  <li class="heading"><?= __('Actions') ?></li>
        <li><?php /*echo $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $mobileRepairPrice->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $mobileRepairPrice->id)]
            )*/
        ?></li>
        <li><?= $this->Html->link(__('List Mobile<br/> Repair Prices'), ['action' => 'index'],['escape' => false]) ?></li>
        <li><?= $this->Html->link(__('List Brands'), ['controller' => 'Brands', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Brand'), ['controller' => 'Brands', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Mobile Models'), ['controller' => 'MobileModels', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mobile Model'), ['controller' => 'MobileModels', 'action' => 'add']) ?></li>
	</ul>
</div>
