
#source: http://www.percona.com/blog/2014/07/02/using-mysql-triggers-and-views-in-amazon-rds/

#mysqldump -h hostname -u username -ppassword –single-transaction –quick database_name | sed -e ‘s/\/\*[^*]*DEFINER=[^*]*\*\///’ | mysql -h hostname -u username -ppassword database_name

mysqldump -u root bento | sed -e 's/\/\*[^*]*DEFINER=[^*]*\*\///' > ../full_dumps/justnow.sql
