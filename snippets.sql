
INSERT INTO wp-wic-address
( `constituent_id`, 
`address_type`,
`address_line`, 
`street_name`, 
`city`, 
`state`, 
`zip`, 
`last_updated_time`, 
`last_updated_by` 
select 
c.ID, 
0, 
concat ( street_number, street_suffix, ' ' , street_name, if (apartment > '', concat( ' APT ', apartment, ' '), ' ' ) ), street_name, city, state, zip, now(), 15)
from wp_wic_constituents_old o inner join wp_wic_constituent c on o.ssid = c.ssid







































/* copy old to new */
insert into wp_wic_constituent
( ssid, 
civi_id,
van_id, 
last_name, 
last_name_soundex, 
first_name, 
first_name_soundex, 
middle_name, 
middle_name_soundex, 
date_of_birth, 
is_deceased, 
case_assigned, 
case_review_date, 
case_status, 
occupation, 
organization, 
party, 
gender, 
ward, 
precinct, 
voter_status, 
reg_date, 
last_updated_time,
last_updated_by)
SELECT 
ssid, 
civicrm_id,
VAN_id, 
last_name, 
soundex(last_name),
first_name, 
soundex(first_name), 
middle_name, 
soundex(middle_name), 
dob, 
is_deceased, 
assigned, 
case_review_date, 
case_status, 
occupation, 
organization_name, 
party, 
gender, 
ward, 
precinct, 
voter_status, 
reg_date, 
now(),
15
from wp_wic_constituents_old
