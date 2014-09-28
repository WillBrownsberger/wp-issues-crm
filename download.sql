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
	delete FROM `wp_postmeta` WHERE left(meta_key,5) = '_wic_'
	delete  FROM `wp_posts` WHERE post_type = 'wic_constituent'
	
	SELECT meta_key, count(meta_id) FROM `wp_postmeta` where left(meta_key, 5) = '_wic_' group by meta_key
