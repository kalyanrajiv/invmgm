-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 25, 2018 at 10:41 AM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.1.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `hifi_master`
--

-- --------------------------------------------------------

--
-- Table structure for table `acos`
--

CREATE TABLE `acos` (
  `id` int(10) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `memo` text NOT NULL,
  `status` tinyint(5) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `agents`
--
DELIMITER $$
CREATE TRIGGER `add_agent` AFTER INSERT ON `agents` FOR EACH ROW BEGIN
INSERT INTO `hifiprofile_oc`.`oc_custom_field_value` (custom_field_value_id,custom_field_id,sort_order) VALUES (NEW.id,1,0);
INSERT INTO `hifiprofile_oc`.`oc_custom_field_value_description` (custom_field_value_id,language_id,custom_field_id,name) VALUES (NEW.id,1,1,NEW.name);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_agent` AFTER DELETE ON `agents` FOR EACH ROW BEGIN
DELETE FROM `hifiprofile_oc`.`oc_custom_field_value` WHERE custom_field_value_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_custom_field_value_description` WHERE custom_field_value_id=OLD.id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_agent` AFTER UPDATE ON `agents` FOR EACH ROW BEGIN
UPDATE `hifiprofile_oc`.`oc_custom_field_value` SET custom_field_id = 1, sort_order=0 WHERE custom_field_value_id = NEW.id;
UPDATE `hifiprofile_oc`.`oc_custom_field_value_description` SET language_id = 1, custom_field_id = 1, name = NEW.name WHERE custom_field_value_id = NEW.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `aros`
--

CREATE TABLE `aros` (
  `id` int(10) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `aros_acos`
--

CREATE TABLE `aros_acos` (
  `id` int(10) NOT NULL,
  `aro_id` int(10) NOT NULL,
  `aco_id` int(10) NOT NULL,
  `_create` varchar(2) NOT NULL DEFAULT '0',
  `_read` varchar(2) NOT NULL DEFAULT '0',
  `_update` varchar(2) NOT NULL DEFAULT '0',
  `_delete` varchar(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `model` varchar(20) NOT NULL,
  `foreign_key` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `dir` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `size` int(11) DEFAULT '0',
  `sr_no` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(10) UNSIGNED NOT NULL,
  `brand` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `brands`
--
DELIMITER $$
CREATE TRIGGER `add_brand` AFTER INSERT ON `brands` FOR EACH ROW BEGIN
INSERT INTO `hifiprofile_oc`.`oc_manufacturer` (
manufacturer_id,name,image,sort_order
)
VALUES ( NEW.id , NEW.brand , '' ,0 );
INSERT INTO `hifiprofile_oc`.`oc_manufacturer_to_store`(
manufacturer_id,store_id)     
VALUES (NEW.id , 0);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_brand` AFTER DELETE ON `brands` FOR EACH ROW BEGIN
DELETE FROM `hifiprofile_oc`.`oc_manufacturer` WHERE manufacturer_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_manufacturer_to_store` WHERE manufacturer_id=OLD.id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_brand` AFTER UPDATE ON `brands` FOR EACH ROW BEGIN UPDATE 
`hifiprofile_oc`.`oc_manufacturer` SET name=NEW.brand,image='',sort_order=0 WHERE 
manufacturer_id=NEW.id; UPDATE `hifiprofile_oc`.`oc_manufacturer_to_store` SET store_id=0 WHERE 
manufacturer_id=NEW.id; 
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `id_name_path` text NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_dir` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `top` tinyint(1) NOT NULL,
  `column` int(3) NOT NULL,
  `sort_order` int(3) DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `categories`
--
DELIMITER $$
CREATE TRIGGER `add_category` AFTER INSERT ON `categories` FOR EACH ROW BEGIN
INSERT INTO `hifiprofile_oc`.`oc_category` ( category_id,image,parent_id,top,sort_order,status,date_added,date_modified)VALUES (NEW.id,NEW.image,NEW.parent_id,0,0,NEW.status,NEW.created,NEW.modified);
INSERT INTO `hifiprofile_oc`.`oc_category_description` (category_id,language_id,name,description,meta_title,meta_description,meta_keyword)VALUES (NEW.id,1,NEW.category,NEW.description,NEW.category,'','');
INSERT INTO `hifiprofile_oc`.`oc_category_to_store` ( category_id,store_id)VALUES (NEW.id,0);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_category` AFTER DELETE ON `categories` FOR EACH ROW BEGIN
DELETE FROM `hifiprofile_oc`.`oc_category` WHERE category_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_category_description` WHERE category_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_category_to_store` WHERE category_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_category_path` WHERE category_id=OLD.id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_category` AFTER UPDATE ON `categories` FOR EACH ROW BEGIN
UPDATE `hifiprofile_oc`.`oc_category` SET
image=NEW.image,parent_id=NEW.parent_id,top=0,sort_order=0,status=NEW.status,date_modified=NEW.modified WHERE category_id=NEW.id;
UPDATE `hifiprofile_oc`.`oc_category_description` SET
language_id=1,name=NEW.category,description=NEW.description,meta_title=NEW.category,meta_description='',meta_keyword='' WHERE category_id=NEW.id;
UPDATE `hifiprofile_oc`.`oc_category_to_store` SET
store_id=0 WHERE category_id=NEW.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories_tmp`
--

CREATE TABLE `categories_tmp` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `id_name_path` text NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_dir` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `top` tinyint(1) NOT NULL,
  `column` int(3) NOT NULL,
  `sort_order` int(3) DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `center_orders`
--

CREATE TABLE `center_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1:transient|2:confirmed',
  `dispatched_on` datetime NOT NULL,
  `received_on` datetime NOT NULL,
  `received_by` int(11) UNSIGNED NOT NULL COMMENT 'user_id of logged in users',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(45) NOT NULL DEFAULT '',
  `name` varchar(20) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `colors`
--
DELIMITER $$
CREATE TRIGGER `update_` AFTER UPDATE ON `colors` FOR EACH ROW BEGIN
    declare optionID integer;
        SELECT option_id FROM hifiprofile_oc.oc_option_description WHERE `name` = 'color' INTO @optionID; 
        UPDATE hifiprofile_oc.oc_option_value SET
                                            option_id  =  @optionID  ,
                                            sort_order  = NEW.id
                                            WHERE  option_value_id  =  NEW.id;
         
        UPDATE  hifiprofile_oc.oc_option_value_description  SET
                                                            name  =   NEW.name ,        
                                                            language_id  = '1',
                                                            option_id  = '15'
                                                            WHERE  option_value_id  = NEW.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `color_log`
--

CREATE TABLE `color_log` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(255) NOT NULL,
  `old_color` varchar(255) NOT NULL,
  `new_color` varchar(255) NOT NULL,
  `mail_sent` tinyint(4) DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `post_id` int(11) UNSIGNED NOT NULL,
  `comments` text NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comment_mobile_purchases`
--

CREATE TABLE `comment_mobile_purchases` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_purchase_id` int(11) UNSIGNED NOT NULL,
  `comments` text NOT NULL,
  `admin_remarks` text NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comment_mobile_repairs`
--

CREATE TABLE `comment_mobile_repairs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_repair_id` int(11) UNSIGNED NOT NULL,
  `brief_history` text NOT NULL,
  `admin_remarks` text NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comment_mobile_re_sales`
--

CREATE TABLE `comment_mobile_re_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_re_sale_id` int(11) UNSIGNED NOT NULL,
  `comments` text NOT NULL,
  `admin_remarks` text NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comment_mobile_unlocks`
--

CREATE TABLE `comment_mobile_unlocks` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_unlock_id` int(11) UNSIGNED NOT NULL,
  `brief_history` text NOT NULL,
  `admin_remarks` text NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `credit_payment_details`
--

CREATE TABLE `credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `kiosk_id` tinyint(4) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `credit_product_details`
--

CREATE TABLE `credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) DEFAULT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `credit_receipts`
--

CREATE TABLE `credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `csv_products`
--

CREATE TABLE `csv_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model` varchar(100) NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'retail_discount_status',
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED NOT NULL COMMENT '0:special offer disabled, 1:enabled',
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED NOT NULL COMMENT '0:special offer disabled, 1:enabled',
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` tinyint(4) NOT NULL,
  `last_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED DEFAULT '0' COMMENT '0 for main website',
  `agent_id` tinyint(3) UNSIGNED NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT '0',
  `edited_by` int(11) NOT NULL,
  `business` varchar(150) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `vat_number` varchar(25) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `landline` varchar(15) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `country` tinytext NOT NULL,
  `zip` varchar(20) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `same_delivery_address` tinyint(4) UNSIGNED NOT NULL,
  `imei` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `memo` text NOT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(20) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `system_user` tinyint(5) NOT NULL DEFAULT '0' COMMENT 'use for dr5 special sale. system_user =1',
  `modified` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `customers`
--
DELIMITER $$
CREATE TRIGGER `add_customer` AFTER INSERT ON `customers` FOR EACH ROW BEGIN
DECLARE country_id integer;
DECLARE ID integer;
DECLARE oc_address_id integer;
DECLARE cust_grp_id varchar(255);
DECLARE custom_field varchar(255);
DECLARE state_id integer;
DECLARE del_state_id integer;
SELECT `customer_id` FROM `hifiprofile_oc`.`oc_customer` WHERE `customer_id`=NEW.id INTO @ID;
IF (ISNULL(@ID)) THEN
    IF(NEW.country = 'GB') THEN
        SET @country_id:=222;
        SET @cust_grp_id:=1;
    ELSE
        SET @country_id:=258;
        SET @cust_grp_id:=2;
    END IF;
    
    IF (NEW.state = '') THEN
        SET @state_id:=99;
    ELSE
        SELECT `zone_id` FROM `hifiprofile_oc`.`oc_zone` WHERE `name`=NEW.state INTO @state_id;
    END IF;
        
        IF (ISNULL(@state_id)) THEN
        SET @state_id:=99;
        END IF;
    SET @custom_field:=CONCAT('{"1":"',NEW.agent_id,'"}');
    INSERT INTO `hifiprofile_oc`.`oc_address` (
    customer_id,firstname,lastname,company,address_1,address_2,city,postcode,country_id ,zone_id,custom_field
    )
    VALUES (NEW.id,NEW.fname,NEW.lname,NEW.business,NEW.address_1,NEW.address_2,NEW.city,NEW.zip,@country_id,@state_id,'');
    
    SELECT `address_id` FROM `hifiprofile_oc`.`oc_address` WHERE `customer_id`=NEW.id ORDER BY address_id DESC LIMIT 1 INTO @oc_address_id;
    
    INSERT INTO `hifiprofile_oc`.`oc_customer` (
    customer_id,customer_group_id,store_id,language_id,firstname,lastname,email,telephone,fax,password,salt, cart, wishlist,newsletter,address_id,custom_field,ip,status,safe,token,code,date_added
    )
    VALUES (NEW.id,@cust_grp_id,0,1,NEW.fname,NEW.lname,NEW.email,NEW.mobile,'','25d55ad283aa400af464c76d713c07ad','','','','',@oc_address_id,@custom_field,'',NEW.status,0,'','',NEW.created);
    
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_customer` AFTER DELETE ON `customers` FOR EACH ROW BEGIN
DELETE FROM `hifiprofile_oc`.`oc_customer` WHERE customer_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_address` WHERE customer_id=OLD.id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_customer` AFTER UPDATE ON `customers` FOR EACH ROW BEGIN
DECLARE country_id integer;
DECLARE cust_grp_id varchar(255);
DECLARE custom_field varchar(255);
DECLARE oc_address_id integer;
DECLARE oc_del_address_id integer;
DECLARE state_id integer;
DECLARE del_state_id integer;
IF (NEW.status <> OLD.status AND NEW.status = 1) THEN
    DELETE FROM `hifiprofile_oc`.`oc_customer_approval` WHERE `customer_id`=NEW.id;
END IF;
IF(NEW.country = 'GB') THEN
SET @country_id:=222;
SET @cust_grp_id:=1;
ELSE
SET @country_id:=258;
SET @cust_grp_id:=2;
END IF;
SET @custom_field:= CONCAT('{"1":"',NEW.agent_id,'"}');
UPDATE `hifiprofile_oc`.`oc_customer` SET
customer_group_id=@cust_grp_id,store_id=0,language_id=1,firstname=NEW.fname,lastname=NEW.lname,email=NEW.email,telephone=NEW.mobile,fax='',cart='', wishlist='',newsletter='',custom_field=@custom_field,ip='',status=NEW.status,safe=0,token='',code='' WHERE customer_id= OLD.id;
   IF (NEW.state = '') THEN
       SET @state_id:=99;
   ELSE
       SELECT `zone_id` FROM `hifiprofile_oc`.`oc_zone` WHERE `name`=NEW.state INTO @state_id;
   END IF;
       
       IF (ISNULL(@state_id)) THEN
       SET @state_id:=99;
       END IF;
   
   IF (NEW.del_state = '') THEN
       SET @del_state_id:=99;
   ELSE
       SELECT `zone_id` FROM `hifiprofile_oc`.`oc_zone` WHERE `name`=NEW.state INTO @del_state_id;
   END IF;
       
       IF (ISNULL(@del_state_id)) THEN
       SET @del_state_id:=99;
       END IF;
   IF (NEW.same_delivery_address = 0) THEN
       SELECT `address_id` FROM `hifiprofile_oc`.`oc_address` WHERE `customer_id`=OLD.id ORDER BY `address_id` ASC LIMIT 1 INTO @oc_address_id;
       
       SELECT `address_id` FROM `hifiprofile_oc`.`oc_address` WHERE `customer_id`=OLD.id ORDER BY `address_id` DESC LIMIT 1 INTO @oc_del_address_id;
       
       UPDATE `hifiprofile_oc`.`oc_address` SET
       firstname=NEW.fname,lastname=NEW.lname,company=NEW.business,address_1=NEW.address_1,address_2=NEW.address_2, city=NEW.city,postcode=NEW.zip,country_id=@country_id,zone_id=@state_id,custom_field='' WHERE customer_id=OLD.id AND `address_id`=@oc_address_id;
       
       UPDATE `hifiprofile_oc`.`oc_address` SET
       firstname=NEW.fname,lastname=NEW.lname,company=NEW.business,address_1=NEW.del_address_1,address_2=NEW.del_address_2, city=NEW.del_city,postcode=NEW.del_zip,country_id=@country_id,zone_id=@del_state_id,custom_field='' WHERE customer_id=OLD.id AND `address_id`=@oc_del_address_id;    
   ELSE
       SELECT `address_id` FROM `hifiprofile_oc`.`oc_address` WHERE `customer_id`=OLD.id ORDER BY `address_id` ASC LIMIT 1 INTO @oc_address_id;
       UPDATE `hifiprofile_oc`.`oc_address` SET
       firstname=NEW.fname,lastname=NEW.lname,company=NEW.business,address_1=NEW.address_1,address_2=NEW.address_2, city=NEW.city,postcode=NEW.zip,country_id=@country_id,zone_id=@state_id,custom_field='' WHERE customer_id=OLD.id AND `address_id`=@oc_address_id;    
   END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_product_price`
--

CREATE TABLE `customer_product_price` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sale_price` float(10,6) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `daily_stocks`
--

CREATE TABLE `daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL COMMENT '0: sold or modified today; 1: not modified',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `daily_targets`
--

CREATE TABLE `daily_targets` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `target` float(10,2) UNSIGNED DEFAULT '0.00',
  `product_sale` float(9,2) UNSIGNED DEFAULT '0.00',
  `mobile_sale` float(9,2) UNSIGNED DEFAULT '0.00',
  `mobile_blk_sale` float(10,2) UNSIGNED DEFAULT NULL,
  `mobile_repair_sale` float(9,2) UNSIGNED DEFAULT '0.00',
  `mobile_unlock_sale` float(9,2) UNSIGNED DEFAULT '0.00',
  `product_refund` float(9,2) UNSIGNED DEFAULT '0.00',
  `mobile_refund` float(9,2) UNSIGNED DEFAULT '0.00',
  `mobile_blk_refund` float(10,2) UNSIGNED DEFAULT NULL,
  `mobile_repair_refund` float(9,2) UNSIGNED DEFAULT '0.00',
  `mobile_unlock_refund` float(9,2) UNSIGNED DEFAULT '0.00',
  `total_sale` float(10,2) UNSIGNED DEFAULT '0.00',
  `total_refund` float(10,2) UNSIGNED DEFAULT '0.00',
  `target_date` date NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_data`
--

CREATE TABLE `dashboard_data` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `user_type` varchar(255) NOT NULL COMMENT 'Normal,Special',
  `repair_sale` varchar(255) DEFAULT NULL,
  `repair_sale_desc` text,
  `repair_refund` varchar(255) DEFAULT NULL,
  `repair_refund_desc` text,
  `unlock_sale` varchar(255) DEFAULT NULL,
  `unlock_sale_desc` text,
  `unlock_refund` varchar(11) DEFAULT NULL,
  `unlock_refund_desc` text,
  `product_sale` varchar(255) DEFAULT NULL,
  `product_sale_desc` text,
  `quotation` varchar(255) DEFAULT NULL,
  `quotation_desc` text,
  `credit_note` varchar(255) DEFAULT NULL,
  `credit_note_desc` text,
  `credit_quotation` varchar(255) DEFAULT NULL,
  `credit_quotation_desc` text,
  `product_refund` varchar(255) DEFAULT NULL,
  `product_refund_desc` text,
  `bulk_mobile_sale` varchar(255) DEFAULT NULL,
  `bulk_mobile_sale_desc` text,
  `bulk_mobile_refund` varchar(255) DEFAULT NULL,
  `bulk_mobile_refund_desc` text,
  `mobile_sale` varchar(255) DEFAULT NULL,
  `mobile_sale_desc` text,
  `mobile_purchase` varchar(255) DEFAULT NULL,
  `mobile_purchase_desc` text,
  `mobile_refund` varchar(255) DEFAULT NULL,
  `mobile_refund_desc` text,
  `total_sale` varchar(255) DEFAULT NULL,
  `total_sale_desc` text,
  `total_refund` varchar(255) DEFAULT NULL,
  `total_refund_desc` text,
  `net_sale` varchar(255) DEFAULT NULL,
  `net_sale_desc` text,
  `net_card` varchar(255) DEFAULT NULL,
  `net_card_desc` text,
  `net_credit` varchar(255) DEFAULT NULL,
  `net_credit_desc` text,
  `net_bnk_tnsfer` varchar(255) DEFAULT NULL,
  `net_bnk_tnsfer_desc` text,
  `net_cheque_payment` varchar(255) DEFAULT NULL,
  `net_cheque_payment_desc` text,
  `cash_in_hand` varchar(255) DEFAULT NULL,
  `cash_in_hand_desc` text,
  `credit_to_cash` varchar(255) DEFAULT NULL,
  `credit_to_cash_desc` text,
  `credit_to_other_payment` varchar(255) DEFAULT NULL,
  `credit_to_other_payment_desc` text,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dead_products`
--

CREATE TABLE `dead_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kiosk_id` int(10) UNSIGNED NOT NULL,
  `products` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `defective_bin`
--

CREATE TABLE `defective_bin` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT 'no relevance as of now',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `defective_bin_references`
--

CREATE TABLE `defective_bin_references` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT 'admin''s user id',
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT 'no relevance, as of now',
  `total_cost` float UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `reference` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `defective_bin_transients`
--

CREATE TABLE `defective_bin_transients` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `defective_bin_reference_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `single_product_cost` float UNSIGNED NOT NULL,
  `total_product_cost` float UNSIGNED NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `defective_central_products`
--

CREATE TABLE `defective_central_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `original_quantity` int(11) UNSIGNED NOT NULL,
  `sent_quantity` int(11) UNSIGNED NOT NULL,
  `remaining_quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT 'no relevance',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `defective_kiosk_products`
--

CREATE TABLE `defective_kiosk_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT 'Fault marked By Kiosk User',
  `received_by` int(11) UNSIGNED DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `defective_kiosk_reference_id` int(10) UNSIGNED DEFAULT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT '0 = not moved, 1 = moved to central_faulty_products table',
  `remarks` varchar(255) NOT NULL,
  `receive_date` datetime DEFAULT NULL,
  `date_of_movement` datetime NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table will store initially all faulty stock of warehous';

-- --------------------------------------------------------

--
-- Table structure for table `defective_kiosk_references`
--

CREATE TABLE `defective_kiosk_references` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT 'user id of user creating the reference',
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT '0 = transient, 1 = received',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `reference` varchar(100) NOT NULL,
  `date_of_receiving` datetime NOT NULL,
  `received_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `defective_kiosk_transients`
--

CREATE TABLE `defective_kiosk_transients` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `defective_kiosk_reference_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT 'admin''s user id',
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT '0 = transient, 1 = fully received',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) UNSIGNED NOT NULL,
  `device_name` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1: active device, 0: inactive',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `device_activation_requests`
