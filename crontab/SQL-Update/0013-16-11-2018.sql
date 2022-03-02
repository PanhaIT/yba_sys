

-- Dumping structure for trigger super_retail.z3InventoryAfInsert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `z2InventoryAfInsert` AFTER INSERT ON `2_inventories` FOR EACH ROW BEGIN
   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN
       UPDATE `transaction_details` SET `loc_inventory` =  (`loc_inventory` + 1) WHERE  `id`= NEW.transaction_detail_id;
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;


-- Dumping structure for trigger super_retail.z3InventoryTotalAfInsert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `z2InventoryTotalAfInsert` AFTER INSERT ON `2_inventory_totals` FOR EACH ROW BEGIN
   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN
       UPDATE `transaction_details` SET `loc_inventory_total` =  (`loc_inventory_total` + 1) WHERE  `id`= NEW.transaction_detail_id;
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

UPDATE `transaction_details` SET `loc_inventory`=1, `loc_inventory_total`=1 WHERE  1;
