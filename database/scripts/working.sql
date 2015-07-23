

# open orders
select
	o.pk_Order,
	o.created_at as order_created_at,
	o.street, o.city, o.state, o.zip,
	os.`status`,
	concat(u.firstname, ' ', u.lastname) as user_name,
	u.phone as user_phone,
	concat(d.firstname, ' ', d.lastname) as driver_name,
    d.pk_Driver
from `Order` o
left join OrderStatus os on (o.pk_Order = os.fk_Order)
left join Driver d on (os.fk_Driver = d.pk_Driver)
left join User u on (o.fk_User = u.pk_User)
where os.status IN ('Open', 'En Route')
ORDER BY order_created_at DESC;


# current drivers
            SELECT d.* 
            FROM DriverInventory di
            left join Driver d on (di.fk_Driver = d.pk_Driver)
            group by fk_Driver;



#get live and driver
        # count stuff in the LiveInventory
        SELECT li.fk_item, d.`name`, d.short_name, li.qty as lqty, d.type,
                        (select sum(di.qty) from DriverInventory di where di.fk_item = li.fk_item group by fk_item) as dqty
        FROM bento.LiveInventory li
        left join Dish d on (li.fk_item = d.pk_Dish)

        union
        
        # for Drivers, roll into items that are NOT in the LiveInventory
        select di.fk_item, d.`name`, d.short_name, li.qty as lqty, d.type,
                sum(di.qty) as dqty
        from DriverInventory di
        left join Dish d on (di.fk_item = d.pk_Dish)
        left join LiveInventory li on (li.fk_item = di.fk_item)
        where li.qty IS NULL
        group by di.fk_item

        order by type asc, name asc
;



# Clear crap out
truncate table `Order`;
truncate table OrderLog;
truncate table OrderStatus;
truncate table OrderStatusLog;
truncate table PendingOrder;
truncate table DriverInventory;
truncate table DriverInventoryLog;
truncate table LiveInventory;
truncate table CustomerBentoBox;



# get orders for survey
select 
	o.pk_Order, o.created_at, u.email, 
	d1.`name` as main_name,
	d2.`name` as side1_name,
	d3.`name` as side2_name,
	d4.`name` as side3_name,
	d5.`name` as side4_name,
    os.`status`
from CustomerBentoBox cbb
left join Dish d1 on (cbb.fk_main = d1.pk_Dish)
left join Dish d2 on (cbb.fk_side1 = d2.pk_Dish)
left join Dish d3 on (cbb.fk_side2 = d3.pk_Dish)
left join Dish d4 on (cbb.fk_side3 = d4.pk_Dish)
left join Dish d5 on (cbb.fk_side4 = d5.pk_Dish)
left join `Order` o on (o.pk_Order = cbb.fk_Order)
left join OrderStatus os on (o.pk_Order = os.fk_Order)
left join User u on (u.pk_User = o.fk_User)
where cbb.created_at like '%2015-03-13%'
	AND status != 'Cancelled'
;



# Get tips within a range
SELECT 
	o.pk_Order,
    o.created_at,
    o.amount,
    o.tip,
    d.firstname, d.lastname, d.email
FROM bento.`Order` o
left join OrderStatus os on (os.fk_Order = o.pk_Order)
left join Driver d on (d.pk_Driver = os.fk_Driver)
where o.created_at >= '2015-03-08 00:00:00' AND o.created_at <= '2015-03-13 23:59:59'
	AND os.status != 'Cancelled'
;


# Find drivers who can complete a bento box
select * from (
	SELECT fk_Driver, count(*) as `count`
	FROM DriverInventory
	where (fk_item = 1 AND qty >= 1) || (fk_item = 2 AND qty >= 50)
	group by fk_Driver
) t
where t.count >= 2
;


# Find number of bento boxes delivered
SELECT * 
FROM bento.CustomerBentoBox cbb
left join OrderStatus os on (cbb.fk_Order = os.fk_Order) 
where cbb.created_at >= '2015-03-16'
    AND os.status != 'Cancelled'
;


# ----------------------------------------------------------------------------------------------------------
# Sales Counting
# ----------------------------------------------------------------------------------------------------------

