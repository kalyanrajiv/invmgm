<div class="warehouseVendors view">
<h2><?php echo __('Warehouse Vendor'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd><?php
        //echo $active;
        //pr($warehouseVendor['status']);die; ?>
			<?php echo h($warehouseVendor['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Vendor'); ?></dt>
		<dd>
			<?php echo h($warehouseVendor['vendor']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Vendor Email'); ?></dt>
		<dd>
			<?php echo h($warehouseVendor['vendor_email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Vendor Address 1'); ?></dt>
		<dd>
			<?php echo h($warehouseVendor['vendor_address_1']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Vendor Address 2'); ?></dt>
		<dd>
			<?php echo h($warehouseVendor['vendor_address_2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($warehouseVendor['zip']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Vendor Contact'); ?></dt>
		<dd>
			<?php echo h($warehouseVendor['vendor_contact']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Country'); ?></dt>
		<dd>
			<?php				
				echo $country[$warehouseVendor['country']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $active[$warehouseVendor['status']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($warehouseVendor['created'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($warehouseVendor['modified'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Warehouse <br/>Vendor'), array('action' => 'edit', $warehouseVendor['id']),array('escape'=>false)); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Warehouse <br/>Vendor'), array('action' => 'delete', $warehouseVendor['id']),array('escape'=>false) ,array(), __('Are you sure you want to delete # %s?', $warehouseVendor['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Warehouse <br/>Vendors'), array('action' => 'index'),array('escape'=>false)); ?> </li>
		<li><?php echo $this->Html->link(__('New Warehouse <br/>Vendor'), array('action' => 'add'),array('escape'=>false)); ?> </li>
	</ul>
</div>
