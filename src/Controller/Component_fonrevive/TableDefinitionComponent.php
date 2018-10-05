<?php
   namespace App\Controller\Component;
    use Cake\Controller\Component;
    use Cake\ORM\TableRegistry;
    use Cake\Network\Session;
    //usage e.g : $this->TableDefinition->get_table_defination('product_table',$kiosk_id);
    class TableDefinitionComponent extends Component {
        public function get_table_defination($tableName,$tableID) {
            switch($tableName){
                case 'product_table':
                    $productTable = "kiosk_{$tableID}_products";
                    $definition = "CREATE TABLE IF NOT EXISTS `$productTable` (
                                    `prefix` varchar(2) NOT NULL DEFAULT 'SD',
				    `id` int(11) unsigned NOT NULL,
				    `product` varchar(150) NOT NULL,
				    `quantity` int(11) unsigned NOT NULL,
				    `description` text NOT NULL,
				    `location` varchar(80) DEFAULT NULL,
				    `category_id` int(11) unsigned NOT NULL,
				    `cost_price` float(10,2) unsigned NOT NULL,
				    `lu_cp` datetime DEFAULT NULL,
				    `retail_cost_price` float(10,2) DEFAULT NULL,
				    `lu_rcp` datetime DEFAULT NULL,
				    `selling_price` float(10,2) NOT NULL,
				    `lu_sp` datetime DEFAULT NULL,
				    `retail_selling_price` float(10,2) DEFAULT NULL,
				    `lu_rsp` datetime DEFAULT NULL,
				    `brand_id` int(11) unsigned NOT NULL,
				    `model` varchar(100) NOT NULL,
				    `manufacturing_date` date NOT NULL,
				    `sku` int(11) unsigned NOT NULL,
				    `country_make` varchar(80) NOT NULL,
				    `product_code` varchar(50) NOT NULL,
				    `weight` float(8,2) DEFAULT NULL,
				    `color` varchar(30) NOT NULL,
				    `user_id` int(11) unsigned DEFAULT '1',
				    `featured` tinyint(4) unsigned DEFAULT '0',
				    `discount` int(11) DEFAULT NULL COMMENT 'In %',
				    `retail_discount` int(8) unsigned DEFAULT NULL,
				    `discount_status` tinyint(4) unsigned NOT NULL,
				    `rt_discount_status` tinyint(4) unsigned DEFAULT NULL COMMENT 'retail_discount_status',
				    `max_discount` tinyint(5) unsigned NOT NULL,
				    `min_discount` tinyint(5) unsigned NOT NULL,
				    `image_id` int(11) DEFAULT NULL,
				    `image` varchar(255) NOT NULL,
				    `image_dir` varchar(255) NOT NULL,
				    `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
				    `stock_level` int(11) NOT NULL,
				    `dead_stock_level` int(11) NOT NULL,
				    `status` tinyint(4) NOT NULL,
				    `created` datetime NOT NULL,
				    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				    PRIMARY KEY (`id`),
				    UNIQUE KEY `prefix` (`prefix`,`id`),
				    UNIQUE KEY `id` (`id`),
				    UNIQUE KEY `product_code` (`product_code`)
                                ) ENGINE=InnoDB";
                                break;
                	    
                case 'product_receipt_table':
                    $receiptTable = "kiosk_{$tableID}_product_receipts";
                    $definition = "CREATE TABLE IF NOT EXISTS `$receiptTable` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
						`retail_customer_id` int(11) unsigned NOT NULL,
					    `customer_id` int(11) unsigned NOT NULL,
					    `fname` varchar(70) NOT NULL,
					    `lname` varchar(70) NOT NULL,
					    `email` varchar(100) NOT NULL,
					    `mobile` varchar(15) NOT NULL,
					    `address_1` varchar(255) NOT NULL,
					    `address_2` varchar(255) NOT NULL,
					    `city` varchar(150) NOT NULL,
					    `state` varchar(150) NOT NULL,
					    `zip` varchar(12) NOT NULL,
						`bill_cost` FLOAT(10,2),
                        `orig_bill_amount` FLOAT(10,2),
                          `vat` int(10) unsigned NOT NULL,
					    `bill_amount` float(10,2) unsigned NOT NULL,
					    `bulk_discount` tinyint(5) unsigned DEFAULT NULL,
					    `processed_by` int(11) unsigned NOT NULL,
					    `status` tinyint(4) unsigned NOT NULL,
					    `created` datetime NOT NULL,
					    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					    PRIMARY KEY (`id`)
					  ) ENGINE=InnoDB";
                                break;
                
		//Note: Admin sales are being stored in kiosk_product_sales with a format having customer_id after product_id, different than below table
		
                case 'product_sale_table':
                    $saleTable = "kiosk_{$tableID}_product_sales";
                    $definition = "CREATE TABLE IF NOT EXISTS `$saleTable` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`kiosk_id` int(11) unsigned NOT NULL,
					`product_id` int(11) unsigned NOT NULL,
					`quantity` int(11) unsigned NOT NULL,
					`cost_price` FLOAT(10,3) NULL,
					`sale_price` float(10,2) unsigned NOT NULL,
					`refund_price` float(10,2) unsigned NOT NULL,
					`discount` TINYINT(5) DEFAULT NULL COMMENT 'In %',
					`discount_status` TINYINT( 4 ) UNSIGNED NOT NULL,					
					`refund_gain` float(10,2) unsigned NOT NULL,
					`sold_by` int(11) unsigned NOT NULL,
					`refund_by` INT( 11 ) UNSIGNED NOT NULL,
					`status` tinyint(4) unsigned NOT NULL DEFAULT '1',
					`refund_status` TINYINT( 4 ) UNSIGNED NOT NULL DEFAULT '0',
					`refund_remarks` VARCHAR( 255 ) NOT NULL,
					`product_receipt_id` int(11) unsigned NOT NULL,
					`remarks` varchar(255) NOT NULL,
					`created` datetime NOT NULL,
					`modified` datetime NOT NULL,
					PRIMARY KEY (`id`)
				      ) ENGINE=InnoDB";
                                break;
                case 'transferred_stock':
                    $stockTransferTable = "kiosk_transferred_stock_{$tableID}";
                    $definition = "CREATE TABLE IF NOT EXISTS `$stockTransferTable` (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`kiosk_order_id` int(11) unsigned NOT NULL,
							`product_id` int(11) unsigned NOT NULL,
							`quantity` int(11) unsigned NOT NULL,
							`sale_price` float(8,2) unsigned NOT NULL,
							`status` int(11) NOT NULL,
							`created` datetime NOT NULL,
							`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
							PRIMARY KEY (`id`)
							) ENGINE=InnoDB";
                                break;
		case 'payment_table':
		    $paymentTable = "kiosk_{$tableID}_payment_details";
		    $definition = "CREATE TABLE IF NOT EXISTS `$paymentTable` (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`invoice_order_id` int(11) unsigned NOT NULL,
							`product_receipt_id` int(11) unsigned NOT NULL,
							`payment_method` varchar(20) NOT NULL,
							`description` varchar(255) NOT NULL,
							`amount` float(8,2) unsigned NOT NULL,
							`payment_status` tinyint(4) unsigned NOT NULL,
							`status` tinyint(4) unsigned DEFAULT NULL,
							`created` datetime NOT NULL,
							`modified` datetime NOT NULL,
							PRIMARY KEY (`id`)
						      ) ENGINE=InnoDB";
				break;
		case 'invoice_table':
		    $invoiceTable = "kiosk_{$tableID}_invoice_orders";
		    $definition = "CREATE TABLE IF NOT EXISTS `$invoiceTable` (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`kiosk_id` int(11) unsigned NOT NULL,
							`user_id` int(11) unsigned NOT NULL,
							`customer_id` int(11) unsigned DEFAULT NULL,
							`fname` varchar(50) NOT NULL,
							`lname` varchar(50) NOT NULL,
							`email` varchar(50) NOT NULL,
							`mobile` varchar(12) NOT NULL,
							`bulk_discount` float(5,2) unsigned DEFAULT NULL,
							`del_city` varchar(50) NOT NULL,
							`del_state` varchar(50) NOT NULL,
							`del_zip` varchar(20) NOT NULL,
							`del_address_1` varchar(255) NOT NULL,
							`del_address_2` varchar(255) NOT NULL,
							`invoice_status` tinyint(4) unsigned NOT NULL,
							`status` tinyint(4) unsigned NOT NULL,
							`amount` float(10,2) unsigned NOT NULL,
							`created` datetime NOT NULL,
							`modified` datetime NOT NULL,
							PRIMARY KEY (`id`)
						      ) ENGINE=InnoDB";
				break;
		case 'invoice_details_table':
		    $invoiceDetailTable = "kiosk_{$tableID}_invoice_order_details";
		    $definition = "CREATE TABLE IF NOT EXISTS `$invoiceDetailTable` (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`kiosk_id` int(11) unsigned NOT NULL,
							`invoice_order_id` int(11) unsigned NOT NULL,
							`product_id` int(11) unsigned NOT NULL,
							`price` float(8,2) unsigned NOT NULL,
							`quantity` int(11) unsigned NOT NULL,
							`discount` tinyint(4) unsigned NOT NULL,
							`status` tinyint(4) unsigned NOT NULL,
							`created` datetime NOT NULL,
							`modified` datetime NOT NULL,
							PRIMARY KEY (`id`)
						      ) ENGINE=InnoDB";
				break;
		case 'credit_receipts_table':
		    $creditReceiptTable = "kiosk_{$tableID}_credit_receipts";
		    $definition = "CREATE TABLE IF NOT EXISTS `$creditReceiptTable` (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`customer_id` int(11) unsigned NOT NULL,
							`fname` varchar(70) DEFAULT NULL,
							`lname` varchar(70) DEFAULT NULL,
							`email` varchar(100) NOT NULL,
							`mobile` varchar(12) DEFAULT NULL,
							`address_1` varchar(255) DEFAULT NULL,
							`address_2` varchar(255) DEFAULT NULL,
							`city` varchar(150) DEFAULT NULL,
							`state` varchar(150) DEFAULT NULL,
							`zip` varchar(12) DEFAULT NULL,
							`credit_amount` float(10,2) unsigned NOT NULL,
							`bulk_discount` tinyint(5) unsigned NOT NULL,
							`processed_by` int(11) unsigned NOT NULL,
							`status` tinyint(4) unsigned NOT NULL,
							`created` datetime NOT NULL,
							`modified` datetime NOT NULL,
							PRIMARY KEY (`id`)
						      ) ENGINE=InnoDB";
				break;
		case 'credit_product_details_table':
		    $creditProductDetailTable = "kiosk_{$tableID}_credit_product_details";
		    $definition = "CREATE TABLE IF NOT EXISTS `$creditProductDetailTable` (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`kiosk_id` int(11) unsigned NOT NULL,
							`product_id` int(11) unsigned NOT NULL,
							`customer_id` int(11) unsigned NOT NULL,
							`quantity` int(11) unsigned NOT NULL,
							`sale_price` float(10,3) unsigned NOT NULL,
							`credit_price` float(10,2) unsigned NOT NULL,
							`discount` tinyint(5) unsigned NOT NULL,
							`credit_by` int(11) unsigned NOT NULL,
							`status` tinyint(4) unsigned NOT NULL DEFAULT '1',
							`credit_status` tinyint(4) unsigned NOT NULL DEFAULT '1',
							`credit_receipt_id` int(11) unsigned NOT NULL,
							`remarks` varchar(255) NOT NULL,
							`created` datetime NOT NULL,
							`modified` datetime NOT NULL,
							PRIMARY KEY (`id`)
						      ) ENGINE=InnoDB";
				break;
		case 'credit_payment_table':
		    $creditPaymentTable = "kiosk_{$tableID}_credit_payment_details";
		    $definition = "CREATE TABLE IF NOT EXISTS `$creditPaymentTable` (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`credit_receipt_id` int(11) unsigned NOT NULL,
							`payment_method` varchar(20) NOT NULL,
							`description` varchar(255) NOT NULL,
							`amount` float(8,2) unsigned NOT NULL,
							`payment_status` tinyint(4) unsigned NOT NULL,
							`status` tinyint(4) unsigned DEFAULT NULL,
							`created` datetime NOT NULL,
							`modified` datetime NOT NULL,
							PRIMARY KEY (`id`)
						      ) ENGINE=InnoDB";
				break;
		case 'daily_stock_table':
		    $dailyStockTable = "kiosk_{$tableID}_daily_stocks";
		    $definition = "CREATE TABLE IF NOT EXISTS `$dailyStockTable` (
							`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
							`product_id` int(11) unsigned NOT NULL,
							`cost_price` float(8,2) unsigned NOT NULL,
							`selling_price` float(8,2) unsigned NOT NULL,
							`quantity` int(11) unsigned NOT NULL,
							`status` tinyint(4) unsigned NOT NULL,
							`created` datetime NOT NULL,
							`modified` datetime NOT NULL,
							PRIMARY KEY (`id`)
						      ) ENGINE=InnoDB";
				break;
            }
            return $definition;
        }//end of function
    }
?>