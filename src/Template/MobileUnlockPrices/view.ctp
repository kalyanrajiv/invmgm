<?php
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	 
?>
<div class="mobileUnlockPrices view">
<h2><?php echo __('Mobile Unlock Price'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mobileUnlockPrice['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php echo $this->Html->link($mobileUnlockPrice['brand']['brand'], array('controller' => 'brands', 'action' => 'view', $mobileUnlockPrice['brand']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile Model'); ?></dt>
		<dd>
			<?php echo $this->Html->link($mobileUnlockPrice['mobile_model']['model'], array('controller' => 'mobile_models', 'action' => 'view', $mobileUnlockPrice['mobile_model']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Network'); ?></dt>
		<dd>
			<?php echo $this->Html->link($mobileUnlockPrice['network']['title'], array('controller' => 'networks', 'action' => 'view', $mobileUnlockPrice['network']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Unlocking Price'); ?></dt>
		<dd>
			<?php $unlocking_price = h($mobileUnlockPrice['unlocking_price']);
			echo $currency.$unlocking_price;
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Unlocking Days'); ?></dt>
		<dd>
			<?php echo h($mobileUnlockPrice['unlocking_days']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Unlocking Minutes'); ?></dt>
		<dd>&nbsp;&nbsp;
			<?php echo h($mobileUnlockPrice['unlocking_minutes']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A', strtotime($mobileUnlockPrice['created'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A', strtotime($mobileUnlockPrice['modified'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Mobile <br/>Unlock Price'), ['action' => 'edit', $mobileUnlockPrice['id']],['escape' => false]); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Mobile <br/> Unlock Price'), ['action' => 'delete', $mobileUnlockPrice['id']],['escape' => false],
                                             array(), __('Are you sure you want to delete # %s?', $mobileUnlockPrice['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile<br/> Unlock Prices'), ['action' => 'index'],['escape' => false]); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile <br/>Unlock Price'), ['action' => 'add'],['escape' => false]); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Models'), array('controller' => 'mobile_models', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Model'), array('controller' => 'mobile_models', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Networks'), array('controller' => 'networks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Network'), array('controller' => 'networks', 'action' => 'add')); ?> </li>
	</ul>
</div>
