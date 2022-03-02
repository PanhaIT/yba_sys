DROP TRIGGER `zLocationGroupBfInsert`;
DROP TRIGGER `zLocationGroupBfUpdate`;

-- Dumping structure for trigger super_retail.zLocationGroupBfInsert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `zLocationGroupBfInsert` BEFORE INSERT ON `location_groups` FOR EACH ROW BEGIN
   DECLARE isAllowNeg TINYINT(4);
   DECLARE isComTrans TINYINT(4);
	IF NEW.sys_code = "" OR NEW.sys_code IS NULL OR NEW.location_group_type_id IS NULL OR NEW.code = "" OR NEW.code IS NULL OR NEW.name = "" OR NEW.name IS NULL THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid Data';
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;


-- Dumping structure for trigger super_retail.zLocationGroupBfUpdate
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `zLocationGroupBfUpdate` BEFORE UPDATE ON `location_groups` FOR EACH ROW BEGIN
	DECLARE isCheck TINYINT(4);
	DECLARE isAllowNeg TINYINT(4);
	DECLARE isComTrans TINYINT(4);
	IF OLD.is_active = 1 AND NEW.is_active =2 THEN
		SELECT COUNT(id) INTO isCheck FROM locations WHERE location_group_id = OLD.id AND is_active = 1;
		IF isCheck > 0 THEN 
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Data cloud not been delete';
		END IF;
	END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;
