SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `z1InventoryAfInsert` AFTER INSERT ON `1_inventories` FOR EACH ROW BEGIN
   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN
       UPDATE `transaction_details` SET `loc_inventory` =  (`loc_inventory` + 1) WHERE  `id`= NEW.transaction_detail_id;
   END IF;
END//
DELIMITER;
SET SQL_MODE=@OLDTMP_SQL_MODE;