# Count bento boxes by day
select count(*) from CustomerBentoBox where fk_Order in (
	select fk_Order from OrderStatus where created_at like '2015-03-24 %'
    );
    
# Count bento boxes by day 2
select count(*) from CustomerBentoBox where created_at >= '2015-03-23';


# Count Lunch bento boxes by day 
select count(*) from CustomerBentoBox 
where 
	# Convert from local timestamp to UTC, since that's what the DB and servers store time in
	created_at >= CONVERT_TZ('2015-05-26 10:30:00','America/Los_Angeles','UTC') AND 
    created_at <= CONVERT_TZ('2015-05-26 14:30:00','America/Los_Angeles','UTC')
;

# Count Dinner bento boxes by day 
select count(*) from CustomerBentoBox 
where 
	# Convert from local timestamp to UTC, since that's what the DB and servers store time in
	created_at >= CONVERT_TZ('2015-05-26 16:30:00','America/Los_Angeles','UTC') AND 
    created_at <= CONVERT_TZ('2015-05-26 23:59:59','America/Los_Angeles','UTC')
;

# Rollup order counts by day, UTC
select DATE_FORMAT(created_at, '%Y-%m-%d') as date2, count(*) as `count`
from `Order`
group by date2
order by date2 desc;

# ----------------------------------------------------------------------------------------------------------


# count redemptions of a coupon
select count(*) from CouponRedemption where fk_Coupon = 'thanksbento646';

# -----
# how hard is it to see if anyone who used the 'thanksbento646' code ordered again?
select o.fk_User, u.firstname, u.lastname
	,count(*) as num # remove to count orders
from `Order` o
left join User u on (u.pk_User = o.fk_User)
where fk_User in (select fk_User from CouponRedemption where fk_Coupon = 'thanksbento646')
group by fk_User # remove to count orders
order by num desc
;


# ------------------------------------------------------------------------------------------- 
# Customer Loyalty Reports
# ------------------------------------------------------------------------------------------- 

# -- Find the customers
select u.*,
	(select count(*) from `Order` o where o.fk_User = u.pk_User) as num_orders
from User u
where (select count(*) from `Order` o where o.fk_User = u.pk_User) > 1
;

# Get the percent
select (select count(*) from (
	select u.*,
		(select count(*) from `Order` o where o.fk_User = u.pk_User) as num_orders
	from User u
	where (select count(*) from `Order` o where o.fk_User = u.pk_User) > 1
) t) / (select count(*) from User) * 100 
as percent_users_repeat_order
;

# Get volume counts
select 
	(select count(*) from `Order` o where o.fk_User = u.pk_User) as num_orders,
    count(*) as count
from User u
where (select count(*) from `Order` o where o.fk_User = u.pk_User) > 1
group by num_orders
;


## -- Find the orders

# ------------------------------------------------------------------------------------------- 
# ORDER REPORTS
# ------------------------------------------------------------------------------------------- 

# 1. Order Details
call bento.Report_OrderDetails();

# 2. Order Summary
select
	o.pk_Order, o.fk_User as 'Customer Id',
    concat(u.firstname, ' ', u.lastname) as 'Customer Name',
    u.email,
	o.created_at as order_created_at,
	o.street, o.city, o.state, o.zip,
	os.`status`,
    o.tax,
    o.tip,
    o.amount as 'Total'
from `Order` o
left join OrderStatus os on (o.pk_Order = os.fk_Order)
left join User u on (o.fk_User = u.pk_User)
ORDER BY order_created_at ASC;



# ------------------------------------------------------------------------------------------- 
# Find a Driver
# ------------------------------------------------------------------------------------------- 

# 1. Find the User
SELECT * FROM bento.User
where email = 'rinkalahir@gmail.com'
;

# 2. Find the Order
select * from `Order` where fk_User = 2427;

# 3. Find the OrderStatus
SELECT * FROM bento.OrderStatus
where fk_Order = 3121
;

# 4. Find the Driver
select * from Driver where pk_Driver = 49;


# ------------------------------------------------------------------------------------------- 
# User Info
# ------------------------------------------------------------------------------------------- 

# Find users who have registered, but never ordered
select distinct email
from User u
left join `Order` o on u.pk_user = o.fk_User
where pk_Order IS NULL AND NOT is_test
limit 2000
;



