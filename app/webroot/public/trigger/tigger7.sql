SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `z1InventoryDetailAfUpdate` AFTER UPDATE ON `1_inventory_total_details` FOR EACH ROW BEGIN
   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN
       UPDATE `transaction_details` SET `loc_inventory_detail` =  (`loc_inventory_detail` + 1) WHERE  `id`= NEW.transaction_detail_id;
   END IF;
END//
DELIMITER;
SET SQL_MODE=@OLDTMP_SQL_MODE;
