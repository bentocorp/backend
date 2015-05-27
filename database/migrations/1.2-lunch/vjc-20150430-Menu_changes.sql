
## ##
## Lunch DB Migrations
## ##

# MealType table
CREATE TABLE `MealType` (
  `pk_MealType` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `startTime` time DEFAULT NULL,
  PRIMARY KEY (`pk_MealType`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


# Insert MealType data
INSERT INTO `MealType` (`pk_MealType`,`name`,`order`,`startTime`) VALUES (1,'brunch',1,NULL);
INSERT INTO `MealType` (`pk_MealType`,`name`,`order`,`startTime`) VALUES (2,'lunch',2,'11:30:00');
INSERT INTO `MealType` (`pk_MealType`,`name`,`order`,`startTime`) VALUES (3,'dinner',3,'16:30:00');

## Menu table

# Add menu_type and MealType to the Menu table 
ALTER TABLE `bento`.`Menu` 
ADD COLUMN `menu_type` ENUM('fixed', 'custom') NULL DEFAULT 'Custom' AFTER `published`,
ADD COLUMN `fk_MealType` INT NULL DEFAULT 3 AFTER `menu_type`;

# Add a unique index that is the for_date, and the MealType
ALTER TABLE `bento`.`Menu` 
ADD UNIQUE INDEX `Menu_Type` (`for_date` ASC, `fk_MealType` ASC);


# Service area changes
UPDATE `bento`.`settings` SET `key`='serviceArea_dinner' WHERE `key`='serviceArea';
INSERT INTO `bento`.`settings` (`key`, `value`) VALUES ('serviceArea_lunch', '-122.44983680000001,37.8095806,0.0 -122.44335350000001,37.77783170000001,0.0 -122.43567470000002,37.7460824,0.0 -122.37636569999998,37.7490008,0.0 -122.37928390000002,37.78611430000001,0.0 -122.40348819999998,37.8135812,0.0 -122.44983680000001,37.8095806,0.0');


# Settings changes
INSERT INTO `bento`.`settings` (`key`, `value`) VALUES ('tzName', 'America/Los_Angeles');

ALTER TABLE `bento`.`settings` 
ADD COLUMN `public` TINYINT NULL DEFAULT 0 AFTER `updated_at`;

UPDATE `bento`.`settings` SET `public`='1' WHERE `key`='serviceArea_dinner';
UPDATE `bento`.`settings` SET `public`='1' WHERE `key`='serviceArea_lunch';
UPDATE `bento`.`settings` SET `public`='1' WHERE `key`='status';
UPDATE `bento`.`settings` SET `public`='1' WHERE `key`='tzName';

INSERT INTO `bento`.`settings` (`key`, `value`, `public`) VALUES ('fk_MealType_mode', '2', '0');

INSERT INTO `bento`.`settings` (`key`, `value`, `public`) VALUES ('buffer_minutes', '60', '1');

### ^^ Above already run on Dev ^^ ###






