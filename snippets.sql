
EXTRACT FROM NEW CIVI
create table civi_summary
select c.id as id, external_identifier, first_name, middle_name, last_name, 
gender_id, birth_date, deceased, e.location_type_id as email_Location, email, a.location_type_id as address_location, street_address, city, postal_code, ca.contact_id as activities_present  
from civicrm_contact c left join civicrm_email e on e.contact_id = c.id left join civicrm_address a 
on a.contact_id = c.id left join contacts_with_activities as ca 
on ca.contact_id = c.id where contact_type = "individual" and is_deleted = 0 group by c.id

ADD SSID FROM OLD CIV TO NEW CIVI 
update civi_summary cs inner join ssid_lookup l on l.original_civi_id = cs.old_civi_id set cs.ssid = l.ssid where old_civi_id > 0   
 
MATCH FROM NEW CIVI TO WIC -- UPDATE SUMMARY ROW TO INCLUDE THE MATCH PARAMETER
1) BY SSID 
update civi_summary old inner join wp_wic_constituent new on old.ssid = new.ssid set wp_wic_ID = new.ID where old.ssid > '' 
2) THROW AWAY IF NO EMAILS AND NO ACTIVITIES AND NOT  
-- checking -- 10055 have email > ''; 6454 also have activities present; 10313 have activities present.  13914 have both present.  3859 have activity, no email.  3601 the reverse.  
Total on DB = 75896.  So, discarding those without activity or email,should = 62072 gone, 13914 remaining.  Actual result is 61982 found, but do delete and see what is left -- clase enough.
13914 remain.  Of remaining, 7941 have matched ssid. 5973 unmatched. But only 5684 are matched through to new DB, 8230 still unmatched.
3) of remaining unmatched 8230, 3200 are in relevant cities.  
select * from civi_summary where ( city = "allston" or city = "belmont" or city = "boston" or city = "brighton" or city = "watertown" ) and wp_wic_id = 0

note: Of these 674 have SSID and are probably moved people.  We will keep them and not try to match them and mark them with a different status -- off roll.
2526 are names without ssid, but in relevant cities.

All others will simply be added.  

3 DO MATCHES PROGRESSIVELY LOOSER
For efficiency, create a wic_summary table
DO FN/LN/ADDR5
update wic_summary w inner join civi_summary c on w.first_name = c.first_name and w.last_name = c.last_name and w.addr5 = c.addr5 set wp_wic_ID = w.id where wp_wic_ID = 0
Match 765
DO FN/LN/CITY.
update  wic_summary w inner join civi_summary c on w.first_name = c.first_name and w.last_name = c.last_name and w.city = c.city set wp_wic_ID = w.id where wp_wic_ID = 0  
Match 192
DO FN/LN WHERE NO CITY
update wic_summary w inner join civi_summary c on w.first_name = c.first_name and w.last_name = c.last_name set wp_wic_id = w.ID where wp_wic_id = 0 and c.ssid = '' and c.city = '' 
Match 190
1662 remain unmatched with SSID = '' in relevant cities.
CONSIDER THESE ADDS WITH UNKNOW VOTER STATUS.

SUMMARIZING:  FROM CIVI SUMMARY WILL DO THE FOLLOWING:
1) UPDATE THOSE MATCHED IN ANY PASS WITH EMAIL (AND LATER ACTIVITIES AND PHONES) 6831 ADD CIVI ID AS GO!
2) ADD THOSE NOT MATCHED IN ANY PASS WITH ADDRESS OR EMAIL AVAILABLE ADD CIVI ID AS GO!
3) TREAT SPECIALLY out those unmatched in relevant cities but with SSID.
		4987 with email



-----------STEP ONE: add civi ID for those matched already: 6831
update wp_wic_constituent w inner join civi_summary c on c.wp_wic_ID = w.ID set w.civi_id = c.id
171465 constituent records of which 6779 have civi_id > 0 -- some consolidation (not going to resolve why)
step two: add new wic_constituent records for the unmatched.
insert into wp_wic_constituent ( civi_id, ssid, first_name, first_name_soundex, middle_name, middle_name_soundex, last_name, last_name_soundex, gender, is_deceased, last_updated_by, last_updated_time )
SELECT id, ssid, first_name, soundex(first_name), middle_name, soundex(middle_name), last_name, soundex(last_name), if ( gender_id = '1', 'f', if ( gender_id = '2', 'm','')) as gender ,  is_deceased, 15, now() from civi_summary where wp_wic_ID = 0 and ssid = ''
Now total linked is 11694 = 4915+6779
Add 587 with special voters status -- probably gone:
insert into wp_wic_constituent ( civi_id, ssid, first_name, first_name_soundex, middle_name, middle_name_soundex, last_name, last_name_soundex, gender, is_deceased, last_updated_by, last_updated_time, voter_status )
SELECT id, ssid, first_name, soundex(first_name), middle_name, soundex(middle_name), last_name, soundex(last_name), if ( gender_id = '1', 'f', if ( gender_id = '2', 'm','')) as gender ,  is_deceased, 15, now(), 'z' from civi_summary where wp_wic_ID = 0 and ssid > '' and ( city = "allston" or city = "belmont" or city = "boston" or city = "brighton" or city = "watertown" )
Yields 12281