--

CREATE TABLE `device_activation_requests` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `kiosk_name` varchar(20) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `device_cookie` varchar(100) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1: active, 0:inactive',
  `type` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:new request, 1:update inactive to active',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `device_cookie_details`
--

CREATE TABLE `device_cookie_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `device_cookie` varchar(100) NOT NULL,
  `device_id` int(11) UNSIGNED NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `logged_in` tinyint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:logged in, 0: not logged in',
  `description` varchar(255) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1: active device, 0: inactive',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `device_login_logs`
--

CREATE TABLE `device_login_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `device_id` int(11) UNSIGNED NOT NULL,
  `device_cookie` varchar(100) NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `login_time` time NOT NULL,
  `log_out_time` time NOT NULL,
  `location` varchar(100) NOT NULL,
  `longitude` varchar(100) NOT NULL,
  `lattitude` varchar(100) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `faulty_conditions`
--

CREATE TABLE `faulty_conditions` (
  `id` int(11) NOT NULL,
  `faulty_condition` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `internal_purpose` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `internal_key` tinyint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `faulty_products`
--

CREATE TABLE `faulty_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `receipt_id` int(11) UNSIGNED NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL,
  `remarks` text,
  `admin_remarks` text,
  `type` tinyint(5) UNSIGNED DEFAULT NULL,
  `discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='faulty_returned_products';

-- --------------------------------------------------------

--
-- Table structure for table `faulty_product_details`
--

CREATE TABLE `faulty_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL,
  `discount` tinyint(5) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fone_products`
--

CREATE TABLE `fone_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model` varchar(100) NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'retail_discount_status',
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED NOT NULL COMMENT '0:special offer disabled, 1:enabled',
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED NOT NULL COMMENT '0:special offer disabled, 1:enabled',
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` tinyint(4) NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=REDUNDANT;

-- --------------------------------------------------------

--
-- Table structure for table `function_conditions`
--

CREATE TABLE `function_conditions` (
  `id` int(10) UNSIGNED NOT NULL,
  `function_condition` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(11) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `import_order_details`
--

CREATE TABLE `import_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `quantity_received` int(11) UNSIGNED NOT NULL,
  `import_order_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `import_order_references`
--

CREATE TABLE `import_order_references` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `reference` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL DEFAULT 'CHN',
  `status` tinyint(5) UNSIGNED NOT NULL,
  `received_date` date NOT NULL,
  `received_by` int(11) UNSIGNED NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_orders`
--

CREATE TABLE `invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `order_site` tinyint(4) NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `invoice_orders`
--
DELIMITER $$
CREATE TRIGGER `delete_order` AFTER DELETE ON `invoice_orders` FOR EACH ROW BEGIN
        DELETE FROM `hifiprofile_oc`.`oc_order` WHERE `order_id`=OLD.id;
        DELETE FROM `hifiprofile_oc`.`oc_order_product` WHERE `order_id`=OLD.id;
        DELETE FROM `hifiprofile_oc`.`oc_order_history` WHERE `order_id`=OLD.id;
        DELETE FROM `hifiprofile_oc`.`oc_order_total` WHERE `order_id`=OLD.id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_static` AFTER INSERT ON `invoice_orders` FOR EACH ROW BEGIN
    DECLARE tbl_id integer;
    DECLARE vat_applied integer;
    DECLARE final_amt decimal(10,4);
    DECLARE vat decimal(10,4);
    DECLARE vat_value decimal(10,4);
    DECLARE sub_total decimal(10,4);
    DECLARE mult_val decimal(10,4);
    DECLARE divd_val decimal(10,4);

    DECLARE agent_field varchar(255);
    DECLARE company varchar(255);
    DECLARE hp_city varchar(255);
    DECLARE hp_state varchar(255);
    DECLARE hp_zip varchar(255);
    DECLARE hp_country varchar(255);
    DECLARE hp_address_1 varchar(255);
    DECLARE hp_address_2 varchar(255);
    DECLARE hp_same_delivery_address varchar(255);
    DECLARE hp_del_city varchar(255);
    DECLARE hp_del_state varchar(255);
    DECLARE hp_del_zip varchar(255);
    DECLARE hp_del_address_1 varchar(255);
    DECLARE hp_del_address_2 varchar(255);
    DECLARE oc_city varchar(255);
    DECLARE oc_state varchar(255);
    DECLARE oc_zip varchar(255);
    DECLARE oc_address_1 varchar(255);
    DECLARE oc_address_2 varchar(255);
    DECLARE oc_country varchar(255);
    DECLARE oc_country_id integer;
    DECLARE oc_customer_grp_id integer;
    DECLARE oc_zone_id integer;
    DECLARE oc_p_zone_id integer;

    SELECT order_id FROM `hifiprofile_oc`.`oc_order` WHERE order_id = NEW.id INTO @tbl_id;
    IF(ISNULL(@tbl_id)) THEN
   SELECT `custom_field` FROM `hifiprofile_oc`.`oc_customer` WHERE `customer_id`=NEW.id INTO @agent_field;
    SET @oc_zone_id := 0;
    SELECT `zone_id` FROM `hifiprofile_oc`.`oc_zone` WHERE `name`=NEW.del_state limit 1,1 INTO @oc_zone_id;
    
    SELECT `business`,`city`,`state`,`zip`,`country`,`address_1`,`address_2`,`same_delivery_address`,`del_city`,`del_state`,`del_zip`,`del_address_1`,`del_address_2` FROM `hifi_master`.`customers` WHERE `id`=NEW.customer_id INTO @company,@hp_city,@hp_state,@hp_zip,@hp_country,@hp_address_1,@hp_address_2,@hp_same_delivery_address,@hp_del_city,@hp_del_state,@hp_del_zip,@hp_del_address_1,@hp_del_address_2;
    SET @oc_p_zone_id := 0;
    IF(@hp_same_delivery_address=1) THEN
        SET @oc_city:= @hp_city;
        SET @oc_state:= @hp_state;
        SET @oc_zip:= @hp_zip;
        SET @oc_address_1:= @hp_address_1;
        SET @oc_address_2:= @hp_address_2;
    ELSE
        SET @oc_city:= @hp_del_city;
        SET @oc_state:= @hp_del_state;
        SET @oc_zip:= @hp_del_zip;
        SET @oc_address_1:= @hp_del_address_1;
        SET @oc_address_2:= @hp_del_address_2;
                
        SELECT `zone_id` FROM `hifiprofile_oc`.`oc_zone` WHERE `name`=@oc_state limit 1,1 INTO @oc_p_zone_id;
         IF(ISNULL(@oc_p_zone_id)) THEN
            SET @oc_p_zone_id := 0;
         END IF;
         
    END IF;
    
    IF(@hp_country= 'GB') THEN
        SET @oc_country:='United Kingdom';
        SET @oc_country_id:='222';
        SET @oc_customer_grp_id:='1';
        SET @vat_applied := 0;
    ELSE
        SET @oc_country:='Other';
        SET @oc_country_id:='0';
        SET @oc_customer_grp_id:='2';
        SET @vat_applied := 1;
    END IF;
    
    INSERT INTO `hifiprofile_oc`.`oc_order` SET
    `order_id`=NEW.id,
    `invoice_no`=0,
    `invoice_prefix`='',
    `store_id`=0,
    `store_name`='Your Store',
    `store_url`='http://hifiprofile.co.uk/',
    `customer_id`=NEW.customer_id,
    `customer_group_id`=@oc_customer_grp_id,
    `firstname`=NEW.fname,
    `lastname`=NEW.lname,
    `email`=NEW.email,
    `telephone`=NEW.mobile,
    `fax`='',
    `custom_field`=@agent_field,
    `payment_firstname`=NEW.fname,
    `payment_lastname`=NEW.lname,
    `payment_company`=@company,
    `payment_address_1`=@oc_address_1,
    `payment_address_2`=@oc_address_2,
    `payment_city`=@oc_city,
    `payment_postcode`=@oc_zip,
    `payment_country`=@oc_country,
    `payment_country_id`=@oc_country_id,
    `payment_zone`=@oc_state,
    `payment_zone_id`=@oc_p_zone_id,
    `payment_address_format`='',
    `payment_custom_field`='[]',
    `payment_method`='Performa',
    `payment_code`='cod',
    `shipping_firstname`=NEW.fname,
    `shipping_lastname`=NEW.lname,
    `shipping_company`=@company,
    `shipping_address_1`=NEW.del_address_1,
    `shipping_address_2`=NEW.del_address_2,
    `shipping_city`=NEW.del_city,
    `shipping_postcode`=NEW.del_zip,
    `shipping_country`=@oc_country,
    `shipping_country_id`=@oc_country_id,
    `shipping_zone`=NEW.del_state,
    `shipping_zone_id`=@oc_zone_id,
    `shipping_address_format`='',
    `shipping_custom_field`='[]',
    `shipping_method`='Free Shipping',
    `shipping_code`='free.free',
    `comment`='',
    `total`=NEW.amount,
    `order_status_id`=1,
    `affiliate_id`='',
    `commission`='',
    `marketing_id`='',
    `tracking`='',
    `language_id`='1',
    `currency_id`='1',
    `currency_code`='GBP',
    `currency_value`='1.00000000',
    `ip`='127.0.0.1',
    `forwarded_ip`='',
    `user_agent`='Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0)',
    `accept_language`='',
    `date_added`=NEW.created,
    `date_modified`=NEW.modified;

        INSERT INTO `hifiprofile_oc`.`oc_order_history` SET order_id = NEW.id, order_status_id = 1, notify = 0;
        
        INSERT INTO `hifiprofile_oc`.`oc_order_total` SET
        order_id = NEW.id,
    code = "total",
    title = "Total",
    value = NEW.amount,
    sort_order = 9;
    
   
    
    INSERT INTO `hifiprofile_oc`.`oc_order_total` SET
        order_id = NEW.id,
    code = "shipping",
    title = "Free Shipping",
    value = 0.000,
    sort_order = 3;
        
    
    CALL `do_calculation` (
        NEW.id,@vat_applied
        );
    

    END IF;    
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_order_qry_n_amount` AFTER UPDATE ON `invoice_orders` FOR EACH ROW BEGIN
    declare amt decimal(15,4);
        declare new_amt float(10,2);
    declare hp_country integer;
    declare vat_applied integer;

    SELECT CAST(`total` as decimal(10,4)) FROM `hifiprofile_oc`.`oc_order` WHERE `order_id`=OLD.id INTO @amt;
    SELECT `country` FROM `hifi_master`.`customers` WHERE `id`=NEW.customer_id INTO @hp_country;
        SET @new_amt := CAST(NEW.amount as decimal(10,4));
  

    IF(@amt  <> @new_amt ) THEN
                   UPDATE `hifiprofile_oc`.`oc_order` SET `total`=NEW.amount WHERE `order_id`=OLD.id;
            UPDATE `hifiprofile_oc`.`oc_order_total` SET `value`=NEW.amount WHERE `order_id`=OLD.id AND `code`='total';
            IF(@hp_country= 'GB') THEN
                SET @vat_applied:= 0;
            ELSE
                SET @vat_applied:= 1;
            END IF;
            CALL `do_calculation_update` (
                                            OLD.id,@vat_applied
            );  
                 
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_order_details`
--

CREATE TABLE `invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `net_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `price_without_vat` float(10,2) UNSIGNED DEFAULT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `discount_status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `invoice_order_details`
--
DELIMITER $$
CREATE TRIGGER `delete_oc_product` AFTER DELETE ON `invoice_order_details` FOR EACH ROW BEGIN
    DECLARE old_price decimal(10,4);
    DECLARE old_total decimal(10,4);
    DECLARE old_tax decimal(10,4);
    DECLARE quantity integer;
    DECLARE amt decimal(10,4);
    DECLARE temp_amt decimal(10,4);

    SELECT price,total,tax,quantity FROM `hifiprofile_oc`.`oc_order_product` WHERE `order_id`=OLD.invoice_order_id AND `product_id` = OLD.product_id INTO @old_price,@old_total,@old_tax,@quantity;
    IF(ISNULL(@old_price)) THEN
            SET @temp_amt := 1;     
    ELSE
        SET @amt := (@old_price * @quantity) + @old_tax;
            DELETE FROM `hifiprofile_oc`.`oc_order_product` WHERE `order_id`=OLD.invoice_order_id AND `product_id` = OLD.product_id;
                UPDATE `hifiprofile_oc`.`oc_order_total` SET value = value - @old_tax WHERE code = "tax" AND order_id = OLD.invoice_order_id;
                UPDATE `hifiprofile_oc`.`oc_order_total` SET value = value - @old_price WHERE code = "sub_total" AND order_id = OLD.invoice_order_id;
                UPDATE `hifiprofile_oc`.`oc_order_total` SET value = value - @amt WHERE code = "total" AND order_id = OLD.invoice_order_id; 
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_oc_order` AFTER INSERT ON `invoice_order_details` FOR EACH ROW BEGIN
        declare p_name varchar(255);
        declare product_model_name varchar(255);
        declare product_model_id integer;
        declare final_price integer;
        declare tbl_id integer;
        declare p_sell_price decimal(15,2);
        declare vat integer;
        declare p_without_vat_price decimal(15,2);
        declare vat_value decimal(15,2);
        
        
                set @tbl_id := 0; 
        
        SELECT product,selling_price FROM `hifi_master`.`products` WHERE id=NEW.product_id INTO @p_name,@p_sell_price;
        
        SELECT `attribute_value` FROM `hifi_master`.`settings` WHERE `attribute_name` = "vat"  INTO @vat;
        
                IF(NEW.discount > 0)THEN
                        SET @p_without_vat_price := (@p_sell_price/(1+(@vat/100)));
                        SET @p_without_vat_price := @p_without_vat_price - (@p_without_vat_price*(NEW.discount/100));        
                ELSEIF(NEW.discount < 0)THEN
                        SET @p_without_vat_price := (@p_sell_price/(1+(@vat/100)));
                        SET @p_without_vat_price := @p_without_vat_price - (@p_without_vat_price*(NEW.discount/100));
                ELSE
                        SET @p_without_vat_price := (@p_sell_price/(1+(@vat/100)));
                END IF;
        
        SET @vat_value := @p_sell_price - (@p_sell_price/(1+(@vat/100)));
        SET @final_price := @p_without_vat_price * NEW.quantity;
        
        SET @product_model_name := "No Model";
        SELECT order_id FROM `hifiprofile_oc`.`oc_order_product` WHERE order_id = NEW.invoice_order_id AND product_id = NEW.product_id INTO @tbl_id;
        
                IF (@tbl_id =0) THEN
                        INSERT INTO `hifiprofile_oc`.`oc_order_product` (order_id,product_id,name,model,quantity,price,total,tax,reward)
                        VALUES ( NEW.invoice_order_id,NEW.product_id,@p_name,@product_model_name,NEW.quantity,@p_without_vat_price,@final_price,@vat_value,0);
                END IF;        
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_order_qty` AFTER UPDATE ON `invoice_order_details` FOR EACH ROW BEGIN
declare total1 float(10,2);
declare final_total decimal(15,4);
declare new_qty integer;
declare old_qty integer;
declare abc TEXT;
declare old_product_id integer;
declare old_order_id integer;
declare oc_price decimal(15,4);
declare vat integer;
declare without_vat_price decimal(15,4);
declare old_oc_price decimal(15,4);

SET old_product_id := OLD.product_id;
SET old_order_id := OLD.invoice_order_id;


IF(NEW.discount < 0)THEN
        SET @oc_price := NEW.price;
        SELECT `quantity` , CAST(`price` as decimal(15,4)) FROM `hifiprofile_oc`.`oc_order_product` where `order_id`=OLD.invoice_order_id AND product_id = OLD.product_id limit 1 INTO @old_qty,@old_oc_price ;
ELSEIF(NEW.discount > 0) THEN
        SELECT `attribute_value` FROM `settings` WHERE `attribute_name` = "vat" INTO @vat;
        SET @without_vat_price := NEW.price/(1+(@vat/100));
        SET @oc_price := @without_vat_price - (@without_vat_price * (NEW.discount/100));

        SELECT `quantity` , CAST(`price` as decimal(15,4)) FROM `hifiprofile_oc`.`oc_order_product` where `order_id`=OLD.invoice_order_id AND product_id = OLD.product_id limit 1 INTO @old_qty,@old_oc_price ;
ELSE
        SELECT `quantity` , CAST(`price` as decimal(15,4)) FROM `hifiprofile_oc`.`oc_order_product` where `order_id`=OLD.invoice_order_id AND product_id = OLD.product_id limit 1 INTO @old_qty,@oc_price ;
        SET @old_oc_price := @oc_price;
END IF;

SET @new_qty := CAST(NEW.quantity as decimal(15,4));
SET @final_total := @oc_price*@new_qty;
IF(@new_qty <> @old_qty OR @old_oc_price <> @oc_price) THEN
        UPDATE `hifiprofile_oc`.`oc_order_product` SET `quantity`=NEW.quantity WHERE `order_id`=OLD.invoice_order_id AND product_id = OLD.product_id;
        UPDATE `hifiprofile_oc`.`oc_order_product` SET `total`= @final_total WHERE `order_id`=OLD.invoice_order_id AND product_id = OLD.product_id;
                UPDATE `hifiprofile_oc`.`oc_order_product` SET `price`= @oc_price WHERE `order_id`=OLD.invoice_order_id AND product_id = OLD.product_id;
        
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kiosks`
--

CREATE TABLE `kiosks` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `logo_image` varchar(255) NOT NULL,
  `vat_applied` varchar(255) DEFAULT NULL,
  `vat_no` varchar(255) NOT NULL,
  `communication_password` varchar(50) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `country` varchar(20) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `rent` float(8,2) UNSIGNED NOT NULL,
  `target` float(8,2) UNSIGNED DEFAULT NULL,
  `monthly_target` float(11,2) DEFAULT NULL,
  `target_mon` tinyint(4) UNSIGNED DEFAULT NULL,
  `target_tue` tinyint(4) UNSIGNED DEFAULT NULL,
  `target_wed` tinyint(4) UNSIGNED DEFAULT NULL,
  `target_thu` tinyint(4) UNSIGNED DEFAULT NULL,
  `target_fri` tinyint(4) UNSIGNED DEFAULT NULL,
  `target_sat` tinyint(4) UNSIGNED DEFAULT NULL,
  `target_sun` tinyint(4) UNSIGNED DEFAULT NULL,
  `memo` text,
  `target_achieved` float(8,2) UNSIGNED NOT NULL,
  `contract_type` tinyint(4) UNSIGNED NOT NULL,
  `agreement_from` date NOT NULL,
  `agreement_to` date NOT NULL,
  `break_clause` varchar(255) NOT NULL,
  `renewal_weeks` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `renewal_months` tinyint(4) UNSIGNED NOT NULL,
  `terms` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `kiosk_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:Retailer;2:service center;3: unlocking center'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_credit_payment_details`
--

CREATE TABLE `kiosk_1_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_credit_product_details`
--

CREATE TABLE `kiosk_1_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_credit_receipts`
--

CREATE TABLE `kiosk_1_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_daily_stocks`
--

CREATE TABLE `kiosk_1_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_invoice_orders`
--

CREATE TABLE `kiosk_1_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_invoice_order_details`
--

CREATE TABLE `kiosk_1_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_payment_details`
--

CREATE TABLE `kiosk_1_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_products`
--

CREATE TABLE `kiosk_1_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) NOT NULL,
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_product_receipts`
--

CREATE TABLE `kiosk_1_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `bulk_invoice` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_1_product_sales`
--

CREATE TABLE `kiosk_1_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_credit_payment_details`
--

CREATE TABLE `kiosk_2_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_credit_product_details`
--

CREATE TABLE `kiosk_2_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_credit_receipts`
--

CREATE TABLE `kiosk_2_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_daily_stocks`
--

CREATE TABLE `kiosk_2_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_invoice_orders`
--

CREATE TABLE `kiosk_2_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_invoice_order_details`
--

CREATE TABLE `kiosk_2_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_payment_details`
--

CREATE TABLE `kiosk_2_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_products`
--

CREATE TABLE `kiosk_2_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` int(8) UNSIGNED DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'retail_discount_status',
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_product_receipts`
--

CREATE TABLE `kiosk_2_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `bulk_invoice` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_2_product_sales`
--

CREATE TABLE `kiosk_2_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_credit_payment_details`
--

CREATE TABLE `kiosk_3_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_credit_product_details`
--

CREATE TABLE `kiosk_3_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_credit_receipts`
--

CREATE TABLE `kiosk_3_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_daily_stocks`
--

CREATE TABLE `kiosk_3_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_daily_stocks_bak`
--

CREATE TABLE `kiosk_3_daily_stocks_bak` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_daily_stocks_bak1`
--

CREATE TABLE `kiosk_3_daily_stocks_bak1` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_invoice_orders`
--

CREATE TABLE `kiosk_3_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_invoice_order_details`
--

CREATE TABLE `kiosk_3_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_payment_details`
--

CREATE TABLE `kiosk_3_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_products`
--

CREATE TABLE `kiosk_3_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) NOT NULL,
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_product_receipts`
--

CREATE TABLE `kiosk_3_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `bulk_invoice` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_3_product_sales`
--

CREATE TABLE `kiosk_3_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_credit_payment_details`
--

CREATE TABLE `kiosk_4_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_credit_product_details`
--

CREATE TABLE `kiosk_4_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_credit_receipts`
--

CREATE TABLE `kiosk_4_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_daily_stocks`
--

CREATE TABLE `kiosk_4_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_invoice_orders`
--

CREATE TABLE `kiosk_4_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_invoice_order_details`
--

CREATE TABLE `kiosk_4_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_payment_details`
--

CREATE TABLE `kiosk_4_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_products`
--

CREATE TABLE `kiosk_4_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) NOT NULL,
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_product_receipts`
--

CREATE TABLE `kiosk_4_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `bulk_invoice` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_4_product_sales`
--

CREATE TABLE `kiosk_4_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_credit_payment_details`
--

CREATE TABLE `kiosk_5_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_credit_product_details`
--

CREATE TABLE `kiosk_5_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_credit_receipts`
--

CREATE TABLE `kiosk_5_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_daily_stocks`
--

CREATE TABLE `kiosk_5_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_invoice_orders`
--

CREATE TABLE `kiosk_5_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_invoice_order_details`
--

CREATE TABLE `kiosk_5_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_payment_details`
--

CREATE TABLE `kiosk_5_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_products`
--

CREATE TABLE `kiosk_5_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) NOT NULL,
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_product_receipts`
--

CREATE TABLE `kiosk_5_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `bulk_invoice` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_5_product_sales`
--

CREATE TABLE `kiosk_5_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_credit_payment_details`
--

CREATE TABLE `kiosk_6_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_credit_product_details`
--

CREATE TABLE `kiosk_6_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) DEFAULT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_credit_receipts`
--

CREATE TABLE `kiosk_6_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_daily_stocks`
--

CREATE TABLE `kiosk_6_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_invoice_orders`
--

CREATE TABLE `kiosk_6_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_invoice_order_details`
--

CREATE TABLE `kiosk_6_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `net_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `price_without_vat` float(10,2) UNSIGNED DEFAULT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `discount_status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_payment_details`
--

CREATE TABLE `kiosk_6_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL COMMENT 'This date will be updated if payment is changed from credit to cash, bank transfer or any other',
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_products`
--

CREATE TABLE `kiosk_6_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'retail_discount_status',
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED NOT NULL COMMENT '0:special offer disabled, 1:enabled',
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED NOT NULL COMMENT '0:special offer disabled, 1:enabled',
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_product_receipts`
--

CREATE TABLE `kiosk_6_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `vat` float(5,2) UNSIGNED NOT NULL,
  `vat_number` float(5,2) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(5) NOT NULL COMMENT '1:wholesale;0:kiosk',
  `bulk_invoice` tinyint(4) DEFAULT NULL COMMENT '1=bulk invoice',
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_6_product_sales`
--

CREATE TABLE `kiosk_6_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_credit_payment_details`
--

CREATE TABLE `kiosk_7_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_credit_product_details`
--

CREATE TABLE `kiosk_7_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_credit_receipts`
--

CREATE TABLE `kiosk_7_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_daily_stocks`
--

CREATE TABLE `kiosk_7_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_invoice_orders`
--

CREATE TABLE `kiosk_7_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_invoice_order_details`
--

CREATE TABLE `kiosk_7_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_payment_details`
--

CREATE TABLE `kiosk_7_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_products`
--

CREATE TABLE `kiosk_7_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) NOT NULL,
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_product_receipts`
--

CREATE TABLE `kiosk_7_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(5) DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `bulk_invoice` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_7_product_sales`
--

CREATE TABLE `kiosk_7_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(5) DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_credit_payment_details`
--

CREATE TABLE `kiosk_8_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_credit_product_details`
--

CREATE TABLE `kiosk_8_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_credit_receipts`
--

CREATE TABLE `kiosk_8_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_daily_stocks`
--

CREATE TABLE `kiosk_8_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_invoice_orders`
--

CREATE TABLE `kiosk_8_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_cart` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_invoice_order_details`
--

