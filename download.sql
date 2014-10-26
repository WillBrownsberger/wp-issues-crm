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


======================
Import notes:

(1) Watertown File:  Was apparently up to date.  25908 records.
(2) Drop mailing fields.
(3) SSID is unique, never null.  Make it an index.  Length is always 12.
(4) 12 precincts -- mostly active, some blank, some inactive -- so can use a/i as registration of voter indicator.

On Boston: 

(1) Included wards/precincts are correct.
(2) 125130 records -- one dup ssid?
(2) Not registered count runs to 88% in 4-10, 83 in 21-2. Over half of Boston precincts have unregistered % over 40.  (Cf. Watertown 10-15%).  Lowest is 26%.
SELECT ward, precinct, sum(if ( '' = status,1,0)), count(ssid), sum(if ( '' = status,1,0))/count(ssid)  FROM boston_2sm_residents group by ward, precinct order by sum(if ( '' = status,1,0))/count(ssid)  desc

FILE FIELD NAMES:
last_name
first_name
middle_name
[TITLE]
dob
occupation
gender
/-- address fields
street_number
street_suffix
street_name
apartment
city
state
zip
/-- end address fields
party
voter_status
ward
precinct
reg_date
ssid
phone


delete from wp_posts where id > 189935
delete from wp_postmeta where post_id > 189935




