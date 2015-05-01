CREATE TABLE `bento`.`MealType` (
  `pk_MealType` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`pk_MealType`));

ALTER TABLE `bento`.`MealType` 
CHANGE COLUMN `pk_MealType` `pk_MealType` INT(11) NOT NULL AUTO_INCREMENT ;

ALTER TABLE `bento`.`Menu` 
ADD COLUMN `menu_type` ENUM('Fixed', 'Custom') NULL DEFAULT 'Custom' AFTER `published`,
ADD COLUMN `fk_MealType` INT NULL DEFAULT 3 AFTER `menu_type`;






