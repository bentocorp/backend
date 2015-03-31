ALTER TABLE `bento`.`User` 
ADD COLUMN `coupon_code` VARCHAR(45) NULL DEFAULT NULL AFTER `stripe_customer_obj`,
ADD UNIQUE INDEX `coupon_code_UNIQUE` (`coupon_code` ASC);


CREATE TABLE `bento`.`CouponUserHash` (
  `pk_CouponUserHash` VARCHAR(20) NOT NULL,
  `count` INT NULL,
  PRIMARY KEY (`pk_CouponUserHash`));

ALTER TABLE `bento`.`CouponUserHash` 
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `count`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

# Run the Bootstrap migration



