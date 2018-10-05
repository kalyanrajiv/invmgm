<div class="customers view">
<h2><?php echo __('Retail Customer'); ?></h2>
	<dl>
		<dt><?php //pr($retailcustomer);
		echo __('Id'); ?></dt>
		<dd>
			<?php echo h($retailcustomer['id']); ?>
			&nbsp;
		</dd>
		<h4>Customer Details:</h4>
		 
		<dt><?php echo __('Retail Customer Name'); ?></dt>
		<dd>
			<?php echo h($retailcustomer['fname'])." ".$retailcustomer['lname']; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer Email'); ?></dt>
		<dd>
			<?php echo h($retailcustomer['email']); ?>
			&nbsp;
		</dd>
		 
		<dt><?php echo __('Mobile'); ?></dt>
		<dd>
			<?php echo h($retailcustomer['mobile']); ?>
			&nbsp;
		</dd>
		 
		 
		<dt><?php echo __('Address'); ?></dt>
		<dd>
			<?php echo $retailcustomer['address_1']."<br/>".$retailcustomer['address_2']; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('City'); ?></dt>
		<dd>
			<?php echo h($retailcustomer['city']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('County'); ?></dt>
		<dd>
			<?php echo $retailcustomer['state']; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($retailcustomer['zip']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Country'); ?></dt>
		<dd>
			<?php
			$country = $retailcustomer['country'];
				if(array_key_exists($country,$countryOptions )){
					echo $countryname =  $countryOptions[$retailcustomer['country']];
				}else{
					echo $countryname = ""; 
				}
		 ?>
			&nbsp;
		</dd>
		 
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($retailcustomer['created'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($retailcustomer['modified'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Sell'), array('controller'=>'kiosk_product_sales','action' => 'new_sale', $retailcustomer['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Create Performa'), array('controller'=>'invoice_order_details','action' => 'create_invoice', $retailcustomer['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Edit Customer'), array('action' => 'edit', $retailcustomer['id'])); ?> </li>
		<li><?php #echo $this->Form->postLink(__('Delete Customer'), array('action' => 'delete', $customer['Customer']['id']), array(), __('Are you sure you want to delete # %s?', $customer['Customer']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Customers'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('action' => 'add')); ?> </li>
	</ul>
</div>
