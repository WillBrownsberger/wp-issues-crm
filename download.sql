count(id) 75757 (all but 24 are MA)  
/* contacts */
SELECT c.id, external_identifier, first_name, middle_name, last_name, job_title, organization_name, gender_id, birth_date, is_deceased, deceased_date, is_deleted, created_date, modified_date,
street_address, city, state_province_id, postal_code, email, phone
FROM civicrm_contact c left join civicrm_address a on a.contact_id = c.id left join civicrm_email e on e.contact_id = c.id left join civicrm_phone p on p.contact_id = c.id
WHERE contact_type = "individual" and 
	( a.is_primary = 1 or a.is_primary is null ) and 
	( e.is_primary = 1 or e.is_primary is null ) and 
	(p.is_primary = 1 or p.is_primary is null)
	

Drop 6 "is deleted"
/* activities */
SELECT a.id, activity_type_id, subject, activity_date_time, details, status_id, is_deleted, contact_id, activity_issue_1, issue_class_2, budget_account_3
from civicrm_activity a inner join civicrm_activity_contact ac on ac.activity_id = a.id 
left join civicrm_value_activity_tracking_1 v on v.entity_id = a.id where record_type_id = 3 

/* 
sudo /etc/init.d/mysql restart
sudo /etc/init.d/apache2 
delete FROM `wp_postmeta` WHERE left(meta_key,5) = '_wic_'
delete  FROM `wp_posts` WHERE post_type = 'wic_constituent'
	
	SELECT meta_key, count(meta_id) FROM `wp_postmeta` where left(meta_key, 5) = '_wic_' group by meta_key
	
update wp_postmeta set meta_value = 'MA' where meta_key = '_wic_state' and meta_value = '1020'	
	
	SELECT count(id) FROM wp_posts WHERE post_type = 'wic_constituent' 
http://www.php-mysql-tutorial.com/wikis/mysql-tutorials/uploading-files-to-mysql-database.aspx


SELECT * FROm wp_posts p inner join wp_postmeta m on m.post_id = p.ID where meta_key = 'wic_data_first_name' and meta_value = 'william' and post_status = 'private'
SELECT * FROm wp_posts p inner join wp_postmeta m on m.post_id = p.ID where meta_key = 'wic_data_city' and meta_value like '%belmont%' and post_status = 'private'
wic_data_mobile_phone 	75771
wic_data_civicrm_id 	75751
wic_data_is_deceased 	75747
wic_data_first_name 	74625
wic_data_last_name 	74609
wic_data_ss_id 	74330
wic_data_city 	71303
wic_data_state 	71101
wic_data_zip 	71085
wic_data_street_address 	70938
wic_data_birth_date 	69507
wic_data_gender_id 	68866
wic_data_middle_name 	54406
wic_data_phone 	43287
wic_data_email 	9974


SELECT * FROm wp_posts p inner join wp_postmeta m1 on m1.post_id = p.ID inner join wp_postmeta m2 on m2.post_ID = p.ID where m1.meta_key = 'wic_data_gender_id' and m1. meta_value = 'm' and post_status = 'private' and m2.meta_key = 'wic_data_city' and m2.meta_value = 'belmont'

SELECT *, serialize () FROm wp_posts p inner join wp_postmeta m1 on m1.post_id = p.ID inner join wp_postmeta m2 on m2.post_ID = p.ID where m1.meta_key = 'wic_data_phone'and post_status = 'private' and m2.meta_key = 'wic_data_mobile_phone'


SELECT city, state_province_id, postal_code,count(id) FROM `civicrm_address` where city > '' and postal_code > '' group by city, state_province_id, postal_code order by count(id) desc 

71358 addresses on table
220 with city but no postal code
3 where have zip, but not city -- clearly city is preferred and must allow entry of city only.
71062 have both city and zip code.  Top 40 account for 70783.

 