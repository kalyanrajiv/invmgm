CREATE TRIGGER `add_agent` AFTER INSERT ON `agents`
 FOR EACH ROW BEGIN
INSERT INTO `hifiprofile_oc`.`oc_custom_field_value` (custom_field_value_id,custom_field_id,sort_order) VALUES (NEW.id,1,0);
INSERT INTO `hifiprofile_oc`.`oc_custom_field_value_description` (custom_field_value_id,language_id,custom_field_id,name) VALUES (NEW.id,1,1,NEW.name);
END

CREATE TRIGGER `add_brand` AFTER INSERT ON `brands`
 FOR EACH ROW BEGIN
INSERT INTO `hifiprofile_oc`.`oc_manufacturer` (
manufacturer_id,name,image,sort_order
)
VALUES ( NEW.id , NEW.brand , '' ,0 );
INSERT INTO `hifiprofile_oc`.`oc_manufacturer_to_store`(
manufacturer_id,store_id)     
VALUES (NEW.id , 0);
END

CREATE TRIGGER `add_category` AFTER INSERT ON `categories`
 FOR EACH ROW BEGIN
INSERT INTO `hifiprofile_oc`.`oc_category` ( category_id,image,parent_id,top,sort_order,status,date_added,date_modified)VALUES (NEW.id,NEW.image,NEW.parent_id,0,0,NEW.status,NEW.created,NEW.modified);
INSERT INTO `hifiprofile_oc`.`oc_category_description` (category_id,language_id,name,description,meta_title,meta_description,meta_keyword)VALUES (NEW.id,1,NEW.category,NEW.description,NEW.category,'','');
INSERT INTO `hifiprofile_oc`.`oc_category_to_store` ( category_id,store_id)VALUES (NEW.id,0);
END

CREATE TRIGGER `add_customer` AFTER INSERT ON `customers`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `delete_agent` AFTER DELETE ON `agents`
 FOR EACH ROW BEGIN
DELETE FROM `hifiprofile_oc`.`oc_custom_field_value` WHERE custom_field_value_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_custom_field_value_description` WHERE custom_field_value_id=OLD.id;
END

CREATE TRIGGER `delete_brand` AFTER DELETE ON `brands`
 FOR EACH ROW BEGIN
DELETE FROM `hifiprofile_oc`.`oc_manufacturer` WHERE manufacturer_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_manufacturer_to_store` WHERE manufacturer_id=OLD.id;
END

CREATE TRIGGER `delete_category` AFTER DELETE ON `categories`
 FOR EACH ROW BEGIN
DELETE FROM `hifiprofile_oc`.`oc_category` WHERE category_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_category_description` WHERE category_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_category_to_store` WHERE category_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_category_path` WHERE category_id=OLD.id;
END

CREATE TRIGGER `delete_customer` AFTER DELETE ON `customers`
 FOR EACH ROW BEGIN
DELETE FROM `hifiprofile_oc`.`oc_customer` WHERE customer_id=OLD.id;
DELETE FROM `hifiprofile_oc`.`oc_address` WHERE customer_id=OLD.id;
END

CREATE TRIGGER `delete_oc_product` AFTER DELETE ON `invoice_order_details`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `delete_products` AFTER DELETE ON `products`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `add_product` AFTER INSERT ON `products`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `trigger_oc_order` AFTER INSERT ON `invoice_order_details`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `trigger_static` AFTER INSERT ON `invoice_orders`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `update_` AFTER UPDATE ON `colors`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `update_agent` AFTER UPDATE ON `agents`
 FOR EACH ROW BEGIN
UPDATE `hifiprofile_oc`.`oc_custom_field_value` SET custom_field_id = 1, sort_order=0 WHERE custom_field_value_id = NEW.id;
UPDATE `hifiprofile_oc`.`oc_custom_field_value_description` SET language_id = 1, custom_field_id = 1, name = NEW.name WHERE custom_field_value_id = NEW.id;
END

CREATE TRIGGER `update_brand` AFTER UPDATE ON `brands`
 FOR EACH ROW BEGIN UPDATE 
`hifiprofile_oc`.`oc_manufacturer` SET name=NEW.brand,image='',sort_order=0 WHERE 
manufacturer_id=NEW.id; UPDATE `hifiprofile_oc`.`oc_manufacturer_to_store` SET store_id=0 WHERE 
manufacturer_id=NEW.id; 
END

CREATE TRIGGER `update_category` AFTER UPDATE ON `categories`
 FOR EACH ROW BEGIN
UPDATE `hifiprofile_oc`.`oc_category` SET
image=NEW.image,parent_id=NEW.parent_id,top=0,sort_order=0,status=NEW.status,date_modified=NEW.modified WHERE category_id=NEW.id;
UPDATE `hifiprofile_oc`.`oc_category_description` SET
language_id=1,name=NEW.category,description=NEW.description,meta_title=NEW.category,meta_description='',meta_keyword='' WHERE category_id=NEW.id;
UPDATE `hifiprofile_oc`.`oc_category_to_store` SET
store_id=0 WHERE category_id=NEW.id;
END

CREATE TRIGGER `update_order_qry_n_amount` AFTER UPDATE ON `invoice_orders`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `update_order_qty` AFTER UPDATE ON `invoice_order_details`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `update_products` AFTER UPDATE ON `products`
 FOR EACH ROW BEGIN
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

CREATE TRIGGER `update_vat` AFTER UPDATE ON `settings`
 FOR EACH ROW BEGIN
IF(NEW.attribute_name = 'vat') THEN
UPDATE `hifiprofile_oc`.`oc_tax_rate` SET `rate`=NEW.attribute_value WHERE `tax_rate_id`=86;
END IF;
END

CREATE TRIGGER `delete_order` AFTER DELETE ON `invoice_orders`
 FOR EACH ROW BEGIN
        DELETE FROM `hifiprofile_oc`.`oc_order` WHERE `order_id`=OLD.id;
        DELETE FROM `hifiprofile_oc`.`oc_order_product` WHERE `order_id`=OLD.id;
        DELETE FROM `hifiprofile_oc`.`oc_order_history` WHERE `order_id`=OLD.id;
        DELETE FROM `hifiprofile_oc`.`oc_order_total` WHERE `order_id`=OLD.id;
END

CREATE TRIGGER `update_customer` AFTER UPDATE ON `customers`
 FOR EACH ROW BEGIN
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