CREATE TABLE `kiosk_8_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_payment_details`
--

CREATE TABLE `kiosk_8_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_products`
--

CREATE TABLE `kiosk_8_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) NOT NULL,
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_product_receipts`
--

CREATE TABLE `kiosk_8_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(5) DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_8_product_sales`
--

CREATE TABLE `kiosk_8_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(5) DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_credit_payment_details`
--

CREATE TABLE `kiosk_10000_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0: credit note is due for clearing; 1: credit note is cleared against inovice; We added this flag if user is clearing credit note we don''t want to allow him re-clear credit note by setting it back to on credit again',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_credit_product_details`
--

CREATE TABLE `kiosk_10000_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `cost_price` float(10,3) NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) NOT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_credit_receipts`
--

CREATE TABLE `kiosk_10000_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_daily_stocks`
--

CREATE TABLE `kiosk_10000_daily_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_invoice_orders`
--

CREATE TABLE `kiosk_10000_invoice_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `bulk_discount` float(5,2) UNSIGNED DEFAULT NULL,
  `del_city` varchar(50) NOT NULL,
  `del_state` varchar(50) NOT NULL,
  `del_zip` varchar(20) NOT NULL,
  `del_address_1` varchar(255) NOT NULL,
  `del_address_2` varchar(255) NOT NULL,
  `invoice_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `amount` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_invoice_order_details`
--

CREATE TABLE `kiosk_10000_invoice_order_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_payment_details`
--

CREATE TABLE `kiosk_10000_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_products`
--

CREATE TABLE `kiosk_10000_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `qty_modified` datetime DEFAULT NULL,
  `subtract_stock` tinyint(5) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) UNSIGNED DEFAULT NULL,
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_product_receipts`
--

CREATE TABLE `kiosk_10000_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_10000_product_sales`
--

CREATE TABLE `kiosk_10000_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL COMMENT 'for normal sale: price is with vat and for wholesale sale: price is with vat',
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1:wholesale;0:kiosk',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `order_refund_value` tinyint(4) DEFAULT '0' COMMENT '0 = not decreesed from placed order,1=decreased from placed order',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_cancelled_order_products`
--

