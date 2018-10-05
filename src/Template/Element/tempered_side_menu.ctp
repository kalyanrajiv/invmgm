<ul>
   <li><?php echo $this->Html->link(__('View Quotation'), array('controller' => 'product_receipts', 'action' => 'dr_all_invoices')); ?></li>
   <li><?php echo $this->Html->link(__('View </br>Quotation Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'dr_index'),array('escape'=>false)); ?></li>
</ul>