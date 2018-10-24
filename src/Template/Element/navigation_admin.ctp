<div id='cssmenu'>
<ul>
   <li><?php echo $this->Html->link(__('Customers'), array('plugin' => null,'controller' => 'customers', 'action' => 'index')); ?>
	  <ul>
         <li><?php echo $this->Html->link(__("Customer's Data"), array('plugin' => null,'controller' => 'customers', 'action' => 'get_customers_data')); ?></li>
          <li><?php echo $this->Html->link(__("Account Manager"), array('plugin' => null,'controller' => 'agents', 'action' => 'index')); ?></li>
	  </ul>
   </li>
   <li class='active has-sub'><?php echo $this->Html->link(__('Users'), array('plugin' => null,'controller' => 'users', 'action' => 'index')); ?></a>
      <ul>
         <li><?php echo $this->Html->link(__('User Groups'), array('plugin' => null,'controller' => 'groups', 'action' => 'index')); ?></li>
		  <li><?php echo $this->Html->link(__('View Attendence'), array('plugin' => null,'controller' => 'user_attendances', 'action' => 'index')); ?></li>
		     <li><?php echo $this->Html->link(__('kioskwise Attendence'), array('plugin' => null,'controller' => 'user_attendances', 'action' => 'kioskwise_attendences')); ?></li>
		   <li><?php echo $this->Html->link(__('Datewise Attendence'), array('plugin' => null,'controller' => 'user_attendances', 'action' => 'datewise_attendences')); ?></li>
      </ul>
   </li>
   
   <li><?php echo $this->Html->link(__('Products'), array('plugin' => null,'controller' => 'products', 'action' => 'index')); ?></a>
      <ul>
		 <li><?php echo $this->Html->link(__('Brands'), array('plugin' => null,'controller' => 'brands', 'action' => 'index')); ?></li>
		  <li><?php echo $this->Html->link(__('Product Models'), array('plugin' => null,'controller' => 'product_models', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Product Categories'), array('plugin' => null,'controller' => 'categories', 'action' => 'index')); ?></li>
		   <li><?php echo $this->Html->link(__('Colors'), array('plugin' => null,'controller' => 'colors', 'action' => 'index')); ?></li>
		 <li class='has-sub'><a href='/defective-kiosk-products/add' id='faulty'><span>Faulty Module</span></a>
			<ul>
			   <li><?php echo $this->Html->link(__('Add Faulty'), array('plugin' => null,'controller' => 'defective_kiosk_products', 'action' => 'add')); ?></li>
			   <li><?php echo $this->Html->link(__('Manage Kiosk Faulty'), array('plugin' => null,'controller' => 'defective_kiosk_products', 'action' => 'consolidate_faulty')); ?></li>
			   <li><?php echo $this->Html->link(__('View/Receive Faulty'), array('plugin' => null,'controller' => 'defective_kiosk_products', 'action' => 'list_defective_references')); ?></li>
			   <li><?php echo $this->Html->link(__('View Faulty Received Items'), array('plugin' => null,'controller' => 'defective_kiosk_products', 'action' => 'all_faulty_products'));?></li>
			   <li><?php echo $this->Html->link(__('Delete Faulty Received Items'), array('plugin' => null,'controller' => 'defective_kiosk_products', 'action' => 'delete_faulty_received'));?></li>
			</ul>
		</li>
	 
	 
	 
	 
	 <li class='has-sub'><a href='/import-order-details' id='replace'><span>Replacements</span></a>
		 <ul>
			<li><?php echo $this->Html->link(__('Send for Replacement'), array('plugin' => null,'controller' => 'import_order_details', 'action' => 'index'));?></li>
	 <li><?php echo $this->Html->link(__('View/Receive Replacements'), array('plugin' => null,'controller' => 'import_order_details', 'action' => 'import_orders_list'));?></li>
		 </ul>
	 </li>
	 <li class='has-sub'><a href='/defective-kiosk-products/faulty-bin-references' id='bin'><span>Bin</span></a>
		 <ul>
			<li><?php echo $this->Html->link(__('Bin Value'), array('plugin' => null,'controller' => 'defective_kiosk_products', 'action' => 'faulty_bin_references'));?></li>
			<li><?php echo $this->Html->link(__('Bin Items'), array('plugin' => null,'controller' => 'defective_kiosk_products', 'action' => 'central_bin'));?></li>
		 </ul>
	 </li>
	 <?php
		 $path = dirname(__FILE__);
		 $isHpWaheguru = strpos($path, ADMIN_DOMAIN);
		 $ismbWaheguru = strpos($path,"mbwaheguru");
		 if($isHpWaheguru){
	 ?>
	 <li class='has-sub'><a href='products/import-products-2-db' id='import'><span>Import</span></a>
		 <ul>
         <li><?php echo $this->Html->link(__("**Import Products**"), array('plugin' => null,'controller' => 'products', 'action' => 'import_products'));?></li>
			<li><?php echo $this->Html->link(__('Import Products 2 DB'), array('plugin' => null,'controller' => 'products', 'action' => 'import_products_2_db'));?></li>
			<li><?php echo $this->Html->link(__('WareHouse Products'), array('plugin' => null,'controller' => 'products', 'action' => 'import_products'));?></li>
			<li><?php echo $this->Html->link(__('Kiosk Products'), array('plugin' => null,'controller' => 'products', 'action' => 'import_kiosk_products'));?></li>
		 </ul>
	 </li>
	 <?php
		 }
	 ?>
	 <li class='has-sub'><a href='/products/export-warehouse-products' id='export'><span>Export</span></a>
		 <ul>
			<li><?php echo $this->Html->link(__('WareHouse Products'), array('plugin' => null,'controller' => 'products', 'action' => 'export_warehouse_products'));?></li>
			<li><?php echo $this->Html->link(__('Kiosk Products'), array('plugin' => null,'controller' => 'products', 'action' => 'export_kiosk_products'));?></li>
		 </ul>
	 </li>
	 <li><a href="<?php echo URL_SCHEME.ADMIN_DOMAIN;?>/cpafter/cp_images_2_mbwaheguru.php" id="cpy_img" >Copy Imgs 2 Ram</a></li>
      </ul>
   </li>
   
   <li><?php echo $this->Html->link(__('Mobile'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'index')); ?> </span>
      <ul>
        <li><?php echo $this->Html->link(__('Mobile Prices'), array('plugin' => null,'controller' => 'mobile_prices', 'action' => 'index')); ?></li>
        <li><?php echo $this->Html->link(__('Mobile Repair Prices'), array('plugin' => null,'controller' => 'mobile_repair_prices', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Purchase Report'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'mobile_report')); ?></li>
        <li><?php echo $this->Html->link(__('View Mobile Sale'), array('plugin' => null,'controller' => 'mobile_re_sales', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('View Blk Mobile Sale'), array('plugin' => null,'controller' => 'mobile_blk_re_sales', 'action' => 'index')); ?></li>
        <li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'add')); ?></li>
        <li><?php echo $this->Html->link(__('Mobile Stock In'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'bulk_mobile_purchase')); ?></li>
        <li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
        <li><?php echo $this->Html->link(__('Global Mobile Search'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'global_search')); ?></li>
        <li><?php echo $this->Html->link(__('Mobile Models'), array('plugin' => null,'controller' => 'mobile_models', 'action' => 'index')); ?></li>
        <li><?php echo $this->Html->link(__('Transient Mobiles'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'transient_mobiles'));
           ?></li>
		 <li><?php echo $this->Html->link(__('Stock by Reference'), array('plugin' => null,'controller' => 'mobile_purchases', 'action' => 'reference_number_listing')); ?></li>
      </ul>
   </li>
   
   <li><?php echo $this->Html->link(__('Repair'), array('plugin' => null,'controller' => 'mobile_repairs', 'action' => 'index')); ?></span>
      <ul>
         <!--li><?php echo $this->Html->link(__('Book Repair'), array('plugin' => null,'controller' => 'mobile_repairs', 'action' => 'index')); ?></li-->
		 <li><?php echo $this->Html->link(__('Mobile Repair Prices'), array('plugin' => null,'controller' => 'mobile_repair_prices', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('View Repair Sales'), array('plugin' => null,'controller' => 'mobile_repair_sales', 'action' => 'view_repair_sales')); ?></li>
		 <li><?php echo $this->Html->link(__('Networks'), array('plugin' => null,'controller' => 'networks', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Problem Types'), array('plugin' => null,'controller' => 'problem_types', 'action' => 'index'));?></li>
		 <li><?php echo $this->Html->link(__('Mobile Conditions'), array('plugin' => null,'controller' => 'mobile_conditions', 'action' => 'index'));?></li>
		 <li><?php echo $this->Html->link(__('Function Tests'), array('plugin' => null,'controller' => 'function_conditions', 'action' => 'index'));?></li>
		 <li><?php echo $this->Html->link(__('MultiPart Report'), array('plugin' => null,'controller' => 'mobile_repairs', 'action' => 'multiple_repair_part_report'));?></li>
	  </ul>
   </li>
   
   <li><?php echo $this->Html->link(__('Unlock'), array('plugin' => null,'controller' => 'mobile_unlocks', 'action' => 'index')); ?></span>
      <ul>
         <!--li><?php echo $this->Html->link(__('Book Unlock'), array('plugin' => null,'controller' => 'mobile_unlocks', 'action' => 'index')); ?></li-->
		 <li><?php echo $this->Html->link(__('Unlocking Prices'), array('plugin' => null,'controller' => 'mobile_unlock_prices', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('View Unlock Sales'), array('plugin' => null,'controller' => 'mobile_unlock_sales', 'action' => 'view_unlock_sales')); ?></li>
		 <li><?php echo $this->Html->link(__('Networks'), array('plugin' => null,'controller' => 'networks', 'action' => 'index')); ?></li>
      </ul>
   </li>
   
   <li><?php echo $this->Html->link(__('Stock'), array('plugin' => null,'controller' => 'stock', 'action' => 'index')); ?></span>
      <ul>
		 <li><?php echo $this->Html->link(__('Stock (In/Out)'), array('plugin' => null,'controller' => 'warehouse_stocks', 'action' => 'index')); ?></li>
         <li><?php echo $this->Html->link(__('View stock in/out'), array('plugin' => null,'controller' => 'warehouse_stocks', 'action' => 'reference_stock')); ?></li>
		 <li><?php echo $this->Html->link(__('Stock Transfer'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Quick Stock Transfer'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'ajaxBaseTransfer')); ?></li>
		 <li><?php echo $this->Html->link(__('Stock Below Level'), array('plugin' => null,'controller' => 'stock', 'action' => 'stock_level')); ?></li>
		 <li><?php echo $this->Html->link(__('Warehouse Placed Orders'), array('plugin' => null,'controller' => 'stock', 'action' => 'view_stock_level')); ?></li>
		 <li><?php echo $this->Html->link(__('Stock Initializer'), array('plugin' => null,'controller' => 'stock_initializers', 'action' => 'add_to_kiosk')); ?></li>
		 <li><?php echo $this->Html->link(__('View Stock'), array('plugin' => null,'controller' => 'stock', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('View Kiosk Stock'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'view_stock')); ?></li>
		 <li><?php echo $this->Html->link(__('Stock Taking'), array('plugin' => null,'controller' => 'stock_initializers', 'action' => 'stock_taking')); ?></li>
		 <li><?php echo $this->Html->link(__('View Stock Taking'), array('plugin' => null,'controller' => 'stock_initializers', 'action' => 'stock_taking_reference_list')); ?></li>
		 <li><?php echo $this->Html->link(__('Kiosk Daily Stock'), array('plugin' => null,'controller' => 'stock', 'action' => 'kiosk_daily_stock')); ?></li>
		 <li><?php echo $this->Html->link(__('View Dead stock'), array('plugin' => null,'controller' => 'stock', 'action' => 'view_dead_stock')); ?></li>
		 <li><?php echo $this->Html->link(__('View Global Dead Stock'), array('plugin' => null,'controller' => 'stock', 'action' => 'combined_dead_products')); ?></li>
		 <li><?php echo $this->Html->link(__('View Transferred Stock'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'transferred_stock')); ?></li>
      </ul>
   </li>
   <li class='last'><a href='/kiosks' id='misc'><span>Misc</span></a>
	    <ul>
		<!-- <li class='has-sub'><a href='#'><span>Devices</span></a>
			<ul>
				<li><?php echo $this->Html->link(__('View Requests'), array('plugin' => null,'controller' => 'devices', 'action' => 'view_requests'));?></li>
				<li><?php echo $this->Html->link(__('Manage Devices'), array('plugin' => null,'controller' => 'devices', 'action' => 'list_devices'));?></li>
			</ul>
	    </li>-->
		 <li class='has-sub'><a href='/stock-transfer/lost-stock' id='stock'><span>Stock</span></a>
			<ul>
				<li><?php echo $this->Html->link(__('Lost Stock'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'lost_stock'));?></li>
				<li><?php echo $this->Html->link(__('Suspense Stock'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'suspense_stock'));?></li>
			</ul>
	    </li>
		 <li><?php echo $this->Html->link(__('Settings'), array('plugin' => null,'controller' => 'settings', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Sync All Kiosks'), array('plugin' => null,'controller' => 'stock_initializers', 'action' => 'sync_all_kiosks')); ?></li>
		 <li><?php echo $this->Html->link(__('Sync Single Kiosk'), array('plugin' => null,'controller' => 'stock_initializers', 'action' => 'sync_single_kiosk')); ?></li>
		 <li><?php echo $this->Html->link(__('Suppliers'), array('plugin' => null,'controller' => 'warehouse_vendors', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Reorder Levels'), array('plugin' => null,'controller' => 'reorder_levels', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Kiosks'), array('plugin' => null,'controller' => 'kiosks', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Posts'), array('plugin' => null,'controller' => 'posts', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('Faulty Conditions'), array('plugin' => null,'controller' => 'faulty_conditions', 'action' => 'index'));?></li>
		 <li><?php echo $this->Html->link(__('Screen Hints'), array('plugin' => null,'controller' => 'screen_hints', 'action' => 'index'));?></li>
		 <li><?php echo $this->Html->link(__('Troubleshoot K2W'), array('plugin' => null,'controller' => 'troubleshoot', 'action' => 'troubleshoot_products')); ?></li>
		 <li><?php echo $this->Html->link(__('Troubleshoot W2K'), array('plugin' => null,'controller' => 'troubleshoot', 'action' => 'troubleshoot_w2k')); ?></li>
		 <li><?php echo $this->Html->link(__('Placed orders Crone'), array('plugin' => null,'controller' => 'kiosk-orders', 'action' => 'placed-orders-crone')); ?></li>
		  
		  <?php if($this->request->session()->read('Auth.User.id') == 1){ ?>
		 <li><?php echo $this->Html->link(__('**Permissions**'), array('plugin' => null,'controller' => 'tests', 'action' => 'controllerPermissions')); ?></li>
		 <?php } ?>
		 <li><?php echo $this->Html->link(__('Delete Cache'), array('plugin' => null,'controller' => 'networks', 'action' => 'delete_cache_files')); ?></li>
	    </ul>
   </li>
   <?php echo $notificationsMenu; ?>
   <li><a href='/kiosk-orders/transient-orders' id='order'><span>Order</a></span>
      <ul>
         <li><?php echo $this->Html->link(__('WH to Kiosk Transients'), array('plugin' => null,'controller' => 'kiosk_orders', 'action' => 'transient_orders'));
            ?></li>
		 <li><?php echo $this->Html->link(__('Kiosk to WH Transients'), array('plugin' => null,'controller' => 'kiosk_orders', 'action' => 'transient_kiosk_orders'));
            ?></li>
		 <li><?php echo $this->Html->link(__('WH to Kiosk Confirmed'), array('plugin' => null,'controller' => 'kiosk_orders', 'action' => 'confirmed_orders'));
            ?></li>
		 <li><?php echo $this->Html->link(__('Kiosk to WH Confirmed'), array('plugin' => null,'controller' => 'kiosk_orders', 'action' => 'confirmed_kiosk_orders'));
            ?></li>
		 <li><?php echo $this->Html->link(__('Kiosk Placed Orders'), array('plugin' => null,'controller' => 'kiosk_orders', 'action' => 'placed_orders'));
            ?></li>
		 <li><?php echo $this->Html->link(__('Order Dispute'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'disputed_orders'));
            ?></li>
		 <li><?php echo $this->Html->link(__('Warehouse Placed Orders'), array('plugin' => null,'controller' => 'stock', 'action' => 'view_stock_level')); ?></li>
		 <li><?php echo $this->Html->link(__('Search Global Orders'), array('plugin' => null,'controller' => 'kiosk_orders', 'action' => 'search_from_orders')); ?></li>
		 <li><?php echo $this->Html->link(__('Dispatched Products'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'dispatched_products'));
            ?></li>
		  
      </ul>
   </li>
   
   <li><a href='/product-receipts/all-invoices' id='report'><span>Report</a></span>
      <ul>
		 <li class='has-sub'><a href='/kiosk-product-sales/all-kiosk-sale' id='sales'><span>Sales</span></a>
			<ul>
			   <li><?php echo $this->Html->link(__('All Kiosk Sale'), array('plugin' => null,'controller' => 'kiosk_product_sales', 'action' => 'all_kiosk_sale')); ?></li>
			   <li><?php echo $this->Html->link(__('All WholeSale Sale'), array('plugin' => null,'controller' => 'kiosk_product_sales', 'action' => 'all_wholesale_kiosk_sale')); ?></li>
			   <li><?php echo $this->Html->link(__('View Sales'), array('plugin' => null,'controller' => 'kiosk_product_sales', 'action' => 'index')); ?></li>
			    <li><?php echo $this->Html->link(__('Kiosk Sale Stat'), array('plugin' => null,'controller' => 'ProductSellStats', 'action' => 'index')); ?></li> 
			</ul> 
		</li>
	   
		 <!--<li><?php //echo $this->Html->link(__('View Stock Value'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'view_stock')); ?></li>-->
		 <li><?php echo $this->Html->link(__('View Invoices'), array('plugin' => null,'controller' => 'product_receipts', 'action' => 'all_invoices')); ?></li>
		 <li><?php echo $this->Html->link(__('Kiosk Product Payments'), array('plugin' => null,'controller' => 'product_receipts', 'action' => 'kiosk_product_payments')); ?></li>
		 <?php
		 if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			echo "<li>";
			echo $this->Html->link(__('Payment Amendments'), array('plugin' => null,'controller' => 'kiosk_product_sales', 'action' => 'search_sale_log'));
			echo "</li>";
		 }
		 ?>
		 <li><?php echo $this->Html->link(__('View Performas'), array('plugin' => null,'controller' => 'invoice_orders', 'action' => 'index')); ?></li>
		 <li><?php echo $this->Html->link(__('View Credit Notes'), array('plugin' => null,'controller' => 'credit_product_details', 'action' => 'view_credit_note')); ?></li>
		
		 <li><?php echo $this->Html->link(__('kiosk Bill'), array('plugin' => null,'controller' => 'stock_transfer', 'action' => 'summary_sale'));
            ?></li>
		 <li><?php echo $this->Html->link(__('Repair Report'), array('plugin' => null,'controller' => 'mobile_repairs', 'action' => 'repair_technician_report'));
            ?></li>
		 <li><?php echo $this->Html->link(__('Unlock Report'), array('plugin' => null,'controller' => 'mobile_unlocks', 'action' => 'unlock_technician_report'));
            ?></li>
		 <li><?php echo $this->Html->link(__('Datewise Kiosk Report'), array('plugin' => null,'controller' => 'home', 'action' => 'monthly_kiosk_sale_detail'));
            ?></li>
      </ul>
   </li>
   <li><a href='/daily-targets/user-sale-report' id='tgt'><span>Tgt</a></span>
	  <ul>
		 <li><?php echo $this->Html->link(__('User Sale Report'), array('plugin' => null,'controller' => 'daily_targets', 'action' => 'user_sale_report'));?></li>
		 <li><?php echo $this->Html->link(__('Kiosk sale Report'), array('plugin' => null,'controller' => 'daily_targets', 'action' => 'kiosk_sale_report'));?></li>
		 <li><?php echo $this->Html->link(__('Monthly Kiosk Sale Report'), array('plugin' => null,'controller' => 'daily_targets', 'action' => 'monthly_kiosk_sale_report'));?></li>
		 <li><?php echo $this->Html->link(__('Daily All Kiosk Sale'), array('plugin' => null,'controller' => 'daily_targets', 'action' => 'all'));?></li>
	  </ul>
   </li>
</ul>
</div>