Do the final out of district group:
insert into wp_wic_constituent ( civi_id, ssid, first_name, first_name_soundex, middle_name, middle_name_soundex, last_name, last_name_soundex, gender, is_deceased, last_updated_by, last_updated_time )
SELECT id, ssid, first_name, soundex(first_name), middle_name, soundex(middle_name), last_name, soundex(last_name), if ( gender_id = '1', 'f', if ( gender_id = '2', 'm','')) as gender ,  is_deceased, 15, now() from civi_summary where wp_wic_ID = 0 and ssid > '' and not ( city = "allston" or city = "belmont" or city = "boston" or city = "brighton" or city = "watertown" )
+1581 + 12281 = 13862  Remaining 52 appear to be some flavor of dup -- all are previously matched
( select c.* from civi_summary c left join  wp_wic_constituent w  on c.id = w.civi_id where  w.civi_id is null )
SELECT * FROM wp_wic_constituent w inner join civi_summary c on c.id = w.civi_id -- 13862 records
Name checking:  select c.first_name, c.last_name, w.first_name, w.last_name, c.* from civi_summary c inner join  wp_wic_constituent w  on c.id = w.civi_id where c.last_name != w.last_name or c.first_name != w.first_name
58 breaks -- apparently legit -- marriages or slight spelling differences
so -- civi_id is good; ssid looks good; names are good.  Checked gender, is_deceased, update fields and the 587 voter status is z.

Call CONSTITUENT ADDS done.

step two: do other updates linking on that basis.
First: emails

insert into wp_wic_email ( constituent_id, email_type, email_address) 
select w.id, if ( c.email_location = '1', '0', if (c.email_location = '2', '1', '2')), c.email from wp_wic_constituent w inner join civi_summary c on w.civi_id = c.id where email_address > ''
The email table now has 10044 rows (of which 32 previously added) -- 10012
fix last updated time and lastupdated by
NOTE: HAD NO EMAILS (ALMOST NO EMAILS) PREVIOUSLY, SO NO CONCERN FOR ADDING DUP EMAILS
FOR ADDRESSES, IF HAVE SSID ON WIC, ALREADY HAVE ADDRESS WIC AND INCORRECT TO ADD ANOTHER (Except in cases where had SSID on civi and added the record rather than matching it -- voter status = z)
	all addresses at this stage come from voter file, so condition is correct.
Now do addresss
insert into wp_wic_address (
	 constituent_id, 
	 address_type, 
	 address_line, 
	 street_name,
	 city,
	 state,
	 zip,
	 last_updated_time,
	 last_updated_by ) 
select 
w.id, 
if ( c.address_location = '1', '0', if (c.address_location = '2', '1', '2')), 
street_address,
trim( substring(street_address, locate(' ', street_address ))),
city, 
'MA',
postal_code,
now(),
15
from wp_wic_constituent w inner join civi_summary c on w.civi_id = c.id where c.city > '' and ( w.ssid = '' or w.voter_status = 'z')
TO ADD 2705+587 =3292) ADDRESSES -- WOULD HAVE BEEN 11391 without that limitation. Get 3549 if enforce as c.ssid = '', but this includes some who matched legit by fn/ln . . .
Accept this and proceed.


PHONES IN TWO STEPS
1) Grab phones for the records that are matched through by any means 

create table civi_phones
select c.id as id, location_type_id as location, phone_type_id as type, phone_numeric as phone 
from willbr5_civicrm.civicrm_phone p inner join civi_summary c on c.id = p.contact_id  ORDER BY location_type_id DESC   

insert into wp_wic_phone ( constituent_id, phone_type, phone_number, last_updated_time, last_updated_by )
SELECT w.id, if( type = '2', '1', if ( location = '1', '0', if ( location = '2', '2', '4'))), phone, now(), 15 FROM civi_phones p inner join wp_wic_constituent w on w.civi_id = p.id
7614 phones -- this is just the email activity contacts 

2) Go back and get the straight SSID matches

create table civi_more_phones
select external_identifier, location_type_id as location, phone_type_id as type, phone_numeric as phone 
from willbr5_civicrm.civicrm_phone p inner join willbr5_civicrm.civicrm_contact c on c.id = p.contact_id  ORDER BY location_type_id DESC   

get ssid on it
update civi_more_phones mp inner join ssid_lookup l on l.original_civi_id = mp.external_identifier set mp.ssid = l.ssid where mp.external_identifier > 0 

insert into wp_wic_phone ( constituent_id, phone_type, phone_number, last_updated_time, last_updated_by )
SELECT w.id, if( type = '2', '1', if ( location = '1', '0', if ( location = '2', '2', '4'))), phone, now(), 15 FROM civi_more_phones mp inner join wp_wic_constituent w on w.ssid = mp.ssid where mp.ssid > '' and civi_id = ''
New total = 38170.


