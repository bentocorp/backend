ALTER TABLE `bento`.`LiveInventory` 
ADD COLUMN `qty_saved` SMALLINT(6) UNSIGNED NULL DEFAULT NULL AFTER `change_reason`,
ADD COLUMN `sold_out` VARCHAR(45) NULL AFTER `qty_saved`;
ALTER TABLE `bento`.`LiveInventory` 
CHANGE COLUMN `sold_out` `sold_out` TINYINT(1) NULL DEFAULT 0 ;






