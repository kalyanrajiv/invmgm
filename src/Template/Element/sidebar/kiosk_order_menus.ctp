 <ul>
		<li><?php echo $this->Html->link(__('WH 2 Kiosk </br>Trnsient Ordrs'), array('controller' => 'kiosk_orders', 'action' => 'transient_orders'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('WH 2 Kiosk <br/>Confmd Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_orders'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('Kiosk 2 WH <br/>Trnsient Ordrs'), array('controller' => 'kiosk_orders', 'action' => 'transient_kiosk_orders'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('Kiosk 2 WH <br/>Confmd Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_kiosk_orders'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('Disputed Orders'), array('controller' => 'stock_transfer', 'action' => 'disputed_orders'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('Placed Orders'), array('controller' => 'kiosk_orders','action' => 'placed_orders'));?></li>
		<li><?php echo $this->Html->link(__('Extra Stock <br/> Required'), array('controller' => 'kiosk_orders','action' => 'on_demand_placed_orders'),array('escape' => false)); ?></li>
		<li><?php #echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
	</ul> 
