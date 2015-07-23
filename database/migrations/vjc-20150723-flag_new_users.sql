
ALTER TABLE `bento`.`User` 
ADD COLUMN `has_ordered` TINYINT(1) NULL DEFAULT 0 AFTER `is_top_customer`;

update bento.User set has_ordered = 1;

CREATE TABLE User_tmp AS
select distinct email
	from User u
	left join `Order` o on u.pk_user = o.fk_User
	where pk_Order IS NULL AND NOT is_test 
;

update User set has_ordered = 0
where User.email in ( 
	select *
	from User_tmp
)
;