CREATE TABLE `kiosk_cancelled_order_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `cancelled_by` int(11) UNSIGNED NOT NULL,
  `kiosk_placed_order_id` int(11) UNSIGNED NOT NULL,
  `sr_no` int(11) NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `difference` int(11) DEFAULT NULL COMMENT 'if kiosk user placing order above requirement than that difference should be maintained for next placement of order for same day',
  `remarks` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `is_on_demand` tinyint(5) NOT NULL COMMENT '1:yes',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_ideal_products`
--

CREATE TABLE `kiosk_ideal_products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model` varchar(100) NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) NOT NULL,
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` tinyint(4) NOT NULL,
  `last_updated` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=REDUNDANT;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_orders`
--

CREATE TABLE `kiosk_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_placed_order_id` int(11) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1:transient|2:confirmed|3:revert stock',
  `is_on_demand` tinyint(5) NOT NULL COMMENT '1:yes',
  `dispatched_on` datetime NOT NULL,
  `received_on` datetime NOT NULL,
  `received_by` int(11) UNSIGNED NOT NULL COMMENT 'user_id of logged in users',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_order_products`
--

CREATE TABLE `kiosk_order_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `kiosk_placed_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `sr_no` int(11) NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `org_qty` int(11) NOT NULL,
  `difference` int(11) DEFAULT NULL COMMENT 'if kiosk user placing order above requirement than that difference should be maintained for next placement of order for same day',
  `remarks` varchar(255) DEFAULT NULL,
  `admin_remarks` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL COMMENT '1:Replaced by Admin; 2:added by admin',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_placed_orders`
--

CREATE TABLE `kiosk_placed_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `weekly_order` tinyint(5) UNSIGNED DEFAULT NULL COMMENT '1: Yes',
  `status` tinyint(4) UNSIGNED NOT NULL COMMENT '0:Normal; 1: Dispatched by Admin; 9: Trash',
  `weekly_placed` tinyint(4) UNSIGNED DEFAULT NULL,
  `lock_status` int(11) NOT NULL,
  `locked_by` int(11) NOT NULL,
  `merged` int(11) NOT NULL,
  `merge_data` text NOT NULL,
  `kiosk_merged` int(11) NOT NULL COMMENT '1:merge by kiosk,0:merge by other  Used to blink row on placed order screen',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_product_sales`
--

CREATE TABLE `kiosk_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL COMMENT 'This field is not used in search queries',
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(30) DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(10) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `sale_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1: wholesale 0: kiosk',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_timings`
--

CREATE TABLE `kiosk_timings` (
  `id` int(11) NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `mon_time_in` time NOT NULL DEFAULT '09:30:00',
  `mon_time_out` time NOT NULL DEFAULT '17:30:00',
  `tues_time_in` time NOT NULL DEFAULT '09:30:00',
  `tues_time_out` time NOT NULL DEFAULT '17:30:00',
  `wed_time_in` time NOT NULL DEFAULT '09:30:00',
  `wed_time_out` time NOT NULL DEFAULT '17:30:00',
  `thrus_time_in` time NOT NULL DEFAULT '09:30:00',
  `thrus_time_out` time NOT NULL DEFAULT '17:30:00',
  `fri_time_in` time NOT NULL DEFAULT '09:30:00',
  `fri_time_out` time NOT NULL DEFAULT '05:30:00',
  `sat_time_in` time NOT NULL,
  `sat_time_out` time NOT NULL DEFAULT '05:30:00',
  `sun_time_in` time NOT NULL DEFAULT '09:30:00',
  `sun_time_out` time NOT NULL DEFAULT '05:30:00',
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modifed` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_1`
--

CREATE TABLE `kiosk_transferred_stock_1` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_2`
--

CREATE TABLE `kiosk_transferred_stock_2` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_3`
--

CREATE TABLE `kiosk_transferred_stock_3` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_4`
--

CREATE TABLE `kiosk_transferred_stock_4` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_5`
--

CREATE TABLE `kiosk_transferred_stock_5` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_6`
--

CREATE TABLE `kiosk_transferred_stock_6` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_7`
--

CREATE TABLE `kiosk_transferred_stock_7` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_8`
--

CREATE TABLE `kiosk_transferred_stock_8` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk_transferred_stock_10000`
--

CREATE TABLE `kiosk_transferred_stock_10000` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(8,2) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk__products`
--

CREATE TABLE `kiosk__products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model` varchar(100) NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` int(11) DEFAULT NULL COMMENT 'In %',
  `retail_discount` int(8) UNSIGNED DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'retail_discount_status',
  `max_discount` tinyint(5) UNSIGNED NOT NULL,
  `min_discount` tinyint(5) UNSIGNED NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk__product_receipts`
--

CREATE TABLE `kiosk__product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `fname` varchar(70) NOT NULL,
  `lname` varchar(70) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(150) NOT NULL,
  `state` varchar(150) NOT NULL,
  `zip` varchar(12) NOT NULL,
  `vat` int(10) UNSIGNED NOT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kiosk__product_sales`
--

CREATE TABLE `kiosk__product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) DEFAULT NULL,
  `sale_price` float(10,2) UNSIGNED NOT NULL,
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` tinyint(5) DEFAULT NULL COMMENT 'In %',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `log` text NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sent_by` int(11) DEFAULT NULL,
  `sent_to_id` int(11) UNSIGNED DEFAULT NULL,
  `read_by` int(11) DEFAULT NULL,
  `read_by_user` int(11) DEFAULT NULL,
  `subject` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `date` datetime NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '2read/1unread',
  `receiver_status` tinyint(4) NOT NULL,
  `sender_status` tinyint(4) NOT NULL,
  `receiver_read` tinyint(4) UNSIGNED NOT NULL COMMENT '1:unread 2:read',
  `sender_read` tinyint(4) UNSIGNED NOT NULL COMMENT '1:unread 2:read',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_blk_re_sales`
--

CREATE TABLE `mobile_blk_re_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `sale_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'this field is for saving origingal id when mobile is returned',
  `mobile_purchase_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `mobile_model_id` int(11) UNSIGNED NOT NULL,
  `network_id` int(11) UNSIGNED DEFAULT NULL,
  `color` varchar(100) NOT NULL,
  `imei` varchar(15) NOT NULL,
  `brief_history` text NOT NULL,
  `customer_fname` varchar(50) NOT NULL,
  `customer_lname` varchar(50) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `customer_address_1` varchar(255) NOT NULL,
  `customer_address_2` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `country` tinytext NOT NULL,
  `description` text NOT NULL,
  `type` tinyint(4) UNSIGNED NOT NULL,
  `grade` varchar(255) DEFAULT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) NOT NULL,
  `discounted_price` float(8,2) UNSIGNED DEFAULT NULL,
  `discount` tinyint(4) UNSIGNED DEFAULT NULL,
  `selling_date` datetime NOT NULL,
  `zip` varchar(20) NOT NULL,
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `refund_status` tinyint(4) UNSIGNED DEFAULT NULL,
  `refund_remarks` varchar(255) NOT NULL,
  `refund_date` datetime DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `custom_grade` tinyint(4) UNSIGNED DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_blk_re_sale_payments`
--

CREATE TABLE `mobile_blk_re_sale_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_blk_re_sale_id` int(11) UNSIGNED NOT NULL,
  `mobile_purchase_id` int(11) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `pmt_identifier` varchar(30) DEFAULT NULL COMMENT 'this field is added to distinguish payments after resale of mobiles',
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_blk_transfer_logs`
--

CREATE TABLE `mobile_blk_transfer_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `mobile_purchase_reference` varchar(50) DEFAULT NULL,
  `mobile_purchase_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED DEFAULT NULL,
  `network_id` int(11) UNSIGNED DEFAULT NULL,
  `grade` tinyint(4) UNSIGNED DEFAULT NULL,
  `type` tinyint(4) UNSIGNED DEFAULT NULL,
  `mobile_resale_id` int(11) UNSIGNED DEFAULT NULL,
  `receiving_status` tinyint(5) UNSIGNED DEFAULT NULL COMMENT '1:transient 0:received',
  `imei` varchar(25) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:available 1:sold 2:refunded 3:reserved 4:unlocking 5:repairing',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_conditions`
--

