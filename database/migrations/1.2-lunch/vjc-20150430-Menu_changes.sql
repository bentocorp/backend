
# MealType table
CREATE TABLE `bento`.`MealType` (
  `pk_MealType` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`pk_MealType`));

ALTER TABLE `bento`.`MealType` 
CHANGE COLUMN `pk_MealType` `pk_MealType` INT(11) NOT NULL AUTO_INCREMENT ;

# Add menu_type and MealType to the Menu table 
ALTER TABLE `bento`.`Menu` 
ADD COLUMN `menu_type` ENUM('Fixed', 'Custom') NULL DEFAULT 'Custom' AFTER `published`,
ADD COLUMN `fk_MealType` INT NULL DEFAULT 3 AFTER `menu_type`;


# Service area changes
UPDATE `bento`.`settings` SET `key`='serviceArea_dinner' WHERE `key`='serviceArea';
INSERT INTO `bento`.`settings` (`key`, `value`) VALUES ('serviceArea_lunch', '-122.44983680000001,37.8095806,0.0 -122.44335350000001,37.77783170000001,0.0 -122.43567470000002,37.7460824,0.0 -122.37636569999998,37.7490008,0.0 -122.37928390000002,37.78611430000001,0.0 -122.40348819999998,37.8135812,0.0 -122.44983680000001,37.8095806,0.0');




