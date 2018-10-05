<ul>
         <li><?php echo $this->Html->link(__('WH 2 Kiosk Trnsient Ordrs'), array('controller' => 'kiosk_orders', 'action' => 'transient_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('WH 2 Kiosk Confmd Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('Kiosk 2 WH Trnsient Ordrs'), array('controller' => 'kiosk_orders', 'action' => 'transient_kiosk_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('Kiosk 2 WH Confmd Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_kiosk_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('Disputed Orders'), array('controller' => 'stock_transfer','action' => 'disputed_orders'));?></li>
		<li><?php echo $this->Html->link(__('Kiosk Placed Orders'), array('controller' => 'kiosk_orders','action' => 'placed_orders'));?></li>
		<li><?php echo $this->Html->link(__('Warehouse Placed Orders'), array('controller' => 'stock','action' => 'view_stock_level'));?></li>
		<li><?php echo $this->Html->link(__('Search Global Orders'), array('controller' => 'kiosk_orders','action' => 'search_from_orders'));?></li>
		<li><?php echo $this->Html->link(__('Trash'), array('controller' => 'kiosk_orders','action' => 'trash')); ?></li>
		<li><?php echo $this->Html->link(__('On Demand'), array('controller' => 'kiosk_orders','action' => 'on_demand_placed_orders')); ?></li>
</ul>