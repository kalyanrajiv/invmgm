<div id='cssmenu'>
   <ul>
   <li><?php echo $this->Html->link(__('Repair'), array('controller' => 'mobile_repairs', 'action' => 'index')); ?>
	   <ul>
		 <li><?php echo $this->Html->link(__('Mobile Repairs'), array('controller' => 'mobile_repairs', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Mobile Repair Prices'), array('controller' => 'mobile_repair_prices', 'action' => 'index')); ?></li>
	   </ul>
   </li>
   <li><?php echo $this->Html->link(__('Brands'), array('controller' => 'brands', 'action' => 'index')); ?></li>
   <li><?php echo $this->Html->link(__('Mobile Models'), array('controller' => 'mobile_models', 'action' => 'index')); ?></li>
   
   <li><a href='/kiosk-orders/transient-orders'><span>Order</a></span> 
	  <ul>
		 <li><?php echo $this->Html->link(__('Transient Orders'), array('controller' => 'kiosk_orders','action' => 'transient_orders'));?></li>
		 <li><?php echo $this->Html->link(__('Place Order'), array('controller' => 'kiosk_orders', 'action' => 'initiate_order_placement')); ?>  </li>          
		 <li><?php echo $this->Html->link(__('Order Dispute'), array('controller' => 'stock_transfer', 'action' => 'disputed_orders'));?></li>
	  </ul>
   </li>
   
   <li class='last'><a href='/messages/inbox'><span>Misc</span></a>
	  <ul>
		 <li><?php echo $this->Html->link(__('Mail ('.$newEmailCount.')'), array('controller' => 'messages', 'action' => 'inbox')); ?></li>
	  </ul>
   </li>
   <?php echo $notificationsMenu;?>
   <li><a href='/stock'>Stock</a>
      <ul>
		 <li><?php echo $this->Html->link('Stock Transfer',array('controller'=>'stock_transfer','action'=>'stock_transfer_by_kiosk'))?></li>
		 <li><?php echo $this->Html->link('View Stock',array('controller'=>'stock','action'=>'index'))?></li>
		 <li><?php echo $this->Html->link('View Selling Price',array('controller'=>'stock','action'=>'view_selling_price'))?></li>
		 <li><?php echo $this->Html->link(__('View Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'view_faulty_products')); ?></li>
      </ul>
   </li>
   </ul>
</div>