NOW ACTIVITIES (20378))
======================!

SELECT id, entity_id, activity_issue_1, issue_class_2, budget_account_3 FROM civicrm_value_activity_tracking_1 WHERE 1

SELECT * FROM civicrm_option_value WHERE OPTION_GROUP_ID = '86'  (activity_issue_1)
SELECT * FROM civicrm_option_value WHERE option_group_id = '87' (issue_class_2)
SELECT * FROM civicrm_option_value WHERE option_group_id = '88' (budget_account_3)
SELECT * FROM civicrm_option_value WHERE option_group_id = 2 (activity_type)
NOTE -- USE LABEL FIELD -- NAME, NOT CONSISTENTLY COMPLETED
SELECT
ac.contact_id,
a.id, 
activity_type_id, ov4.label as activity_type_label,
subject, 
activity_date_time, 
details, 
activity_issue_1, ov1.label as issue_label,
issue_class_2, ov2.label as issue_class_label,
budget_account_3, ov3.label as budget_account_label
FROM civicrm_activity a inner join civicrm_activity_contact ac on ac.activity_id = a.id inner join civicrm_value_activity_tracking_1 vat on vat.entity_id = a.id 
left join civicrm_option_value ov1 on ov1.value = activity_issue_1 and ov1.option_group_id = '86'
left join civicrm_option_value ov2 on ov2.value = issue_class_2 and ov2.option_group_id = '87'
left join civicrm_option_value ov3 on ov3.value = budget_account_3 and ov3.option_group_id = '88'
left join civicrm_option_value ov4 on ov4.value = activity_type_id and ov4.option_group_id = '2'
where record_type_id = 3

OBSERVATION:  ESSENTIALLY ALL HAVE STATUS ID AND PRIORITY ID = 2 -- 20329 2/2 48 1/2 4 2/1 IGNORE THese fields
Thescheduled (status 1 activities are all from April 15 onlegislation )
USE RECORD_TYPE_ID = 3 (is the record for the creator) 20376 type 3 records -- close to right.  join yields 20376 records


insert into wp_wic_activity
( constituent_id, 
activity_date, 
activity_type, 
issue, 
activity_note, 
last_updated_time, 
last_updated_by, 
civi_activity_id )
select 
wc.id, 
activity_date_time,
if ( activity_type_id = 12 or activity_type_id = 3 or activity_type_id = 48, '0', if (activity_type_id = 2, '1', 
	if ( activity_type_id = 51, '2', if (activity_type_id = 1, '3', if (activity_type_id = 49, '4', if (activity_type_id = 50, '5', '6')))))) as type,
212670,
concat ( 'Subject: ' , subject, '. ' , if ( details is not null, details, '') ),
now(),
15,
a.id
from willbr5_civicrm.activity_summary a inner join willbr5_wordpress.wp_wic_constituent wc on a.contact_id = wc.civi_id  

61 activites not converted
SELECT contact_id FROM `activity_summary` a left join willbr5_wordpress.wp_wic_activity w on a.id = w.civi_activity_id where w.civi_activity_id is null
 
 select contact_id, wc.* from 
( SELECT contact_id FROM willbr5_civicrm.`activity_summary` a left join willbr5_wordpress.wp_wic_activity w on a.id = w.civi_activity_id where w.civi_activity_id is null ) as missing
left join willbr5_wordpress.wp_wic_constituent wc on wc.civi_id = contact_id
Problem appears to be dups.  So, bring over the 61 and do surgery on them insitu


insert into wp_wic_activity
( constituent_id, 
activity_date, 
activity_type, 
issue, 
activity_note, 
last_updated_time, 
last_updated_by, 
civi_activity_id )
select 
999999999,
activity_date_time,
if ( activity_type_id = 12 or activity_type_id = 3 or activity_type_id = 48, '0', if (activity_type_id = 2, '1', 
	if ( activity_type_id = 51, '2', if (activity_type_id = 1, '3', if (activity_type_id = 49, '4', if (activity_type_id = 50, '5', '6')))))) as type,
212670,
concat ( 'Subject: ' , subject, '. ' , if ( details is not null, details, '') ),
now(),
15,
a.id
from willbr5_civicrm.activity_summary a where a.new_wic_constituent_id = 0 


then create table of the messed up ideas and link them in as follows (possibly not perfect, but good )
update wp_wic_activity a inner join ( SELECT c.id as contact_id, activity_id FROM `messed_up_activities` m inner join 
wp_wic_constituent c on m.last_name = c.last_name and m.first_name = c.first_name group by activity_id ) as fubar 
on a.civi_activity_id = fubar.activity_id set a.constituent_id = fubar.contact_id 
All set -- 20410 records in wp_wic_activity -- 34 previously, so 20376 is the new count --- call it done.



INSERT INTO wp-wic-address
( constituent_id, 
address_type,
address_line, 
street_name, 
city, 
state, 
zip, 
last_updated_time, 
last_updated_by 
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
