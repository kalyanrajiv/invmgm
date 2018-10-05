<div class="warehouseVendors form">
<?php echo $this->Form->create('WarehouseVendor'); ?>
	<fieldset>
		<legend><?php echo __('Edit Warehouse Vendor'); ?></legend>
	<?php
		echo $this->Form->input('id',array('type' => 'hidden'));
		echo $this->Form->input('vendor');
		echo $this->Form->input('vendor_email');
		echo $this->Form->input('vendor_address_1');
		echo $this->Form->input('vendor_address_2');
		echo $this->Form->input('zip',array('label' => 'Postal Code'));
		echo $this->Form->input('vendor_contact');
		echo $this->Form->input('country', array('options' => $country,'empty' => 'Choose Country','default' => 'GB'));
		echo $this->Form->input('status',array('options' => $active));
	?>
	</fieldset>
    <?php echo $this->Form->submit('Submit',array('name'=>'submit')); ?>
<?php echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('WarehouseVendor.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('WarehouseVendor.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Warehouse <br/> Vendors'), array('action' => 'index'),['escape'=>false]); ?></li>
	</ul>
</div>
