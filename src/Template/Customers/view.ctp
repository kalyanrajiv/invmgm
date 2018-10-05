<div class="customers view">
<h2><?php echo __('Customer'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($customer->id); ?>
			&nbsp;
		</dd>
		<h4>Customer Details:</h4>
		<dt><?php echo __('Business'); ?></dt>
		<dd>
			<?php echo h($customer->business); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer Name'); ?></dt>
		<dd>
			<?php h($customer->fname)." ".h($customer->lname); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer Email'); ?></dt>
		<dd>
			<?php echo h($customer->email); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer Agent'); ?></dt>
		<dd>
			<?php echo h($agentName); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Date Of Birth'); ?></dt>
		<dd>
			<?php echo h($customer->date_of_birth);	 ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile'); ?></dt>
		<dd>
			<?php echo h($customer->mobile); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Landline'); ?></dt>
		<dd>
			<?php echo h($customer->landline); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Vat Number'); ?></dt>
		<dd>
			<?php echo h($customer->vat_number); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Imei'); ?></dt>
		<dd>
			<?php echo h($customer->imei); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address'); ?></dt>
		<dd>
			<?php echo  h($customer->address_1)."<br/>". h($customer->address_2); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('City'); ?></dt>
		<dd>
			<?php echo h($customer->city); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('County'); ?></dt>
		<dd>
			<?php echo h($customer->state); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($customer->zip); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Country'); ?></dt>
		<dd>
			<?php echo h($customer->country); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created By'); ?></dt>
		<dd>
			<?php
			if($customer->created_by == 0){
				echo"--";
			}
			else{
				echo h($users[$customer->created_by]);
			}?>
			&nbsp;
		</dd>
		<dt><?php echo __('Edited By'); ?></dt>
		<dd>
			<?php
			if($customer->edited_by == 0){
				echo"--";
			}else{
				echo h($users[$customer->edited_by]); 	
			}
			?>
			&nbsp;
		</dd>
		<h4>Delivery Address Details:</h4>
		<dt><?php echo __('Address'); ?></dt>
		<dd>
			<?php echo h($customer->del_address_1)."<br/>".h($customer->del_address_2); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('City'); ?></dt>
		<dd>
			<?php echo h($customer->del_city); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('County'); ?></dt>
		<dd>
			<?php echo h($customer->del_state) ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($customer->del_zip) ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h(date('d-m-Y h:i:s',strtotime($customer->created))); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo  h(date('d-m-Y h:i:s',strtotime($customer->modified))); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Sell'), array('controller'=>'kiosk_product_sales','action' => 'new_sale', $customer['Customer']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Create Performa'), array('controller'=>'invoice_order_details','action' => 'create_invoice', $customer['Customer']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Edit Customer'), array('action' => 'edit', $customer->id)); ?> </li>
		<li><?php #echo $this->Form->postLink(__('Delete Customer'), array('action' => 'delete', $customer['Customer']['id']), array(), __('Are you sure you want to delete # %s?', $customer['Customer']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Customers'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('action' => 'add')); ?> </li>
	</ul>
</div>
