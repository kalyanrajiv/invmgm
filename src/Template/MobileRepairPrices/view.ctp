<?php
	 
?>
<div class="mobileRepairPrices view">
<h2><?php echo __('Mobile Repair Price'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mobileRepairPrice->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php
           echo  $mobileRepairPrice->has('brand') ? $this->Html->link($brands[$mobileRepairPrice->brand->id], ['controller' => 'Brands', 'action' => 'view', $mobileRepairPrice->brand->id]) : ''  
  ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Model'); ?></dt>
		<dd>
			<?= $mobileRepairPrice->has('mobile_model') ? $this->Html->link($modelname[$mobileRepairPrice->mobile_model->id], ['controller' => 'MobileModels', 'action' => 'view', $mobileRepairPrice->mobile_model->id]) : '' ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Problem Type'); ?></dt>
		<dd>
			<?php echo $problemtype[$mobileRepairPrice->problem_type]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Problem'); ?></dt>
		<dd>
			<?php echo h($mobileRepairPrice->problem); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Repair Cost'); ?></dt>
		<dd>
			<?php $repair_cost = h($mobileRepairPrice->repair_cost);
				echo  $CURRENCY_TYPE.$repair_cost ; 
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Repair Price'); ?></dt>
		<dd>
			<?php $repair_price =  h($mobileRepairPrice->repair_price);
			echo $CURRENCY_TYPE.$repair_price ;
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Repair Days'); ?></dt>
		<dd>
			<?php echo h($mobileRepairPrice->repair_days); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $active[$mobileRepairPrice->status]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo  date('jS M, Y g:i A',strtotime($mobileRepairPrice->created)) ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo   date('jS M, Y g:i A',strtotime($mobileRepairPrice->modified)) ; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Mobile <br/> Repair Price'), array('action' => 'edit', $mobileRepairPrice->id),array('escape' => false)); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Mobile <br/>Repair Price'), array('action' => 'delete', $mobileRepairPrice->id), array('escape' => false) ,array(), __('Are you sure you want to delete # %s?', $mobileRepairPrice->id) ); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile <br/>Repair Prices'), array('action' => 'index'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile <br/>Repair Price'), array('action' => 'add'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Models'), array('controller' => 'models', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Model'), array('controller' => 'models', 'action' => 'add')); ?> </li>
	</ul>
</div>