CREATE TABLE `mobile_conditions` (
  `id` int(11) UNSIGNED NOT NULL,
  `mobile_condition` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_models`
--

CREATE TABLE `mobile_models` (
  `id` int(11) UNSIGNED NOT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model` varchar(150) NOT NULL,
  `brief_description` varchar(255) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_placed_orders`
--

CREATE TABLE `mobile_placed_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `imei` int(15) NOT NULL,
  `brand` int(11) NOT NULL,
  `model` int(11) NOT NULL,
  `ntework` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sold_by` int(11) NOT NULL,
  `kiosk_placed_order_id` int(11) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_prices`
--

CREATE TABLE `mobile_prices` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `mobile_model_id` int(11) UNSIGNED NOT NULL,
  `network_id` int(11) UNSIGNED DEFAULT NULL,
  `locked` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '1:locked 0:unlocked',
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `maximum_discount` varchar(20) NOT NULL,
  `topup_status` tinyint(4) UNSIGNED NOT NULL,
  `maximum_topup` varchar(20) NOT NULL,
  `grade` tinyint(4) UNSIGNED NOT NULL,
  `cost_price` float(8,2) NOT NULL,
  `sale_price` float(8,2) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_purchases`
--

CREATE TABLE `mobile_purchases` (
  `id` int(11) UNSIGNED NOT NULL,
  `purchase_number` int(11) UNSIGNED NOT NULL,
  `mobile_purchase_reference` varchar(50) DEFAULT NULL,
  `rand_num` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `new_kiosk_id` int(11) UNSIGNED DEFAULT NULL,
  `purchased_by_kiosk` int(11) UNSIGNED NOT NULL DEFAULT '10000',
  `mobile_condition` varchar(255) DEFAULT NULL,
  `mobile_condition_remark` text,
  `function_condition` varchar(255) DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `mobile_model_id` int(11) UNSIGNED NOT NULL,
  `network_id` int(11) UNSIGNED DEFAULT NULL,
  `color` varchar(100) NOT NULL,
  `imei` varchar(15) NOT NULL,
  `brief_history` text NOT NULL,
  `customer_fname` varchar(50) NOT NULL,
  `customer_lname` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `customer_address_1` varchar(255) NOT NULL,
  `customer_address_2` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `country` tinytext NOT NULL,
  `customer_identification` varchar(255) NOT NULL,
  `serial_number` varchar(20) NOT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(150) NOT NULL,
  `photo_type` varchar(25) NOT NULL,
  `photo_size` varchar(20) NOT NULL,
  `path` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `cost_price` float(8,2) NOT NULL,
  `topedup_price` float(8,2) UNSIGNED DEFAULT NULL,
  `grade` varchar(255) NOT NULL,
  `type` tinyint(4) UNSIGNED NOT NULL COMMENT '1:locked 0:unlocked',
  `purchasing_date` datetime NOT NULL,
  `reserve_date` datetime NOT NULL,
  `reserved_by` int(11) UNSIGNED NOT NULL,
  `transient_date` datetime NOT NULL,
  `transient_by` int(11) UNSIGNED NOT NULL,
  `zip` varchar(20) NOT NULL,
  `receiving_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:transient, 0:received',
  `status` tinyint(4) UNSIGNED NOT NULL,
  `mobile_status` tinyint(4) UNSIGNED NOT NULL,
  `purchase_status` tinyint(4) UNSIGNED DEFAULT '0' COMMENT '1: Bulk Purchase; 0: Normal Purchase',
  `custom_grades` tinyint(4) UNSIGNED DEFAULT '0',
  `selling_price` float(10,2) UNSIGNED NOT NULL,
  `static_selling_price` float(10,2) UNSIGNED DEFAULT NULL COMMENT 'actual selling price added by admin',
  `lowest_selling_price` float(10,2) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_purchase_references`
--

CREATE TABLE `mobile_purchase_references` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference_no` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_repairs`
--

CREATE TABLE `mobile_repairs` (
  `id` int(11) UNSIGNED NOT NULL,
  `repair_number` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED DEFAULT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `booked_by` int(11) UNSIGNED DEFAULT NULL,
  `delivered_by` int(11) UNSIGNED DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `mobile_model_id` varchar(100) NOT NULL,
  `problem_type` varchar(255) NOT NULL,
  `mobile_condition` varchar(255) DEFAULT NULL,
  `mobile_condition_remark` text,
  `function_condition` varchar(255) DEFAULT NULL,
  `imei` varchar(15) NOT NULL,
  `received_at` datetime NOT NULL,
  `delivered_at` datetime NOT NULL,
  `brief_history` text NOT NULL,
  `customer_fname` varchar(50) NOT NULL,
  `customer_lname` varchar(50) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `customer_address_1` varchar(255) NOT NULL,
  `customer_address_2` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `country` tinytext NOT NULL,
  `description` text NOT NULL,
  `phone_password` text,
  `estimated_cost` varchar(255) NOT NULL,
  `actual_cost` float(8,2) NOT NULL,
  `net_cost` float(8,2) DEFAULT NULL COMMENT 'recommended cost prices of all problem types',
  `zip` varchar(20) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL COMMENT '1:initial_request|2:in_process|3:not_repairable|4:processed|5:delivered',
  `internal_repair` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '1: yes',
  `status_refund` tinyint(4) UNSIGNED DEFAULT NULL,
  `status_rebooked` tinyint(4) UNSIGNED DEFAULT NULL,
  `status_freezed` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'if price would be freezed by admin than its value would be 1 otherwise null or 0 ',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_repair_logs`
--

CREATE TABLE `mobile_repair_logs` (
  `id` bigint(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `service_center_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_repair_id` int(11) UNSIGNED NOT NULL,
  `comments` int(11) UNSIGNED DEFAULT NULL,
  `repair_status` tinyint(5) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_repair_parts`
--

CREATE TABLE `mobile_repair_parts` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `opp_status` tinyint(5) NOT NULL DEFAULT '0' COMMENT 'backstock=1,faulty=2',
  `opp_date` datetime DEFAULT NULL,
  `mobile_repair_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_repair_prices`
--

CREATE TABLE `mobile_repair_prices` (
  `id` int(11) UNSIGNED NOT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `mobile_model_id` int(10) UNSIGNED NOT NULL,
  `problem_type` tinyint(4) UNSIGNED NOT NULL,
  `problem` varchar(255) DEFAULT NULL,
  `repair_cost` float(7,2) UNSIGNED NOT NULL,
  `repair_price` float(9,4) NOT NULL,
  `repair_days` tinyint(5) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `status_change_date` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_repair_sales`
--

CREATE TABLE `mobile_repair_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `mobile_repair_id` int(11) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `sold_on` datetime DEFAULT NULL,
  `refund_by` int(11) UNSIGNED DEFAULT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `refund_amount` float DEFAULT NULL,
  `refund_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_on` datetime DEFAULT NULL,
  `refund_remarks` varchar(255) DEFAULT NULL,
  `repair_status` tinyint(6) NOT NULL DEFAULT '-1' COMMENT '-1 FOR PHONE BOOKED BEFORE MAR 17, 2016;6:DELIVERED_REPAIRED_BY_KIOSK;8:DELIVERED_REPAIRED_BY_TECHNICIAN;9:DELIVERED_UNREPAIRED_BY_TECHNICIAN;7:DELIVERED_UNREPAIRED_BY_KIOSK',
  `rebooked_status` tinyint(4) UNSIGNED DEFAULT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT 'uf status = 1, than refunded',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_re_sales`
--

CREATE TABLE `mobile_re_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `sale_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'this field is for saving origingal id when mobile is returned',
  `mobile_purchase_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `mobile_model_id` int(11) UNSIGNED NOT NULL,
  `network_id` int(11) UNSIGNED DEFAULT NULL,
  `color` varchar(100) NOT NULL,
  `imei` varchar(15) NOT NULL,
  `brief_history` text NOT NULL,
  `customer_fname` varchar(50) NOT NULL,
  `customer_lname` varchar(50) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `customer_address_1` varchar(255) NOT NULL,
  `customer_address_2` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `country` tinytext NOT NULL,
  `description` text NOT NULL,
  `type` tinyint(4) UNSIGNED NOT NULL,
  `grade` varchar(255) DEFAULT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) NOT NULL,
  `discounted_price` float(8,2) UNSIGNED DEFAULT NULL,
  `discount` tinyint(4) UNSIGNED DEFAULT NULL,
  `selling_date` datetime NOT NULL,
  `zip` varchar(20) NOT NULL,
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `refund_status` tinyint(4) UNSIGNED DEFAULT NULL,
  `refund_remarks` varchar(255) NOT NULL,
  `refund_date` datetime DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `custom_grade` tinyint(4) UNSIGNED DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_re_sale_payments`
--

CREATE TABLE `mobile_re_sale_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_re_sale_id` int(11) UNSIGNED NOT NULL,
  `mobile_purchase_id` int(11) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `pmt_identifier` varchar(30) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_transfer_logs`
--

CREATE TABLE `mobile_transfer_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `mobile_purchase_reference` varchar(50) DEFAULT NULL,
  `mobile_purchase_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED DEFAULT NULL,
  `network_id` int(11) UNSIGNED DEFAULT NULL,
  `grade` tinyint(4) UNSIGNED DEFAULT NULL,
  `type` tinyint(4) UNSIGNED DEFAULT NULL,
  `mobile_resale_id` int(11) UNSIGNED DEFAULT NULL,
  `receiving_status` tinyint(5) UNSIGNED DEFAULT NULL COMMENT '1:transient 0:received',
  `imei` varchar(25) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:available 1:sold 2:refunded 3:reserved 4:unlocking 5:repairing',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_unlocks`
--

CREATE TABLE `mobile_unlocks` (
  `id` int(11) UNSIGNED NOT NULL,
  `unlock_number` int(11) UNSIGNED NOT NULL,
  `code` varchar(100) DEFAULT NULL,
  `unlock_code_instructions` text,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `booked_by` int(11) UNSIGNED DEFAULT NULL,
  `delivered_by` int(11) UNSIGNED DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `network_id` int(11) UNSIGNED NOT NULL,
  `mobile_model_id` int(11) UNSIGNED DEFAULT NULL,
  `imei` varchar(15) NOT NULL,
  `received_at` datetime NOT NULL,
  `delivered_at` datetime NOT NULL,
  `brief_history` text NOT NULL,
  `customer_fname` varchar(50) NOT NULL,
  `customer_lname` varchar(50) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `customer_address_1` varchar(255) NOT NULL,
  `customer_address_2` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `country` tinytext NOT NULL,
  `description` text NOT NULL,
  `estimated_cost` float(8,4) DEFAULT NULL,
  `actual_cost` float(8,2) DEFAULT NULL,
  `net_cost` float(8,2) UNSIGNED DEFAULT NULL,
  `zip` varchar(20) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL COMMENT '1:In Process|2:Repaired|3:Delivered After Unlocking|4:Unlocked Delivery|5:Unlockable',
  `internal_unlock` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '1: yes',
  `status_refund` tinyint(4) UNSIGNED DEFAULT NULL,
  `status_rebooked` tinyint(4) UNSIGNED DEFAULT NULL,
  `status_freezed` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'if price would be freezed by admin than its value would be 1 otherwise null or 0 ',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_unlock_logs`
--

CREATE TABLE `mobile_unlock_logs` (
  `id` bigint(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `unlock_center_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_unlock_id` int(11) UNSIGNED NOT NULL,
  `comments` int(11) DEFAULT NULL,
  `unlock_status` tinyint(5) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_unlock_prices`
--

CREATE TABLE `mobile_unlock_prices` (
  `id` int(11) UNSIGNED NOT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `mobile_model_id` int(11) UNSIGNED NOT NULL,
  `network_id` int(11) UNSIGNED NOT NULL,
  `unlocking_cost` float(7,2) UNSIGNED NOT NULL,
  `unlocking_price` float(9,4) UNSIGNED NOT NULL,
  `unlocking_days` tinyint(5) UNSIGNED NOT NULL,
  `unlocking_minutes` tinyint(5) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `status_change_date` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_unlock_sales`
--

CREATE TABLE `mobile_unlock_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `retail_customer_id` int(11) UNSIGNED NOT NULL,
  `mobile_unlock_id` int(11) UNSIGNED NOT NULL,
  `sold_by` int(11) UNSIGNED NOT NULL,
  `sold_on` datetime DEFAULT NULL,
  `refund_by` int(11) UNSIGNED DEFAULT NULL,
  `amount` float(8,4) DEFAULT NULL,
  `refund_amount` float(8,2) DEFAULT NULL,
  `refund_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_on` datetime DEFAULT NULL,
  `refund_remarks` varchar(255) DEFAULT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT 'status = 1: refunded',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

CREATE TABLE `networks` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `on_demand_orders`
--

CREATE TABLE `on_demand_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL COMMENT '0:Normal; 1: Dispatched by Admin; 9: Trash',
  `lock_status` int(11) NOT NULL,
  `locked_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `on_demand_placed_orders`
--

CREATE TABLE `on_demand_placed_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_placed_order_id` int(11) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1:transient|2:confirmed',
  `dispatched_on` datetime NOT NULL,
  `received_on` datetime NOT NULL,
  `received_by` int(11) UNSIGNED NOT NULL COMMENT 'user_id of logged in users',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `on_demand_products`
--

CREATE TABLE `on_demand_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `kiosk_placed_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `sr_no` int(11) NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `org_qty` int(11) NOT NULL,
  `difference` int(11) DEFAULT NULL COMMENT 'if kiosk user placing order above requirement than that difference should be maintained for next placement of order for same day',
  `remarks` varchar(255) DEFAULT NULL,
  `admin_remarks` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL COMMENT '1:Replaced by Admin; 2:added by admin',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `order_disputes`
--

CREATE TABLE `order_disputes` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) NOT NULL,
  `sale_price` float(10,2) NOT NULL,
  `receiving_status` tinyint(4) NOT NULL DEFAULT '0',
  `disputed_by` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `kiosk_user_remarks` varchar(255) NOT NULL,
  `admin_remarks` varchar(255) NOT NULL,
  `approval_by` tinyint(5) UNSIGNED NOT NULL,
  `approval_status` tinyint(4) UNSIGNED NOT NULL,
  `admin_acted` datetime NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `cr_ref_id` varchar(255) DEFAULT NULL,
  `pmt_ref_id` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL COMMENT 'This date will be updated if payment is changed from credit to cash, bank transfer or any other',
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pmt_logs`
--

CREATE TABLE `pmt_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `pmt_id` int(11) NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `reference_id` int(11) UNSIGNED NOT NULL,
  `adjusted_by` varchar(255) NOT NULL COMMENT '1:cash,2:credit',
  `memo` varchar(255) DEFAULT NULL,
  `old_pmt_method` varchar(255) NOT NULL,
  `pmt_method` varchar(255) NOT NULL,
  `credit_recipt_id` int(11) NOT NULL COMMENT 'This field will have value when invoice is cleared by credit note',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` datetime NOT NULL,
  `receipt_id` int(11) UNSIGNED NOT NULL,
  `receipt_type` tinyint(4) UNSIGNED NOT NULL COMMENT '1=invoice,2=credit_noe,3=quotation,4=credit_quotation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `problem_types`
--

CREATE TABLE `problem_types` (
  `id` int(11) NOT NULL,
  `problem_type` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `prefix` varchar(2) NOT NULL DEFAULT 'SD',
  `id` int(11) UNSIGNED NOT NULL,
  `product` varchar(150) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `qty_update_status` int(11) NOT NULL COMMENT '0:new product without qty,1:first time stock in;2:more then one time qty update ',
  `qty_update_time` datetime NOT NULL COMMENT 'on first time qty update this field will be updated ',
  `back_stock_status` int(11) NOT NULL COMMENT '1: back stocked 0: old products or no status',
  `back_stock_time` datetime NOT NULL,
  `description` text NOT NULL,
  `location` varchar(80) DEFAULT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) UNSIGNED NOT NULL,
  `lu_cp` datetime DEFAULT NULL,
  `vat_excluded_wholesale_price` float(10,2) UNSIGNED DEFAULT NULL,
  `vat_exclude_retail_price` float(10,2) UNSIGNED DEFAULT NULL,
  `retail_cost_price` float(10,2) DEFAULT NULL,
  `lu_rcp` datetime DEFAULT NULL,
  `selling_price` float(10,2) NOT NULL,
  `lu_sp` datetime DEFAULT NULL,
  `retail_selling_price` float(10,2) DEFAULT NULL,
  `lu_rsp` datetime DEFAULT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `additional_model_id` text NOT NULL,
  `manufacturing_date` date NOT NULL,
  `sku` int(11) UNSIGNED NOT NULL,
  `country_make` varchar(80) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `color` varchar(30) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '1',
  `featured` tinyint(4) UNSIGNED DEFAULT '0',
  `discount` float(6,2) UNSIGNED DEFAULT NULL COMMENT 'In %',
  `retail_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `rt_discount_status` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'retail_discount_status',
  `max_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `min_discount` float(6,2) UNSIGNED DEFAULT NULL,
  `special_offer` tinyint(4) UNSIGNED NOT NULL COMMENT '0:special offer disabled, 1:enabled',
  `festival_offer` int(11) NOT NULL DEFAULT '0',
  `retail_special_offer` tinyint(4) UNSIGNED NOT NULL COMMENT '0:special offer disabled, 1:enabled',
  `image_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_dir` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
  `stock_level` int(11) NOT NULL,
  `dead_stock_level` int(11) NOT NULL,
  `modified_by` int(11) UNSIGNED NOT NULL,
  `modified_by_bak` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NULL DEFAULT NULL,
  `qty_modified` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `last_import` datetime DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=REDUNDANT;

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `add_product` AFTER INSERT ON `products` FOR EACH ROW BEGIN
    declare optionID integer;
    declare oc_product_option_id integer;
    declare optionvalueid integer;
    declare oc_discount varchar(255);
    declare vat decimal(15,4);
    declare without_vat_price_retail decimal(15,4);
    declare without_vat_price decimal(15,4);
    declare oc_price decimal(15,4);
    declare oc_weight decimal(15,8);
        SELECT `option_id`,option_value_id FROM  hifiprofile_oc.oc_option_value_description  WHERE `name` =  NEW.color INTO @optionID,@optionvalueid;
        SELECT `attribute_value` FROM `hifi_master`.`settings` WHERE `attribute_name`='vat' INTO @vat;
        SET @oc_price = NEW.selling_price/(1+@vat/100);
        IF (ISNULL(NEW.weight)) THEN
            SET @oc_weight:= 1;
        ELSE
            SET @oc_weight:= CAST(NEW.weight as decimal(10,8));
        END IF;
        INSERT INTO hifiprofile_oc.oc_product SET
                                            `product_id` = NEW.id,
                                            `model` = NEW.model,
                                            `sku` = NEW.sku ,
                                            `upc` = NEW.product_code,
                                            `ean`= '1',
                                            `jan`= '1',
                                            `isbn`= '1',
                                            `mpn`= '1',
                                            `location` = NEW.location ,
                                            `quantity` = NEW.quantity,
                                            `stock_status_id` ='1',
                                            `manufacturer_id` =  NEW.brand_id ,
                                            `shipping`= '1',
                                            `price` = @oc_price ,
                                            `points`= '0',
                                            `tax_class_id`= '9',
                                            `date_available`= NEW.manufacturing_date,
                                            `weight` =  @oc_weight,
                                            `image` =   NEW.image ,
                                            `length`= '1',
                                            `width`= '1',
                                            `height`= '1',
                                            `subtract`= '0',
                                            `minimum`= '1',
                                            `sort_order`= '0',
                                            `status`=  NEW.status ,
                                            `viewed`= '1',
                                            `date_added` = NEW.created ,
                                            `date_modified`= NEW.modified ;

        INSERT INTO hifiprofile_oc.oc_product_description SET
                                                        `product_id`= NEW.id ,
                                                        `language_id`= '1',
                                                        `name`= NEW.product,
                                                        `description` = ' ',
                                                        `tag` = '',
                                                        `meta_title` = NEW.product,
                                                        `meta_description` = '',
                                                        `meta_keyword` = '';


        INSERT INTO hifiprofile_oc.oc_product_to_category SET
                                                        `product_id`= NEW.id,
                                                        `category_id`=category_id ;

 

        INSERT INTO `hifiprofile_oc`.`oc_product_option` SET
                                                    `product_id`= NEW.id,
                                                    `option_id` = @optionid;

        SELECT `product_option_id` FROM `hifiprofile_oc`.`oc_product_option`  WHERE `product_id`= NEW.id ORDER BY `product_option_id` DESC LIMIT 1 INTO @oc_product_option_id;
        IF (ISNULL(@oc_product_option_id)) THEN
            SET @oc_product_option_id:=0;
        END IF;
        INSERT INTO `hifiprofile_oc`.`oc_product_option_value`  SET
                                                            `product_option_id` = @oc_product_option_id ,
                                                            `product_id`= NEW.id ,
                                                            `option_id` = @optionID,
                                                            `option_value_id` = @optionvalueid,
                                                            `quantity`  = '',
                                                            `subtract`  = '0',
                                                            `price`  = '0.000',
                                                            `price_prefix` = '' ,
                                                            `points` = '',
                                                            `points_prefix` = '' ,
                                                            `weight` = '' ,
                                                            `weight_prefix` = '' ;
 
        INSERT INTO hifiprofile_oc.oc_product_to_store  SET
                                                        `product_id`= NEW.id ,
                                                        `store_id` = '0' ;
                                                
 
        INSERT INTO hifiprofile_oc.oc_product_to_layout  SET
                                                        `product_id`= NEW.id ,
                                                        `layout_id`  = '0',
                                                        `store_id` = '0';

        
        SET @without_vat_price_retail:= (NEW.retail_selling_price/(1+@vat/100));
        SET @without_vat_price:= (NEW.selling_price/(1+@vat/100));
        INSERT INTO hifiprofile_oc.oc_product_discount  SET
                                                    `product_id`= NEW.id  ,
                                                    `customer_group_id` ='1',
                                                    `quantity` ='1',
                                                    `priority` = '1',
                                                    `price`  = @without_vat_price_retail        ;
                                     
        INSERT INTO hifiprofile_oc.oc_product_discount  SET
                                                    `product_id`= NEW.id  ,
                                                    `customer_group_id` ='2',
                                                    `quantity` ='1',
                                                    `priority` = '1',
                                                    `price`  = @without_vat_price        ;
        
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_products` AFTER DELETE ON `products` FOR EACH ROW BEGIN
            DELETE FROM hifiprofile_oc.oc_product WHERE product_id  =OLD.id;
            DELETE FROM hifiprofile_oc.oc_product_description WHERE  product_id  = OLD.id;
            DELETE FROM hifiprofile_oc.oc_product_discount WHERE product_id  =OLD.id;
            DELETE FROM hifiprofile_oc.oc_product_option WHERE  product_id  = OLD.id;
            DELETE FROM hifiprofile_oc.oc_product_option_value WHERE product_id  =OLD.id;
            DELETE FROM hifiprofile_oc.oc_product_to_category WHERE product_id  =OLD.id;
            
            DELETE FROM hifiprofile_oc.oc_product_to_store WHERE product_id  =OLD.id;
            DELETE FROM hifiprofile_oc.oc_product_to_layout WHERE  product_id  = OLD.id;
            
            DELETE FROM hifiprofile_oc.oc_product_discount WHERE product_id  =OLD.id;
            DELETE FROM hifiprofile_oc.oc_product_special WHERE  product_id  = OLD.id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_products` AFTER UPDATE ON `products` FOR EACH ROW BEGIN
        declare optionID integer; 
        declare productOptionId integer;
        declare optionvalueid integer;
        declare product_option_id integer;
        declare vat decimal(15,4);
        declare without_vat_price_retail decimal(15,4);
        declare without_vat_price decimal(15,4);
        declare oc_price decimal(15,4);
        declare oc_weight decimal(15,8);
        IF NEW.color <> OLD.color THEN
              INSERT INTO `hifi_master`.`color_log` (`product_id`,`product_code`,`old_color`, `new_color`,`created`,`modified`) VALUES (OLD.id, OLD.product_code, OLD.color, NEW.color, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);  
        END IF;
        
            SELECT `option_id`,option_value_id FROM  hifiprofile_oc.oc_option_value_description  WHERE `name` =  NEW.color INTO @optionID,@optionvalueid;
            SELECT product_option_id FROM hifiprofile_oc.oc_product_option  WHERE `product_id`= NEW.id INTO @productOptionId;
            SELECT `attribute_value` FROM `hifi_master`.`settings` WHERE `attribute_name`='vat' INTO @vat;
            SET @oc_price:=NEW.selling_price/(1+@vat/100);
            IF (ISNULL(NEW.weight)) THEN
            SET @oc_weight:= 1;
        ELSE
            SET @oc_weight:= CAST(NEW.weight as decimal(10,8));
        END IF;
            UPDATE  hifiprofile_oc.oc_product  SET
                                                `model` = NEW.model,
                                                `sku` = NEW.sku ,
                                                `upc` = NEW.product_code,
                                                `ean`= '1',
                                                `jan`= '1',
                                                `isbn`= '1',
                                                `mpn`= '1',
                                                `location` = NEW.location ,
                                                `quantity` = NEW.quantity,
                                                `stock_status_id` ='1',
                                                `manufacturer_id` =  NEW.brand_id ,
                                                `shipping`= '1',
                                                `price` = @oc_price ,
                                                `points`= '1',
                                                `tax_class_id`= '9',
                                                `date_available`= '0000-00-00',
                                                `weight` = @oc_weight ,
                                                `image` =   NEW.image ,
                                                `length`= '1',
                                                `width`= '1',
                                                `height`= '1',
                                                `subtract`= '0',
                                                `minimum`= '1',
                                                `sort_order`= '0',
                                                `status`=  NEW.status ,
                                                `viewed`= '1',
                                                `date_added` = NEW.created ,
                                                `date_modified`= NEW.modified WHERE  
                                                `product_id` = NEW.id 
                                                ;

            UPDATE hifiprofile_oc.`oc_product_description` SET
                                                        `language_id` = '1',
                                                        `name` = NEW.product,
                                                        `description` = NEW.description,
                                                        `meta_title` = NEW.product,
                                                        `meta_description` = '',
                                                        `meta_keyword` = ''
                                                        WHERE `product_id` = NEW.id  ;
 

            UPDATE hifiprofile_oc.`oc_product_to_category` SET
                                                        `category_id` = NEW.category_id
                                                        WHERE `product_id` = NEW.id;

            UPDATE hifiprofile_oc.`oc_product_option` SET
                                                    `option_id` = @optionid 
                                                    WHERE `product_id`= NEW.id ;

 
 
            UPDATE hifiprofile_oc.oc_product_option_value  SET
                                                        `product_option_id` = @productOptionId ,
                                                        `option_id` = @optionID,
                                                        `option_value_id` = @optionvalueid,
                                                        `quantity`  = '',
                                                        `subtract`  = '1',
                                                        `price`  = '',
                                                        `price_prefix` = '' ,
                                                        `points` = '',
                                                        `points_prefix` = '' ,
                                                        `weight` = '' ,
                                                        `weight_prefix` = ''
                                                         WHERE `product_id`= NEW.id;

            update hifiprofile_oc.oc_product_to_store  SET
                                                        `store_id` = '0'
                                                        WHERE `product_id`= NEW.id;
                                                
 
            update hifiprofile_oc.oc_product_to_layout  SET
                                                         `layout_id`  = '0',
                                                        `store_id` = '0'
                                                        WHERE `product_id`= NEW.id;
            
            SET @without_vat_price_retail:= (NEW.retail_selling_price/(1+@vat/100));
                        SET @without_vat_price:= (NEW.selling_price/(1+@vat/100));
            update hifiprofile_oc.oc_product_discount  SET
                                                        `customer_group_id` ='1',
                                                        `quantity` ='1',
                                                        `priority` = '1',
                                                        `price`  = @without_vat_price_retail
                                                        WHERE `product_id`= NEW.id AND `customer_group_id` ='1';
                                                        
            update hifiprofile_oc.oc_product_discount  SET
                                                        `customer_group_id` ='2',
                                                        `quantity` ='1',
                                                        `priority` = '1',
                                                        `price`  = @without_vat_price
                                                        WHERE `product_id`= NEW.id AND `customer_group_id` ='2';
                        
            

 
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_models`
--

CREATE TABLE `product_models` (
  `id` int(11) UNSIGNED NOT NULL,
  `brand_id` int(11) UNSIGNED NOT NULL,
  `model` varchar(150) NOT NULL,
  `brief_description` varchar(255) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_payments`
--

CREATE TABLE `product_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'only for refund case purpose',
  `refunded_quantity` tinyint(4) UNSIGNED DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` float NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL COMMENT '0:Not done;1:Done',
  `status` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '2 for refunded and entry will be in -ve amount',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_receipts`
--

CREATE TABLE `product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `bill_cost` float(10,2) DEFAULT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `vat` float(5,2) UNSIGNED NOT NULL,
  `vat_number` float(5,2) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `sale_type` tinyint(5) NOT NULL,
  `bulk_invoice` tinyint(4) DEFAULT NULL COMMENT '1=bulk invoice',
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_sale_stats`
--

CREATE TABLE `product_sale_stats` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `cost_price` double(12,6) NOT NULL,
  `selling_price` double(12,6) NOT NULL COMMENT 'without vat',
  `vat` double(12,6) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_sale_stats_new`
--

CREATE TABLE `product_sale_stats_new` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `cost_price` double(12,6) NOT NULL,
  `selling_price` double(12,6) NOT NULL COMMENT 'without vat',
  `vat` double(12,6) NOT NULL,
  `category_id` int(11) NOT NULL,
  `bulk_invoice` tinyint(4) DEFAULT '4',
  `status` tinyint(5) UNSIGNED NOT NULL COMMENT '1: For Quotation; 0: Normal Sale',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_sell_stats`
--

CREATE TABLE `product_sell_stats` (
  `id` int(11) NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `kiosk_name` varchar(250) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `cost_price` double(12,6) NOT NULL,
  `selling_price` double(12,6) NOT NULL COMMENT 'without vat',
  `vat` double(12,6) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_sell_stats_new`
--

CREATE TABLE `product_sell_stats_new` (
  `id` int(11) NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `kiosk_name` varchar(250) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `cost_price` double(12,6) NOT NULL,
  `selling_price` double(12,6) NOT NULL COMMENT 'without vat',
  `vat` double(12,6) NOT NULL,
  `category_id` int(11) NOT NULL,
  `bulk_invoice` tinyint(4) DEFAULT '0',
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int(11) UNSIGNED NOT NULL,
  `date_of_birth` date NOT NULL,
  `national_insurance` varchar(50) NOT NULL,
  `visa_type` varchar(25) NOT NULL,
  `visa_expiry_date` date NOT NULL,
  `memo` text NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reorder_levels`
--

CREATE TABLE `reorder_levels` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `reorder_level` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `repair_log`
--

CREATE TABLE `repair_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `repair_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `comments` int(11) DEFAULT NULL,
  `repair_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `repair_payments`
--

CREATE TABLE `repair_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_repair_id` int(11) UNSIGNED NOT NULL,
  `mobile_repair_sale_id` int(11) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reserved_products`
--

CREATE TABLE `reserved_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL,
  `quantity` int(11) UNSIGNED DEFAULT NULL,
  `cost_price` float(10,3) NOT NULL,
  `sale_price` float(10,3) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0: invoice unprocessed; 1:invoice processed',
  `kiosk_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `retail_customers`
--

CREATE TABLE `retail_customers` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED DEFAULT '0' COMMENT '0 for main website',
  `created_by` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(80) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(30) NOT NULL,
  `country` tinytext NOT NULL,
  `zip` varchar(20) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `modified` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `revert_stocks`
--

CREATE TABLE `revert_stocks` (
  `id` int(11) NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kiosk_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sale_price` float NOT NULL,
  `cost_price` float NOT NULL,
  `remarks` varchar(250) NOT NULL,
  `flag` tinyint(11) UNSIGNED DEFAULT NULL COMMENT '1:Normal;2:Replace By Admin;3:Added By Admin',
  `product_processed` int(11) NOT NULL COMMENT '0:unprocessed;1:processed| purpose for generating invoice for stock transferred to kiosk from warehouse',
  `status` tinyint(5) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sale_logs`
--

CREATE TABLE `sale_logs` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` tinyint(4) UNSIGNED NOT NULL,
  `user_id` tinyint(4) UNSIGNED NOT NULL,
  `orignal_amount` float(10,2) UNSIGNED NOT NULL,
  `modified_amount` float(10,2) UNSIGNED NOT NULL,
  `discount` float(10,2) UNSIGNED DEFAULT NULL,
  `discount_status` tinyint(4) UNSIGNED DEFAULT NULL,
  `quantity` int(11) UNSIGNED DEFAULT NULL,
  `product_code` varchar(255) NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `sale_id` tinyint(4) UNSIGNED NOT NULL,
  `sale_date` datetime NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `screen_hints`
--

CREATE TABLE `screen_hints` (
  `id` int(11) UNSIGNED NOT NULL,
  `controller` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `hint` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `session_backups`
--

CREATE TABLE `session_backups` (
  `id` int(11) UNSIGNED NOT NULL,
  `controller` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `session_key` varchar(50) NOT NULL,
  `session_detail` mediumtext NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `attribute_name` varchar(80) NOT NULL,
  `attribute_value` text NOT NULL,
  `internal_setting` int(11) NOT NULL,
  `comment` text NOT NULL,
  `auto_load` tinyint(4) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `settings`
--
DELIMITER $$
CREATE TRIGGER `update_vat` AFTER UPDATE ON `settings` FOR EACH ROW BEGIN
IF(NEW.attribute_name = 'vat') THEN
UPDATE `hifiprofile_oc`.`oc_tax_rate` SET `rate`=NEW.attribute_value WHERE `tax_rate_id`=86;
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `stock_taking_details`
--

CREATE TABLE `stock_taking_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `stock_taking_reference_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `product_code` varchar(20) NOT NULL,
  `cost_price` float(8,2) UNSIGNED NOT NULL,
  `selling_price` float(8,2) UNSIGNED NOT NULL,
  `quantity` int(8) NOT NULL,
  `difference` tinyint(5) NOT NULL COMMENT '-1: Less; 1: More',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stock_taking_references`
--

CREATE TABLE `stock_taking_references` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `reference` varchar(50) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer`
--

CREATE TABLE `stock_transfer` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(10) UNSIGNED NOT NULL,
  `kiosk_placed_order_id` int(11) UNSIGNED NOT NULL,
  `sr_no` int(11) NOT NULL COMMENT 'used as sequence no',
  `kiosk_id` int(11) UNSIGNED DEFAULT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `cost_price` float(9,2) UNSIGNED NOT NULL,
  `static_cost` float(10,2) UNSIGNED DEFAULT NULL,
  `sale_price` float(9,2) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `kiosk_user_remarks` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `is_on_demand` tinyint(5) NOT NULL DEFAULT '0' COMMENT '1:yes',
  `flag` tinyint(4) UNSIGNED DEFAULT NULL COMMENT '1:Normal;2:Replace By Admin;3:Added By Admin',
  `product_processed` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0:unprocessed;1:processed| purpose for generating invoice for stock transferred to kiosk from warehouse',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_by_kiosk`
--

CREATE TABLE `stock_transfer_by_kiosk` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `cost_price` float(9,2) UNSIGNED NOT NULL,
  `sale_price` float(9,2) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `t1_product_sale_stats`
--

CREATE TABLE `t1_product_sale_stats` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `cost_price` double(12,6) NOT NULL,
  `selling_price` double(12,6) NOT NULL COMMENT 'without vat',
  `vat` double(12,6) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `temp_product_details`
--

CREATE TABLE `temp_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `temp_product_order_id` int(11) UNSIGNED NOT NULL,
  `product_receipt_id` int(11) NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `product_code` varchar(200) DEFAULT NULL,
  `quantity` int(20) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `discount` varchar(50) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `temp_product_orders`
--

CREATE TABLE `temp_product_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` float(8,2) NOT NULL,
  `product_receipt_id` int(11) NOT NULL,
  `fname` varchar(80) DEFAULT NULL,
  `lname` varchar(80) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `zip` varchar(15) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` int(255) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `state` varchar(80) DEFAULT NULL,
  `remarks` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_surplus`
--

CREATE TABLE `transfer_surplus` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_reference` varchar(255) DEFAULT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity` int(11) UNSIGNED DEFAULT NULL,
  `cost_price` float(10,3) NOT NULL,
  `sale_price` float(10,3) NOT NULL,
  `bulk_discount` varchar(30) NOT NULL,
  `vat_applied` tinyint(4) UNSIGNED DEFAULT NULL,
  `product_receipt_id` int(11) UNSIGNED DEFAULT NULL,
  `sequencr_number` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_understock`
--

CREATE TABLE `transfer_understock` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_reference` varchar(255) DEFAULT NULL,
  `customer_id` int(11) UNSIGNED DEFAULT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity` int(11) UNSIGNED DEFAULT NULL,
  `cost_price` float(10,3) NOT NULL,
  `sale_price` float(10,3) NOT NULL,
  `bulk_discount` varchar(30) NOT NULL,
  `vat_applied` tinyint(4) UNSIGNED DEFAULT NULL,
  `product_receipt_id` int(11) UNSIGNED DEFAULT NULL,
  `site_id` int(11) NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transient_stock`
--

CREATE TABLE `transient_stock` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quanities_sent` int(11) UNSIGNED NOT NULL,
  `quantities_received` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,2) NOT NULL,
  `selling_price` float(10,2) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `t_credit_payment_details`
--

CREATE TABLE `t_credit_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `kiosk_id` tinyint(4) NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `credit_cleared` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t_credit_product_details`
--

CREATE TABLE `t_credit_product_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `credit_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(20) DEFAULT NULL,
  `credit_by` int(11) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `credit_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t_credit_receipts`
--

CREATE TABLE `t_credit_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `kiosk_id` tinyint(4) NOT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `vat` int(10) NOT NULL,
  `bill_cost` float(10,2) NOT NULL,
  `bill_amount` float(10,2) NOT NULL,
  `orig_bill_amount` float(10,2) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `credit_amount` float(10,2) UNSIGNED NOT NULL,
  `bulk_discount` tinyint(5) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t_kiosk_product_sales`
--

CREATE TABLE `t_kiosk_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float(10,3) UNSIGNED DEFAULT NULL,
  `sale_price` float(10,3) UNSIGNED NOT NULL,
  `refund_price` float(10,2) UNSIGNED NOT NULL,
  `discount` varchar(50) NOT NULL,
  `discount_status` tinyint(4) UNSIGNED NOT NULL,
  `refund_gain` float(10,2) UNSIGNED NOT NULL,
  `sold_by` int(10) UNSIGNED NOT NULL,
  `refund_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
  `sale_type` tinyint(4) UNSIGNED DEFAULT NULL COMMENT 'COMMENT ''1: wholesale 0: kiosk''',
  `refund_status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1:normal refund; 2:faulty refund',
  `refund_remarks` varchar(255) NOT NULL,
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t_payment_details`
--

CREATE TABLE `t_payment_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_order_id` int(11) UNSIGNED NOT NULL COMMENT 'Not Used Field',
  `kiosk_id` int(11) DEFAULT '0',
  `product_receipt_id` int(11) UNSIGNED NOT NULL,
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `cr_ref_id` varchar(255) DEFAULT NULL,
  `pmt_ref_id` varchar(255) DEFAULT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `t_product_receipts`
--

CREATE TABLE `t_product_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` tinyint(4) DEFAULT '0',
  `agent_id` tinyint(5) UNSIGNED NOT NULL,
  `fname` varchar(70) DEFAULT NULL,
  `lname` varchar(70) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `bill_amount` float(10,2) UNSIGNED NOT NULL,
  `orig_bill_amount` float(10,2) UNSIGNED DEFAULT NULL,
  `bulk_discount` tinyint(5) UNSIGNED DEFAULT NULL,
  `vat` float(5,2) UNSIGNED NOT NULL,
  `vat_number` float(5,2) UNSIGNED NOT NULL,
  `processed_by` int(11) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `bill_cost` float(10,2) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `t_product_sale_stats`
--

CREATE TABLE `t_product_sale_stats` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `cost_price` double(12,6) NOT NULL,
  `selling_price` double(12,6) NOT NULL COMMENT 'without vat',
  `vat` double(12,6) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `t_product_sell_stats`
--

CREATE TABLE `t_product_sell_stats` (
  `id` int(11) NOT NULL,
  `kiosk_id` int(11) NOT NULL,
  `kiosk_name` varchar(250) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `cost_price` double(12,6) NOT NULL,
  `selling_price` double(12,6) NOT NULL COMMENT 'without vat',
  `vat` double(12,6) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` tinyint(5) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `t_temp_product_sales`
--

CREATE TABLE `t_temp_product_sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `cost_price` float UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `understock_level_orders`
--

CREATE TABLE `understock_level_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `unlock_payments`
--

CREATE TABLE `unlock_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `mobile_unlock_id` int(11) UNSIGNED NOT NULL,
  `mobile_unlock_sale_id` int(11) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` float(8,2) UNSIGNED NOT NULL,
  `payment_status` tinyint(4) UNSIGNED NOT NULL,
  `status` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `f_name` varchar(50) NOT NULL,
  `l_name` varchar(50) NOT NULL,
  `email` varchar(90) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `group_id` int(11) NOT NULL,
  `kiosk_assigned` text,
  `mobile` varchar(20) NOT NULL,
  `role` varchar(80) NOT NULL DEFAULT '3',
  `user_type` varchar(15) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `country` tinytext NOT NULL,
  `zip` varchar(20) NOT NULL,
  `start_from` date DEFAULT NULL,
  `tokenhash` varchar(255) DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  `level` tinyint(4) UNSIGNED DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  `temp_str` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_attendances`
--

CREATE TABLE `user_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kiosk_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `logged_in` datetime NOT NULL,
  `logged_out` datetime NOT NULL,
  `session_ide` varchar(255) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `day_off` datetime NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_setting`
--

CREATE TABLE `user_setting` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_session_key` varchar(200) NOT NULL,
  `setting_name` varchar(200) NOT NULL,
  `data` varchar(200) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_session_key` varchar(200) NOT NULL,
  `setting_name` varchar(200) NOT NULL,
  `data` varchar(200) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_stock`
--

CREATE TABLE `warehouse_stock` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `warehouse_vendor_id` int(11) UNSIGNED NOT NULL,
  `reference_number` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `price` float(8,2) NOT NULL,
  `in_out` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:stock in|0:stock out',
  `remarks` varchar(255) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_vendors`
--

CREATE TABLE `warehouse_vendors` (
  `id` int(11) UNSIGNED NOT NULL,
  `vendor` varchar(150) NOT NULL,
  `vendor_email` varchar(50) NOT NULL,
  `vendor_address_1` varchar(50) NOT NULL,
  `vendor_address_2` varchar(50) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `vendor_contact` varchar(15) NOT NULL,
  `country` varchar(100) NOT NULL,
  `status` tinyint(4) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `widgets`
--

CREATE TABLE `widgets` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `part_no` varchar(12) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acos`
--
ALTER TABLE `acos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_acos_lft_rght` (`lft`,`rght`),
  ADD KEY `idx_acos_alias` (`alias`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aros`
--
ALTER TABLE `aros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aros_lft_rght` (`lft`,`rght`),
  ADD KEY `idx_aros_alias` (`alias`);

--
-- Indexes for table `aros_acos`
--
ALTER TABLE `aros_acos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ARO_ACO_KEY` (`aro_id`,`aco_id`),
  ADD KEY `idx_aco_id` (`aco_id`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `foreign_key` (`foreign_key`,`attachment`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brand` (`brand`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category` (`category`,`parent_id`);

--
-- Indexes for table `categories_tmp`
--
ALTER TABLE `categories_tmp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category` (`category`,`parent_id`);

--
-- Indexes for table `center_orders`
--
ALTER TABLE `center_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `KEY_IDX` (`key`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `color_log`
--
ALTER TABLE `color_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment_mobile_purchases`
--
ALTER TABLE `comment_mobile_purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment_mobile_repairs`
--
ALTER TABLE `comment_mobile_repairs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment_mobile_re_sales`
--
ALTER TABLE `comment_mobile_re_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment_mobile_unlocks`
--
ALTER TABLE `comment_mobile_unlocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credit_payment_details`
--
ALTER TABLE `credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credit_product_details`
--
ALTER TABLE `credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credit_receipts`
--
ALTER TABLE `credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `csv_products`
--
ALTER TABLE `csv_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_product_price`
--
ALTER TABLE `customer_product_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_stocks`
--
ALTER TABLE `daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_targets`
--
ALTER TABLE `daily_targets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dashboard_data`
--
ALTER TABLE `dashboard_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dead_products`
--
ALTER TABLE `dead_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defective_bin`
--
ALTER TABLE `defective_bin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defective_bin_references`
--
ALTER TABLE `defective_bin_references`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defective_bin_transients`
--
ALTER TABLE `defective_bin_transients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defective_central_products`
--
ALTER TABLE `defective_central_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defective_kiosk_products`
--
ALTER TABLE `defective_kiosk_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defective_kiosk_references`
--
ALTER TABLE `defective_kiosk_references`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defective_kiosk_transients`
--
ALTER TABLE `defective_kiosk_transients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_activation_requests`
--
ALTER TABLE `device_activation_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_cookie_details`
--
ALTER TABLE `device_cookie_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_login_logs`
--
ALTER TABLE `device_login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faulty_conditions`
--
ALTER TABLE `faulty_conditions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faulty_products`
--
ALTER TABLE `faulty_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faulty_product_details`
--
ALTER TABLE `faulty_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fone_products`
--
ALTER TABLE `fone_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `function_conditions`
--
ALTER TABLE `function_conditions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `import_order_details`
--
ALTER TABLE `import_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `import_order_references`
--
ALTER TABLE `import_order_references`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_orders`
--
ALTER TABLE `invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_order_details`
--
ALTER TABLE `invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosks`
--
ALTER TABLE `kiosks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_credit_payment_details`
--
ALTER TABLE `kiosk_1_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_credit_product_details`
--
ALTER TABLE `kiosk_1_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_credit_receipts`
--
ALTER TABLE `kiosk_1_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_daily_stocks`
--
ALTER TABLE `kiosk_1_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_invoice_orders`
--
ALTER TABLE `kiosk_1_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_invoice_order_details`
--
ALTER TABLE `kiosk_1_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_payment_details`
--
ALTER TABLE `kiosk_1_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_products`
--
ALTER TABLE `kiosk_1_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_1_product_receipts`
--
ALTER TABLE `kiosk_1_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_1_product_sales`
--
ALTER TABLE `kiosk_1_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_credit_payment_details`
--
ALTER TABLE `kiosk_2_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_credit_product_details`
--
ALTER TABLE `kiosk_2_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_credit_receipts`
--
ALTER TABLE `kiosk_2_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_daily_stocks`
--
ALTER TABLE `kiosk_2_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_invoice_orders`
--
ALTER TABLE `kiosk_2_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_invoice_order_details`
--
ALTER TABLE `kiosk_2_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_payment_details`
--
ALTER TABLE `kiosk_2_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_products`
--
ALTER TABLE `kiosk_2_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_2_product_receipts`
--
ALTER TABLE `kiosk_2_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_2_product_sales`
--
ALTER TABLE `kiosk_2_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_credit_payment_details`
--
ALTER TABLE `kiosk_3_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_credit_product_details`
--
ALTER TABLE `kiosk_3_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_credit_receipts`
--
ALTER TABLE `kiosk_3_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_daily_stocks`
--
ALTER TABLE `kiosk_3_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_invoice_orders`
--
ALTER TABLE `kiosk_3_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_invoice_order_details`
--
ALTER TABLE `kiosk_3_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_payment_details`
--
ALTER TABLE `kiosk_3_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_products`
--
ALTER TABLE `kiosk_3_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_3_product_receipts`
--
ALTER TABLE `kiosk_3_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_3_product_sales`
--
ALTER TABLE `kiosk_3_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_credit_payment_details`
--
ALTER TABLE `kiosk_4_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_credit_product_details`
--
ALTER TABLE `kiosk_4_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_credit_receipts`
--
ALTER TABLE `kiosk_4_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_daily_stocks`
--
ALTER TABLE `kiosk_4_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_invoice_orders`
--
ALTER TABLE `kiosk_4_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_invoice_order_details`
--
ALTER TABLE `kiosk_4_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_payment_details`
--
ALTER TABLE `kiosk_4_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_products`
--
ALTER TABLE `kiosk_4_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_4_product_receipts`
--
ALTER TABLE `kiosk_4_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_4_product_sales`
--
ALTER TABLE `kiosk_4_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_credit_payment_details`
--
ALTER TABLE `kiosk_5_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_credit_product_details`
--
ALTER TABLE `kiosk_5_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_credit_receipts`
--
ALTER TABLE `kiosk_5_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_daily_stocks`
--
ALTER TABLE `kiosk_5_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_invoice_orders`
--
ALTER TABLE `kiosk_5_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_invoice_order_details`
--
ALTER TABLE `kiosk_5_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_payment_details`
--
ALTER TABLE `kiosk_5_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_products`
--
ALTER TABLE `kiosk_5_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_5_product_receipts`
--
ALTER TABLE `kiosk_5_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_5_product_sales`
--
ALTER TABLE `kiosk_5_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_credit_payment_details`
--
ALTER TABLE `kiosk_6_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_credit_product_details`
--
ALTER TABLE `kiosk_6_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_credit_receipts`
--
ALTER TABLE `kiosk_6_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_daily_stocks`
--
ALTER TABLE `kiosk_6_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_invoice_orders`
--
ALTER TABLE `kiosk_6_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_invoice_order_details`
--
ALTER TABLE `kiosk_6_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_payment_details`
--
ALTER TABLE `kiosk_6_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_products`
--
ALTER TABLE `kiosk_6_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_6_product_receipts`
--
ALTER TABLE `kiosk_6_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_6_product_sales`
--
ALTER TABLE `kiosk_6_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_credit_payment_details`
--
ALTER TABLE `kiosk_7_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_credit_product_details`
--
ALTER TABLE `kiosk_7_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_credit_receipts`
--
ALTER TABLE `kiosk_7_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_daily_stocks`
--
ALTER TABLE `kiosk_7_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_invoice_orders`
--
ALTER TABLE `kiosk_7_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_invoice_order_details`
--
ALTER TABLE `kiosk_7_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_payment_details`
--
ALTER TABLE `kiosk_7_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_products`
--
ALTER TABLE `kiosk_7_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_7_product_receipts`
--
ALTER TABLE `kiosk_7_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_7_product_sales`
--
ALTER TABLE `kiosk_7_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_credit_payment_details`
--
ALTER TABLE `kiosk_8_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_credit_product_details`
--
ALTER TABLE `kiosk_8_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_credit_receipts`
--
ALTER TABLE `kiosk_8_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_daily_stocks`
--
ALTER TABLE `kiosk_8_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_invoice_orders`
--
ALTER TABLE `kiosk_8_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_invoice_order_details`
--
ALTER TABLE `kiosk_8_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_payment_details`
--
ALTER TABLE `kiosk_8_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_products`
--
ALTER TABLE `kiosk_8_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_8_product_receipts`
--
ALTER TABLE `kiosk_8_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_8_product_sales`
--
ALTER TABLE `kiosk_8_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_credit_payment_details`
--
ALTER TABLE `kiosk_10000_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_credit_product_details`
--
ALTER TABLE `kiosk_10000_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_credit_receipts`
--
ALTER TABLE `kiosk_10000_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_daily_stocks`
--
ALTER TABLE `kiosk_10000_daily_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_invoice_orders`
--
ALTER TABLE `kiosk_10000_invoice_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_invoice_order_details`
--
ALTER TABLE `kiosk_10000_invoice_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_payment_details`
--
ALTER TABLE `kiosk_10000_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_products`
--
ALTER TABLE `kiosk_10000_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_10000_product_receipts`
--
ALTER TABLE `kiosk_10000_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_10000_product_sales`
--
ALTER TABLE `kiosk_10000_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_cancelled_order_products`
--
ALTER TABLE `kiosk_cancelled_order_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_ideal_products`
--
ALTER TABLE `kiosk_ideal_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk_orders`
--
ALTER TABLE `kiosk_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_order_products`
--
ALTER TABLE `kiosk_order_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_placed_orders`
--
ALTER TABLE `kiosk_placed_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_product_sales`
--
ALTER TABLE `kiosk_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_timings`
--
ALTER TABLE `kiosk_timings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_1`
--
ALTER TABLE `kiosk_transferred_stock_1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_2`
--
ALTER TABLE `kiosk_transferred_stock_2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_3`
--
ALTER TABLE `kiosk_transferred_stock_3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_4`
--
ALTER TABLE `kiosk_transferred_stock_4`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_5`
--
ALTER TABLE `kiosk_transferred_stock_5`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_6`
--
ALTER TABLE `kiosk_transferred_stock_6`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_7`
--
ALTER TABLE `kiosk_transferred_stock_7`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_8`
--
ALTER TABLE `kiosk_transferred_stock_8`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk_transferred_stock_10000`
--
ALTER TABLE `kiosk_transferred_stock_10000`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosk__products`
--
ALTER TABLE `kiosk__products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `kiosk__product_sales`
--
ALTER TABLE `kiosk__product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_blk_re_sales`
--
ALTER TABLE `mobile_blk_re_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_blk_re_sale_payments`
--
ALTER TABLE `mobile_blk_re_sale_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_blk_transfer_logs`
--
ALTER TABLE `mobile_blk_transfer_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_conditions`
--
ALTER TABLE `mobile_conditions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_models`
--
ALTER TABLE `mobile_models`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brand_id` (`brand_id`,`model`);

--
-- Indexes for table `mobile_placed_orders`
--
ALTER TABLE `mobile_placed_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_prices`
--
ALTER TABLE `mobile_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brand_id` (`brand_id`,`mobile_model_id`,`locked`,`grade`);

--
-- Indexes for table `mobile_purchases`
--
ALTER TABLE `mobile_purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_purchase_references`
--
ALTER TABLE `mobile_purchase_references`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_repairs`
--
ALTER TABLE `mobile_repairs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_repair_logs`
--
ALTER TABLE `mobile_repair_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_repair_parts`
--
ALTER TABLE `mobile_repair_parts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_repair_prices`
--
ALTER TABLE `mobile_repair_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brand_id` (`brand_id`,`mobile_model_id`,`problem_type`);

--
-- Indexes for table `mobile_repair_sales`
--
ALTER TABLE `mobile_repair_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_re_sales`
--
ALTER TABLE `mobile_re_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_re_sale_payments`
--
ALTER TABLE `mobile_re_sale_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_transfer_logs`
--
ALTER TABLE `mobile_transfer_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_unlocks`
--
ALTER TABLE `mobile_unlocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_unlock_logs`
--
ALTER TABLE `mobile_unlock_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_unlock_prices`
--
ALTER TABLE `mobile_unlock_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brand_id` (`brand_id`,`mobile_model_id`,`network_id`);

--
-- Indexes for table `mobile_unlock_sales`
--
ALTER TABLE `mobile_unlock_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `networks`
--
ALTER TABLE `networks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `on_demand_orders`
--
ALTER TABLE `on_demand_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `on_demand_placed_orders`
--
ALTER TABLE `on_demand_placed_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `on_demand_products`
--
ALTER TABLE `on_demand_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_disputes`
--
ALTER TABLE `order_disputes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kiosk_id` (`kiosk_id`,`kiosk_order_id`,`product_id`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pmt_logs`
--
ALTER TABLE `pmt_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `problem_types`
--
ALTER TABLE `problem_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `product_models`
--
ALTER TABLE `product_models`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brand_id` (`brand_id`,`model`);

--
-- Indexes for table `product_payments`
--
ALTER TABLE `product_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_receipts`
--
ALTER TABLE `product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_sale_stats`
--
ALTER TABLE `product_sale_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_sale_stats_new`
--
ALTER TABLE `product_sale_stats_new`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_sell_stats`
--
ALTER TABLE `product_sell_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_sell_stats_new`
--
ALTER TABLE `product_sell_stats_new`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reorder_levels`
--
ALTER TABLE `reorder_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repair_log`
--
ALTER TABLE `repair_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repair_payments`
--
ALTER TABLE `repair_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reserved_products`
--
ALTER TABLE `reserved_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `retail_customers`
--
ALTER TABLE `retail_customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `revert_stocks`
--
ALTER TABLE `revert_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_logs`
--
ALTER TABLE `sale_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `screen_hints`
--
ALTER TABLE `screen_hints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_backups`
--
ALTER TABLE `session_backups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attribute_name` (`attribute_name`);

--
-- Indexes for table `stock_taking_details`
--
ALTER TABLE `stock_taking_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_taking_references`
--
ALTER TABLE `stock_taking_references`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_transfer_by_kiosk`
--
ALTER TABLE `stock_transfer_by_kiosk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t1_product_sale_stats`
--
ALTER TABLE `t1_product_sale_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_product_details`
--
ALTER TABLE `temp_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_product_orders`
--
ALTER TABLE `temp_product_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transfer_surplus`
--
ALTER TABLE `transfer_surplus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transfer_understock`
--
ALTER TABLE `transfer_understock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transient_stock`
--
ALTER TABLE `transient_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_credit_payment_details`
--
ALTER TABLE `t_credit_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_credit_product_details`
--
ALTER TABLE `t_credit_product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_credit_receipts`
--
ALTER TABLE `t_credit_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_kiosk_product_sales`
--
ALTER TABLE `t_kiosk_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_payment_details`
--
ALTER TABLE `t_payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_product_receipts`
--
ALTER TABLE `t_product_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_product_sale_stats`
--
ALTER TABLE `t_product_sale_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_product_sell_stats`
--
ALTER TABLE `t_product_sell_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_temp_product_sales`
--
ALTER TABLE `t_temp_product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `understock_level_orders`
--
ALTER TABLE `understock_level_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unlock_payments`
--
ALTER TABLE `unlock_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_attendances`
--
ALTER TABLE `user_attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_ide` (`session_ide`);

--
-- Indexes for table `user_setting`
--
ALTER TABLE `user_setting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `warehouse_vendors`
--
ALTER TABLE `warehouse_vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `widgets`
--
ALTER TABLE `widgets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acos`
--
ALTER TABLE `acos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aros`
--
ALTER TABLE `aros`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aros_acos`
--
ALTER TABLE `aros_acos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories_tmp`
--
ALTER TABLE `categories_tmp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `center_orders`
--
ALTER TABLE `center_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `color_log`
--
ALTER TABLE `color_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comment_mobile_purchases`
--
ALTER TABLE `comment_mobile_purchases`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comment_mobile_repairs`
--
ALTER TABLE `comment_mobile_repairs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comment_mobile_re_sales`
--
ALTER TABLE `comment_mobile_re_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comment_mobile_unlocks`
--
ALTER TABLE `comment_mobile_unlocks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_payment_details`
--
ALTER TABLE `credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_product_details`
--
ALTER TABLE `credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_receipts`
--
ALTER TABLE `credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `csv_products`
--
ALTER TABLE `csv_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_product_price`
--
ALTER TABLE `customer_product_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_stocks`
--
ALTER TABLE `daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_targets`
--
ALTER TABLE `daily_targets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dashboard_data`
--
ALTER TABLE `dashboard_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dead_products`
--
ALTER TABLE `dead_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defective_bin`
--
ALTER TABLE `defective_bin`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defective_bin_references`
--
ALTER TABLE `defective_bin_references`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defective_bin_transients`
--
ALTER TABLE `defective_bin_transients`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defective_central_products`
--
ALTER TABLE `defective_central_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defective_kiosk_products`
--
ALTER TABLE `defective_kiosk_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defective_kiosk_references`
--
ALTER TABLE `defective_kiosk_references`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defective_kiosk_transients`
--
ALTER TABLE `defective_kiosk_transients`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_activation_requests`
--
ALTER TABLE `device_activation_requests`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_cookie_details`
--
ALTER TABLE `device_cookie_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_login_logs`
--
ALTER TABLE `device_login_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faulty_conditions`
--
ALTER TABLE `faulty_conditions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faulty_products`
--
ALTER TABLE `faulty_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faulty_product_details`
--
ALTER TABLE `faulty_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fone_products`
--
ALTER TABLE `fone_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `function_conditions`
--
ALTER TABLE `function_conditions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_order_details`
--
ALTER TABLE `import_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_order_references`
--
ALTER TABLE `import_order_references`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_orders`
--
ALTER TABLE `invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_order_details`
--
ALTER TABLE `invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosks`
--
ALTER TABLE `kiosks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_credit_payment_details`
--
ALTER TABLE `kiosk_1_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_credit_product_details`
--
ALTER TABLE `kiosk_1_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_credit_receipts`
--
ALTER TABLE `kiosk_1_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_daily_stocks`
--
ALTER TABLE `kiosk_1_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_invoice_orders`
--
ALTER TABLE `kiosk_1_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_invoice_order_details`
--
ALTER TABLE `kiosk_1_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_payment_details`
--
ALTER TABLE `kiosk_1_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_product_receipts`
--
ALTER TABLE `kiosk_1_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_1_product_sales`
--
ALTER TABLE `kiosk_1_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_credit_payment_details`
--
ALTER TABLE `kiosk_2_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_credit_product_details`
--
ALTER TABLE `kiosk_2_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_credit_receipts`
--
ALTER TABLE `kiosk_2_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_daily_stocks`
--
ALTER TABLE `kiosk_2_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_invoice_orders`
--
ALTER TABLE `kiosk_2_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_invoice_order_details`
--
ALTER TABLE `kiosk_2_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_payment_details`
--
ALTER TABLE `kiosk_2_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_product_receipts`
--
ALTER TABLE `kiosk_2_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_2_product_sales`
--
ALTER TABLE `kiosk_2_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_credit_payment_details`
--
ALTER TABLE `kiosk_3_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_credit_product_details`
--
ALTER TABLE `kiosk_3_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_credit_receipts`
--
ALTER TABLE `kiosk_3_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_daily_stocks`
--
ALTER TABLE `kiosk_3_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_invoice_orders`
--
ALTER TABLE `kiosk_3_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_invoice_order_details`
--
ALTER TABLE `kiosk_3_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_payment_details`
--
ALTER TABLE `kiosk_3_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_product_receipts`
--
ALTER TABLE `kiosk_3_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_3_product_sales`
--
ALTER TABLE `kiosk_3_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_credit_payment_details`
--
ALTER TABLE `kiosk_4_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_credit_product_details`
--
ALTER TABLE `kiosk_4_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_credit_receipts`
--
ALTER TABLE `kiosk_4_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_daily_stocks`
--
ALTER TABLE `kiosk_4_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_invoice_orders`
--
ALTER TABLE `kiosk_4_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_invoice_order_details`
--
ALTER TABLE `kiosk_4_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_payment_details`
--
ALTER TABLE `kiosk_4_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_product_receipts`
--
ALTER TABLE `kiosk_4_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_4_product_sales`
--
ALTER TABLE `kiosk_4_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_credit_payment_details`
--
ALTER TABLE `kiosk_5_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_credit_product_details`
--
ALTER TABLE `kiosk_5_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_credit_receipts`
--
ALTER TABLE `kiosk_5_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_daily_stocks`
--
ALTER TABLE `kiosk_5_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_invoice_orders`
--
ALTER TABLE `kiosk_5_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_invoice_order_details`
--
ALTER TABLE `kiosk_5_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_payment_details`
--
ALTER TABLE `kiosk_5_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_product_receipts`
--
ALTER TABLE `kiosk_5_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_5_product_sales`
--
ALTER TABLE `kiosk_5_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_credit_payment_details`
--
ALTER TABLE `kiosk_6_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_credit_product_details`
--
ALTER TABLE `kiosk_6_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_credit_receipts`
--
ALTER TABLE `kiosk_6_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_daily_stocks`
--
ALTER TABLE `kiosk_6_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_invoice_orders`
--
ALTER TABLE `kiosk_6_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_invoice_order_details`
--
ALTER TABLE `kiosk_6_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_payment_details`
--
ALTER TABLE `kiosk_6_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_products`
--
ALTER TABLE `kiosk_6_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_product_receipts`
--
ALTER TABLE `kiosk_6_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_6_product_sales`
--
ALTER TABLE `kiosk_6_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_credit_payment_details`
--
ALTER TABLE `kiosk_7_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_credit_product_details`
--
ALTER TABLE `kiosk_7_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_credit_receipts`
--
ALTER TABLE `kiosk_7_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_daily_stocks`
--
ALTER TABLE `kiosk_7_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_invoice_orders`
--
ALTER TABLE `kiosk_7_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_invoice_order_details`
--
ALTER TABLE `kiosk_7_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_payment_details`
--
ALTER TABLE `kiosk_7_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_product_receipts`
--
ALTER TABLE `kiosk_7_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_7_product_sales`
--
ALTER TABLE `kiosk_7_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_credit_payment_details`
--
ALTER TABLE `kiosk_8_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_credit_product_details`
--
ALTER TABLE `kiosk_8_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_credit_receipts`
--
ALTER TABLE `kiosk_8_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_daily_stocks`
--
ALTER TABLE `kiosk_8_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_invoice_orders`
--
ALTER TABLE `kiosk_8_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_invoice_order_details`
--
ALTER TABLE `kiosk_8_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_payment_details`
--
ALTER TABLE `kiosk_8_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_product_receipts`
--
ALTER TABLE `kiosk_8_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_8_product_sales`
--
ALTER TABLE `kiosk_8_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_credit_payment_details`
--
ALTER TABLE `kiosk_10000_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_credit_product_details`
--
ALTER TABLE `kiosk_10000_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_credit_receipts`
--
ALTER TABLE `kiosk_10000_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_daily_stocks`
--
ALTER TABLE `kiosk_10000_daily_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_invoice_orders`
--
ALTER TABLE `kiosk_10000_invoice_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_invoice_order_details`
--
ALTER TABLE `kiosk_10000_invoice_order_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_payment_details`
--
ALTER TABLE `kiosk_10000_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_product_receipts`
--
ALTER TABLE `kiosk_10000_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_10000_product_sales`
--
ALTER TABLE `kiosk_10000_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_cancelled_order_products`
--
ALTER TABLE `kiosk_cancelled_order_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_ideal_products`
--
ALTER TABLE `kiosk_ideal_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_orders`
--
ALTER TABLE `kiosk_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_order_products`
--
ALTER TABLE `kiosk_order_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_placed_orders`
--
ALTER TABLE `kiosk_placed_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_product_sales`
--
ALTER TABLE `kiosk_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_timings`
--
ALTER TABLE `kiosk_timings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_1`
--
ALTER TABLE `kiosk_transferred_stock_1`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_2`
--
ALTER TABLE `kiosk_transferred_stock_2`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_3`
--
ALTER TABLE `kiosk_transferred_stock_3`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_4`
--
ALTER TABLE `kiosk_transferred_stock_4`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_5`
--
ALTER TABLE `kiosk_transferred_stock_5`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_6`
--
ALTER TABLE `kiosk_transferred_stock_6`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_7`
--
ALTER TABLE `kiosk_transferred_stock_7`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_8`
--
ALTER TABLE `kiosk_transferred_stock_8`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk_transferred_stock_10000`
--
ALTER TABLE `kiosk_transferred_stock_10000`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kiosk__product_sales`
--
ALTER TABLE `kiosk__product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_blk_re_sales`
--
ALTER TABLE `mobile_blk_re_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_blk_re_sale_payments`
--
ALTER TABLE `mobile_blk_re_sale_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_blk_transfer_logs`
--
ALTER TABLE `mobile_blk_transfer_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_conditions`
--
ALTER TABLE `mobile_conditions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_models`
--
ALTER TABLE `mobile_models`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_placed_orders`
--
ALTER TABLE `mobile_placed_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_prices`
--
ALTER TABLE `mobile_prices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_purchases`
--
ALTER TABLE `mobile_purchases`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_purchase_references`
--
ALTER TABLE `mobile_purchase_references`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_repairs`
--
ALTER TABLE `mobile_repairs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_repair_logs`
--
ALTER TABLE `mobile_repair_logs`
  MODIFY `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_repair_parts`
--
ALTER TABLE `mobile_repair_parts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_repair_prices`
--
ALTER TABLE `mobile_repair_prices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_repair_sales`
--
ALTER TABLE `mobile_repair_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_re_sales`
--
ALTER TABLE `mobile_re_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_re_sale_payments`
--
ALTER TABLE `mobile_re_sale_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_transfer_logs`
--
ALTER TABLE `mobile_transfer_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_unlocks`
--
ALTER TABLE `mobile_unlocks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_unlock_logs`
--
ALTER TABLE `mobile_unlock_logs`
  MODIFY `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_unlock_prices`
--
ALTER TABLE `mobile_unlock_prices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_unlock_sales`
--
ALTER TABLE `mobile_unlock_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `networks`
--
ALTER TABLE `networks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `on_demand_orders`
--
ALTER TABLE `on_demand_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `on_demand_placed_orders`
--
ALTER TABLE `on_demand_placed_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `on_demand_products`
--
ALTER TABLE `on_demand_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_disputes`
--
ALTER TABLE `order_disputes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pmt_logs`
--
ALTER TABLE `pmt_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `problem_types`
--
ALTER TABLE `problem_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_models`
--
ALTER TABLE `product_models`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_payments`
--
ALTER TABLE `product_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_receipts`
--
ALTER TABLE `product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sale_stats`
--
ALTER TABLE `product_sale_stats`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sale_stats_new`
--
ALTER TABLE `product_sale_stats_new`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sell_stats`
--
ALTER TABLE `product_sell_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sell_stats_new`
--
ALTER TABLE `product_sell_stats_new`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reorder_levels`
--
ALTER TABLE `reorder_levels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repair_log`
--
ALTER TABLE `repair_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repair_payments`
--
ALTER TABLE `repair_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reserved_products`
--
ALTER TABLE `reserved_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `retail_customers`
--
ALTER TABLE `retail_customers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revert_stocks`
--
ALTER TABLE `revert_stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_logs`
--
ALTER TABLE `sale_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `screen_hints`
--
ALTER TABLE `screen_hints`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `session_backups`
--
ALTER TABLE `session_backups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_taking_details`
--
ALTER TABLE `stock_taking_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_taking_references`
--
ALTER TABLE `stock_taking_references`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transfer_by_kiosk`
--
ALTER TABLE `stock_transfer_by_kiosk`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t1_product_sale_stats`
--
ALTER TABLE `t1_product_sale_stats`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_product_details`
--
ALTER TABLE `temp_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_product_orders`
--
ALTER TABLE `temp_product_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_surplus`
--
ALTER TABLE `transfer_surplus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_understock`
--
ALTER TABLE `transfer_understock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transient_stock`
--
ALTER TABLE `transient_stock`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_credit_payment_details`
--
ALTER TABLE `t_credit_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_credit_product_details`
--
ALTER TABLE `t_credit_product_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_credit_receipts`
--
ALTER TABLE `t_credit_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_kiosk_product_sales`
--
ALTER TABLE `t_kiosk_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_payment_details`
--
ALTER TABLE `t_payment_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_product_receipts`
--
ALTER TABLE `t_product_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_product_sale_stats`
--
ALTER TABLE `t_product_sale_stats`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_product_sell_stats`
--
ALTER TABLE `t_product_sell_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_temp_product_sales`
--
ALTER TABLE `t_temp_product_sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `understock_level_orders`
--
ALTER TABLE `understock_level_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unlock_payments`
--
ALTER TABLE `unlock_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_attendances`
--
ALTER TABLE `user_attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_setting`
--
ALTER TABLE `user_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warehouse_vendors`
--
ALTER TABLE `warehouse_vendors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `widgets`
--
ALTER TABLE `widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
