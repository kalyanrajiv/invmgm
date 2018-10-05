<?php 
   $image = "new_blinking.gif";
?>
<div id='cssmenu'>
   <ul>
	  <li><?php echo $this->Html->link(__('Customers'), array('controller' => 'customers', 'action' => 'index')); ?></li>
	  <li><?php echo $this->Html->link(__('Stock'), array('controller' => 'stock', 'action' => 'index')); ?></span>
	  <ul>
		 <li><?php echo $this->Html->link(__('Stock Transfer'), array('controller' => 'stock_transfer', 'action' => 'stock_transfer_by_kiosk')); ?></li>
		 <li><?php echo $this->Html->link(__('View Stock'), array('controller' => 'stock', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('View Dead Stock'), array('controller' => 'stock', 'action' => 'view_dead_stock')); ?></li>
		 <li><?php echo $this->Html->link(__('Add Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'add')); ?></li>
		 <li><?php echo $this->Html->link(__('Faulty References'), array('controller' => 'defective_kiosk_products', 'action' => 'list_defective_references')); ?></li>
		 <li><?php echo $this->Html->link(__('View Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'view_faulty_products')); ?></li>
	  </ul>
	  </li>
	  <li><?php //echo $this->Html->link(__('New Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'new_order')); ?></li>
	  <li><a href='/mobile-re-sales'><span>Mobile</a></span>
		 <ul>
			<li><?php echo $this->Html->link(__('Transients Mobile'), array('controller' => 'mobile_purchases', 'action' => 'transient_mobiles'));?></li>
			<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller' => 'mobile_re_sales', 'action' => 'index')); ?></li>
			<li><?php echo $this->Html->link(__('View Bulk Mobile Sale'), array('controller' => 'mobile_blk_re_sales', 'action' => 'index')); ?></li>
			<li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('controller' => 'mobile_purchases', 'action' => 'add')); ?></li>
			<li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
			<li><?php echo $this->Html->link(__('Global Mobile Search'), array('controller' => 'mobile_purchases', 'action' => 'global_search')); ?></li>
			 <li><?php echo $this->Html->link(__('Stock by Reference'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'reference_number_listing')); ?></li>
		  </ul>
	  </li>
	  
	  <li><?php echo $this->Html->link(__('Repair'), array('controller' => 'mobile_repairs', 'action' => 'index')); ?></span>
		  <ul>
		   <li><?php echo $this->Html->link(__('Book Repair'), array('controller' => 'mobile_repairs', 'action' => 'add')); ?></li>
		   <li><?php echo $this->Html->link(__('View Repair Sales'), array('controller' => 'mobile_repair_sales', 'action' => 'view_repair_sales')); ?></li>	 
		  </ul>
	   </li>
	  
	  <li><?php echo $this->Html->link(__('Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?></span>
		 <ul>
			<li><?php echo $this->Html->link(__('Book Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'add')); ?></li>
			<li><?php echo $this->Html->link(__('View Unlocks sales'), array('controller' => 'mobile_unlock_sales', 'action' => 'view_unlock_sales')); ?></li>
		 </ul>
	   </li>
	  <li><a href='/kiosk-product-sales'><span>Report</a></span>
		 <ul>
			<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?></li>
			<li><?php echo $this->Html->link(__('Product Payments'), array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments')); ?></li>
			<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?></li>
			<li><?php echo $this->Html->link(__('View Performas'), array('controller' => 'invoice_orders', 'action' => 'index')); ?></li>
			<li><?php echo $this->Html->link(__('View Credit Notes'), array('controller' => 'credit_product_details', 'action' => 'view_credit_note')); ?></li>
			<li><?php echo $this->Html->link(__('View Dead stock'), array('controller' => 'stock', 'action' => 'view_dead_stock')); ?></li>
		 </ul>
	   </li>
   
	  <li><a href='/kiosk-orders/transient-orders'><span>Order</a></span>
		 <ul>
			<li><?php echo $this->Html->link(__('WH to Kiosk Transients'), array('controller' => 'kiosk_orders', 'action' => 'transient_orders'));?></li>
			<li><?php echo $this->Html->link(__('Kiosk to WH Transients'), array('controller' => 'kiosk_orders', 'action' => 'transient_kiosk_orders'));?></li>
			<li><?php echo $this->Html->link(__('WH to Kiosk Confirmed'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_orders'));?></li>
			<li><?php echo $this->Html->link(__('Kiosk to WH Confirmed'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_kiosk_orders'));?></li>
			<li><?php echo $this->Html->link(__('Transients Mobile'), array('controller' => 'mobile_purchases', 'action' => 'transient_mobiles'));?></li>
			<li> <?php echo $this->Html->link(__('Place Order'), array('controller' => 'kiosk_orders', 'action' => 'initiate-order-placement'));?></li>
			<li><?php echo $this->Html->link(__('Order Dispute'), array('controller' => 'stock_transfer', 'action' => 'disputed_orders'));?></li>
			<li><?php echo $this->Html->link(__('Search Global Orders'), array('controller' => 'kiosk_orders', 'action' => 'search_from_orders')); ?></li>
            <li><?php echo $this->Html->link(__('Extra Stock Required'), array('controller' => 'OnDemandOrders','action' => 'new_order')); ?></li>
			<li><?php echo $this->Html->link(__('New Product Demand'), array('controller' => 'messages', 'action' => 'product_demand')); ?></li>
		 </ul>
	  </li>
	 
	  <?php echo $notificationsMenu;?>
	 <?php echo "<li>".$this->Html->link(__('Products('.$total.')'), array('controller' => 'home', 'action' => 'new_products_notification'))."</li>";?>
   </ul>
</div>