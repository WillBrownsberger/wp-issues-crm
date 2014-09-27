count(id) 75757 (all but 24 are MA)

SELECT c.id, external_identifier, first_name, middle_name, last_name, job_title, organization_name, gender_id, birth_date, is_deceased, deceased_date, is_deleted, created_date, modified_date,
street_address, city, state_province_id, postal_code, email, phone
FROM civicrm_contact c left join civicrm_address a on a.contact_id = c.id left join civicrm_email e on e.contact_id = c.id left join civicrm_phone p on p.contact_id = c.id
WHERE contact_type = "individual" and 
	( a.is_primary = 1 or a.is_primary is null ) and 
	( e.is_primary = 1 or e.is_primary is null ) and 
	(p.is_primary = 1 or p.is_primary is null)
	
-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 25, 2014 at 09:02 PM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wordpress_04_24`
--

-- --------------------------------------------------------

--
-- Table structure for table `TABLE 19`
--

DROP TABLE IF EXISTS `TABLE 19`;
CREATE TABLE IF NOT EXISTS `TABLE 19` (
  `id` int(5) NOT NULL DEFAULT '0',
  `external_identifier` varchar(6) DEFAULT NULL,
  `first_name` varchar(17) DEFAULT NULL,
  `middle_name` varchar(15) DEFAULT NULL,
  `last_name` varchar(46) DEFAULT NULL,
  `job_title` varchar(10) DEFAULT NULL,
  `organization_name` varchar(10) DEFAULT NULL,
  `gender_id` varchar(1) DEFAULT NULL,
  `birth_date` varchar(10) DEFAULT NULL,
  `is_deceased` int(1) DEFAULT NULL,
  `deceased_date` varchar(10) DEFAULT NULL,
  `is_deleted` int(1) DEFAULT NULL,
  `created_date` varchar(19) DEFAULT NULL,
  `modified_date` varchar(19) DEFAULT NULL,
  `street_address` varchar(35) DEFAULT NULL,
  `city` varchar(18) DEFAULT NULL,
  `state_province_id` varchar(4) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `email` varchar(58) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile_phone` int(14) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



SELECT a.id, activity_type_id, subject, activity_date_time, details, status_id, is_deleted, contact_id, activity_issue_1, issue_class_2, budget_account_3
from civicrm_activity a inner join civicrm_activity_contact ac on ac.activity_id = a.id 
left join civicrm_value_activity_tracking_1 v on v.entity_id = a.id where record_type_id = 3 

select menu_order, count(id) from wp_posts where post_type = "post" group by menu_order 

SELECT post_type, count( id )
FROM wp_posts
WHERE menu_order !=0
GROUP BY post_type

post_type 	count(id) 	
forum 	13
nav_menu_item 	66
page 	15
post 	11
reply 	1324
	
