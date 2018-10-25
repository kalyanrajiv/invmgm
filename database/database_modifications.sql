-- For supporing additional model ids
ALTER TABLE `products` ADD `additional_model_id` TEXT NOT NULL AFTER `model`;

-- modified_by is changed in products table to int(11) from tinyint(4)

-- This field was missing in external sites
ALTER TABLE  `attachments` ADD  `sr_no` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `size` ;

-- For finding the difference between main site products and other sites
SELECT products_hfii . * FROM products_hfii LEFT JOIN products ON products_hfii.id = products.id WHERE products.id IS NULL 