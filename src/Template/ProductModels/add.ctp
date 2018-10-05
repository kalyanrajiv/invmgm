 
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
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
        <legend><?= __('Add Product Model') ?></legend>
        <?php
            echo $this->Form->input('brand_id', ['options' => $brands]);
            ?>
            <table>
			<tr>
				<th>Model<span style="color:red">*</span></th>
				<th>Brief Description</th>
			</tr>
            <?php for($n = 0; $n < 10; $n++){
            ?>
            <tr>
				 <td><input name = "ProductModel[model][]" type="text" value=""></td>
				<td><input name = "ProductModel[brief_description][]" type="text" value=""></td>
                
			</tr>
            <?php
                }
             ?>
            </table>
		
	<?php
		 echo $this->Form->input('status', array('options' => $activeOptions));
	?>
            
           
       
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
