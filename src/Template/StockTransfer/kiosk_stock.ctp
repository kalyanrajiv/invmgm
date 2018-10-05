<div class="users form">
	<?php #print_r($this->request); ?>
	<?php echo $this->Form->create(null,array('url' => array('controller' => 'stock_transfer','action' => 'view_stock'),'id' => 'ProductKioskStockForm')); ?>
	<fieldset>
	<legend><span><strong>Kiosk</strong><span style='color:red'><sup>*</sup></span></legend> <?php echo $this->Form->input(null, array(
									       'options' => $kiosks,
									       'label' => false,
									       'div' => false,
									       'name' => 'KioskStock[kiosk_id]',
									      // 'selected' => $kiosk_id,
										  'id' => 'Product',
									      'onChange'=>'select_change();',
									       'empty' => 'Select Kiosk'
									      
									       )
													      );?></span>
		
	</fieldset>
	<?php
	echo $this->Form->submit('Submit',array(
					'default' => 'boolean false'
						));
	echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('Kiosk Stock'), array('controller' => 'stock_transfer', 'action' => 'kiosk_stock')); ?> </li>
	</ul>
</div>

<script>
	
function select_change(){
var z = document.getElementById("Product").value;
var y = document.getElementById("ProductKioskStockForm").action;
var newAction = y+'/'+z;
document.getElementById("ProductKioskStockForm").action = newAction;
document.getElementById("ProductKioskStockForm").submit();
}

</script>