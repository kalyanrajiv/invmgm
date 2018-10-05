<ul>
   <li><?php echo $this->Html->link(__('Add Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'add'),array('style'=>"width: 132px;")); ?></li>
   <li><?php echo $this->Html->link(__('Manage Kiosk <br/>Faulty Products'), array('controller' => 'defective_kiosk_products', 'action' => 'consolidate_faulty'),array('escape' => false,'style'=>"width: 132px;")); ?></li>
   <li><?php echo $this->Html->link(__('View/Receive Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'list_defective_references'),array('style'=>"width: 132px;")); ?></li>
   <li><?php echo $this->Html->link(__('Send for Replacement'), array('controller' => 'import_order_details', 'action' => 'index'),array('style'=>"width: 132px;")); ?></li>
   <li><?php echo $this->Html->link(__('View/Receive <br/> Replacements'), array('controller' => 'import_order_details', 'action' => 'import_orders_list'),array('escape' => false,'style'=>"width: 132px;")); ?></li>
   <li><?php echo $this->Html->link(__('Bin Value'), array('controller' => 'defective_kiosk_products', 'action' => 'faulty_bin_references'),array('style'=>"width: 132px;")); ?></li>
   <li><?php echo $this->Html->link(__('Bin Items'), array('controller' => 'defective_kiosk_products', 'action' => 'central_bin'),array('style'=>"width: 132px;")); ?></li>
   <li><?php echo $this->Html->link(__('View Faulty <br/>Received Items'), array('controller' => 'defective_kiosk_products', 'action' => 'all_faulty_products'),array('escape' => false,'style'=>"width: 132px;"));?></li>
   <li><?php echo $this->Html->link(__('Delete Faulty <br/>Received Items'), array('controller' => 'defective_kiosk_products', 'action' => 'delete_faulty_received'),array('escape' => false,'style'=>"width: 132px;"));?></li>
</ul>