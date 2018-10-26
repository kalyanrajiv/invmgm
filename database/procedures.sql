-------------1 only for main site---------------
DELIMITER $$
CREATE DEFINER=`hifi_master`@`%` PROCEDURE `do_calculation`(IN `new_order_id` INT(11), IN `vat_applied` INT(11))
    NO SQL
BEGIN
  SELECT value FROM `hifiprofile_oc`.`oc_order_total` WHERE order_id = new_order_id AND code = "total" limit 1 INTO @total1;
  IF(vat_applied = 0) THEN
        SELECT `attribute_value` FROM `hifi_master`.`settings` WHERE `attribute_name` = "vat"  INTO @vat;
  ELSE
        SET @vat:= 0;
  END IF;
  
    SET @vat_value =  (@total1 - @total1/(1+(@vat/100)));
    IF(vat_applied = 0) THEN
    SET @final_val =  @total1 - @vat_value;    
    ELSE
    SET @final_val =  @total1 ;    
    END IF; 

    IF (vat_applied = 0) THEN
     INSERT INTO `hifiprofile_oc`.`oc_order_total` SET
        order_id = new_order_id,
        code = "tax",
        title = "VAT (20%)",
        value = cast(@vat_value as DECIMAL(10,2)),
        sort_order = 5;
    ELSE
        SET @vat := 0;
    END IF;
    
    INSERT INTO `hifiprofile_oc`.`oc_order_total` SET
    order_id = new_order_id,
    code = "sub_total",
    title = "Sub-Total",
    value = cast(@final_val as DECIMAL(10,2)),
    sort_order = 1;
 
END$$
DELIMITER ;

-------------2 only for main site---------------
DELIMITER $$
CREATE DEFINER=`hifi_master`@`%` PROCEDURE `do_calculation_update`(IN `new_order_id` INT(11), IN `vat_applied` INT(11))
    NO SQL
BEGIN
  SELECT value FROM `hifiprofile_oc`.`oc_order_total` WHERE order_id = new_order_id AND code = "total" limit 1 INTO @total1;
  IF(vat_applied = 0) THEN
    SELECT attribute_value FROM `hifi_master`.`settings` WHERE attribute_name = "vat"  INTO @vat;
  ELSE
        SET @vat:= 0;
  END IF;
  
  SET @vat_value =  (@total1 - @total1/(1+(@vat/100)));
  IF(vat_applied = 0) THEN
    SET @final_val =  @total1 - @vat_value;
  ELSE
        SET @final_val =  @total1;
  END IF;  
  
  
  IF(vat_applied = 0) THEN
        UPDATE `hifiprofile_oc`.`oc_order_total` SET
        value = cast(@vat_value as DECIMAL(10,2))
        WHERE order_id = new_order_id AND
        code = "tax";
  ELSE
        SET @vat:= 0;
  END IF;        
    
 UPDATE `hifiprofile_oc`.`oc_order_total` SET
    value = cast(@final_val as DECIMAL(10,2))  
WHERE order_id = new_order_id AND
code = "sub_total";
 
END$$
DELIMITER ;