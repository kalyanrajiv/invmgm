 
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
        <li><?php /*echo $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $mobileModel->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $mobileModel->id)]
            )*/
        ?></li>
        <li><?= $this->Html->link(__('List Product Models'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Brands'), ['controller' => 'Brands', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Brand'), ['controller' => 'Brands', 'action' => 'add']) ?></li>
      	<li><?php echo $this->Html->link(__('List Mobile <br/>Unlock Prices'), ['controller' => 'mobile-unlock-prices', 'action' => 'index'],['escape' => false]); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile <br/>Unlock Price'), ['controller' => 'mobile-unlock-prices', 'action' => 'add'],['escape' => false]); ?> </li>
       
    </ul>
 </div>
<div class="mobileModels form large-9 medium-8 columns content">
    <?= $this->Form->create($mobileModel) ?>
    <fieldset>
        <legend><?= __('Edit Product Model') ?></legend>
        <?php
            echo $this->Form->input('brand_id', ['options' => $brands]);
            echo $this->Form->input('model');
            echo $this->Form->input('brief_description');
            echo $this->Form->input('status',['options'=>$activeOptions]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