====================TABLE DEF=========================
-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 26, 2014 at 06:33 AM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `wordpress_04_24`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_constituents`
--

CREATE TABLE IF NOT EXISTS `wp_wic_constituents` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ssid` varchar(13) CHARACTER SET latin1 NOT NULL,
  `civicrm_id` bigint(20) unsigned NOT NULL,
  `VAN_id` bigint(20) unsigned NOT NULL,
  `last_name` varchar(30) CHARACTER SET latin1 NOT NULL,
  `first_name` varchar(20) CHARACTER SET latin1 NOT NULL,
  `middle_name` varchar(20) CHARACTER SET latin1 NOT NULL,
  `title` varchar(3) CHARACTER SET latin1 NOT NULL,
  `dob` date NOT NULL,
  `is_deceased` tinyint(1) NOT NULL,
  `assigned` int(10) unsigned NOT NULL,
  `case_review_date` date NOT NULL,
  `case_status` varchar(1) CHARACTER SET latin1 NOT NULL,
  `street_number` int(7) unsigned NOT NULL,
  `street_suffix` varchar(3) CHARACTER SET latin1 NOT NULL,
  `street_name` varchar(25) CHARACTER SET latin1 NOT NULL,
  `apartment` varchar(4) CHARACTER SET latin1 NOT NULL,
  `zip` varchar(10) CHARACTER SET latin1 NOT NULL,
  `occupation` varchar(20) CHARACTER SET latin1 NOT NULL,
  `organization_name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `party` varchar(2) CHARACTER SET latin1 NOT NULL,
  `gender` varchar(1) CHARACTER SET latin1 NOT NULL,
  `ward` tinyint(4) NOT NULL,
  `precinct` tinyint(4) unsigned zerofill NOT NULL,
  `voter_status` varchar(1) CHARACTER SET latin1 NOT NULL,
  `reg_date` date NOT NULL,
  `last_updated_time` datetime NOT NULL,
  `last_updated_by` int(10) unsigned NOT NULL,
  `email_address_0` varchar(75) CHARACTER SET latin1 NOT NULL,
  `email_type_0` varchar(1) CHARACTER SET latin1 NOT NULL,
  `email_address_1` varchar(75) CHARACTER SET latin1 NOT NULL,
  `email_type_1` varchar(1) CHARACTER SET latin1 NOT NULL,
  `email_address_2` varchar(75) CHARACTER SET latin1 NOT NULL,
  `email_type_2` varchar(1) CHARACTER SET latin1 NOT NULL,
  `email_address_3` varchar(75) CHARACTER SET latin1 NOT NULL,
  `email_type_3` varchar(1) CHARACTER SET latin1 NOT NULL,
  `email_address_4` varchar(75) CHARACTER SET latin1 NOT NULL,
  `email_type_4` varchar(1) CHARACTER SET latin1 NOT NULL,
  `phone_0` varchar(15) CHARACTER SET latin1 NOT NULL,
  `phone_type_0` varchar(1) CHARACTER SET latin1 NOT NULL,
  `phone_ext_0` varchar(5) CHARACTER SET latin1 NOT NULL,
  `phone_1` varchar(15) CHARACTER SET latin1 NOT NULL,
  `phone_type_1` varchar(1) CHARACTER SET latin1 NOT NULL,
  `phone_ext_1` varchar(5) CHARACTER SET latin1 NOT NULL,
  `phone_2` varchar(15) CHARACTER SET latin1 NOT NULL,
  `phone_type_2` varchar(1) CHARACTER SET latin1 NOT NULL,
  `phone_ext_2` varchar(5) CHARACTER SET latin1 NOT NULL,
  `phone_3` varchar(15) CHARACTER SET latin1 NOT NULL,
  `phone_type_3` varchar(1) CHARACTER SET latin1 NOT NULL,
  `phone_ext_3` varchar(5) CHARACTER SET latin1 NOT NULL,
  `phone_4` varchar(15) CHARACTER SET latin1 NOT NULL,
  `phone_type_4` varchar(1) CHARACTER SET latin1 NOT NULL,
  `phone_ext_4` varchar(5) CHARACTER SET latin1 NOT NULL,
  `address_type_0` varchar(1) CHARACTER SET latin1 NOT NULL,
  `street_address_0` varchar(50) CHARACTER SET latin1 NOT NULL,
  `city_state_zip_0` varchar(50) CHARACTER SET latin1 NOT NULL,
  `address_type_1` varchar(1) CHARACTER SET latin1 NOT NULL,
  `street_address_1` varchar(50) CHARACTER SET latin1 NOT NULL,
  `city_state_zip_1` varchar(50) CHARACTER SET latin1 NOT NULL,
  `address_type_2` varchar(1) CHARACTER SET latin1 NOT NULL,
  `street_address_2` varchar(50) CHARACTER SET latin1 NOT NULL,
  `city_state_zip_2` varchar(50) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ssid_2` (`ssid`),
  KEY `ssid` (`ssid`),
  KEY `last_name` (`last_name`),
  KEY `middle_name` (`middle_name`),
  KEY `title` (`title`),
  KEY `dob` (`dob`),
  KEY `street_number` (`street_number`),
  KEY `street_suffix` (`street_suffix`),
  KEY `street_name` (`street_name`),
  KEY `apartment` (`apartment`),
  KEY `zip` (`zip`),
  KEY `occupation` (`occupation`),
  KEY `party` (`party`),
  KEY `gender` (`gender`),
  KEY `ward` (`ward`),
  KEY `precinct` (`precinct`),
  KEY `voter_status` (`voter_status`),
  KEY `last_name_2` (`last_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20414 ;


insert into wp_wic_constituents 
( 
ssid,
first_name, 
last_name, 
middle_name,
title,
post_title,
dob,
street_address_0, 
city_state_zip_0,
occupation,
party,
gender,
voter_status,
ward,
precinct,
last_updated_time,
last_updated_by,
reg_date
)

select 
ssid,
first_name, 
last_name, 
middle_name,
title,
concat(last_name, ', ', first_name ) as post_title,
dob,
concat( street_number, street_suffix, ' ', street_name, if( apartment > '', concat( ', APT. ', apartment), '') ) as street_address_0, 
concat( 'BELMONT', ', ', 'MA', ' 0', zip ) as city_state_zip_0,
occupation,
if( party > '', if( instr('zzdruljgs', lower(trim(party))) > 1, lower(party), 'o'), '' ) as party,
lower(gender) as gender,
if( voter_status > '', lower(voter_status), 'x' ) as voter_status,
ward,
precinct,
now() as last_updated_time,
15 as last_updated_by,
reg_date
from belmont_residents

BOSTON----------
insert into wp_wic_constituents 
( 
ssid,
first_name, 
last_name, 
middle_name,
title,
post_title,
dob,
street_address_0, 
city_state_zip_0,
phone_0,
phone_type_0,
occupation,
party,
gender,
voter_status,
ward,
precinct,
last_updated_time,
last_updated_by,
reg_date
)

select 
ssid,
first_name, 
last_name, 
middle_name,
title,
concat(last_name, ', ', first_name ) as post_title,
dob,
concat( street_number, street_suffix, ' ', street_name, if( apartment > '', concat( ', APT. ', apartment), '') ) as street_address_0, 
concat( city, ', ', state, ' 0', zip ) as city_state_zip_0,
phone,
'0',
occupation,
if( party > '', if( instr('zzdruljgs', lower(trim(party))) > 1, lower(party), 'o'), '' ) as party,
lower(gender) as gender,
if( voter_status > '', lower(voter_status), 'x' ) as voter_status,
ward,
precinct,
now() as last_updated_time,
15 as last_updated_by,
reg_date
from boston_2sm_residents