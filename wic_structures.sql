-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 30, 2014 at 08:00 AM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.5

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
-- Table structure for table `wp_wic_activity`
--

CREATE TABLE IF NOT EXISTS `wp_wic_activity` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Email ID',
  `constituent_id` int(10) unsigned NOT NULL,
  `activity_date` date NOT NULL,
  `activity_type` smallint(6) DEFAULT NULL,
  `issue` int(11) NOT NULL COMMENT 'post_id for associated issue',
  `pro_con` tinyint(1) NOT NULL,
  `activity_note` text NOT NULL,
  `last_updated_time` date NOT NULL,
  `last_updated_by` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `constituent_id` (`constituent_id`),
  KEY `email_address` (`activity_type`),
  KEY `email_type` (`activity_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=89 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_address`
--

CREATE TABLE IF NOT EXISTS `wp_wic_address` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `constituent_id` bigint(20) unsigned NOT NULL,
  `address_type` smallint(11) NOT NULL,
  `address_line` varchar(50) NOT NULL,
  `street_name` varchar(25) NOT NULL,
  `city` varchar(20) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `last_updated_time` datetime NOT NULL,
  `last_updated_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `street_name` (`street_name`),
  KEY `zip` (`zip`),
  KEY `address_line` (`address_line`),
  KEY `constituent_id` (`constituent_id`),
  KEY `city` (`city`),
  KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=171500 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_constituent`
--

CREATE TABLE IF NOT EXISTS `wp_wic_constituent` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ssid` varchar(13) NOT NULL,
  `civi_id` bigint(20) unsigned NOT NULL,
  `van_id` bigint(20) unsigned NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `last_name_soundex` varchar(15) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `first_name_soundex` varchar(10) NOT NULL,
  `middle_name` varchar(20) NOT NULL,
  `middle_name_soundex` varchar(10) NOT NULL,
  `date_of_birth` date NOT NULL,
  `is_deceased` tinyint(1) NOT NULL,
  `mark_deleted` varchar(7) NOT NULL,
  `case_assigned` int(10) unsigned NOT NULL,
  `case_review_date` date NOT NULL,
  `case_status` varchar(1) NOT NULL,
  `occupation` varchar(20) NOT NULL,
  `organization` varchar(50) NOT NULL,
  `party` varchar(2) NOT NULL,
  `gender` varchar(1) NOT NULL,
  `ward` tinyint(4) NOT NULL,
  `precinct` tinyint(4) NOT NULL,
  `voter_status` varchar(1) NOT NULL,
  `reg_date` date NOT NULL,
  `last_updated_time` datetime NOT NULL,
  `last_updated_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ssid` (`ssid`),
  KEY `last_name` (`last_name`),
  KEY `middle_name` (`middle_name`),
  KEY `dob` (`date_of_birth`),
  KEY `occupation` (`occupation`),
  KEY `party` (`party`),
  KEY `gender` (`gender`),
  KEY `ward` (`ward`),
  KEY `precinct` (`precinct`),
  KEY `voter_status` (`voter_status`),
  KEY `first_name` (`first_name`),
  KEY `civicrm_id` (`civi_id`),
  KEY `VAN_id` (`van_id`),
  KEY `is_deceased` (`is_deceased`),
  KEY `is_deleted` (`mark_deleted`),
  KEY `assigned` (`case_assigned`),
  KEY `case_review_date` (`case_review_date`),
  KEY `case_status` (`case_status`),
  KEY `fnln` (`last_name`,`first_name`),
  KEY `first_name_soundex` (`first_name_soundex`),
  KEY `last_name_soundex` (`last_name_soundex`),
  KEY `middle_name_soundex` (`middle_name_soundex`),
  KEY `soundex` (`mark_deleted`,`last_name_soundex`,`first_name_soundex`),
  KEY `organization` (`organization`),
  KEY `last_updated_time` (`last_updated_time`),
  KEY `last_updated_by` (`last_updated_by`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=171513 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_data_dictionary`
--

CREATE TABLE IF NOT EXISTS `wp_wic_data_dictionary` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_slug` varchar(20) NOT NULL,
  `group_slug` varchar(30) NOT NULL,
  `field_slug` varchar(30) NOT NULL,
  `field_type` varchar(30) NOT NULL COMMENT 'name of entity supplying multiple rows for this field',
  `is_date` tinyint(1) NOT NULL,
  `is_int` tinyint(1) NOT NULL,
  `field_label` varchar(30) NOT NULL,
  `field_order` mediumint(9) NOT NULL,
  `listing_order` int(11) NOT NULL,
  `sort_clause_order` mediumint(11) NOT NULL,
  `required` varchar(10) NOT NULL,
  `dedup` tinyint(1) NOT NULL,
  `readonly` tinyint(1) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `field_default` varchar(30) NOT NULL,
  `like_search_enabled` tinyint(1) NOT NULL,
  `secondary_alpha_search` varchar(30) NOT NULL,
  `transient` tinyint(1) NOT NULL,
  `wp_query_parameter` varchar(30) NOT NULL,
  `input_class` varchar(30) NOT NULL DEFAULT 'wic-input',
  `label_class` varchar(30) NOT NULL DEFAULT 'wic-label',
  `placeholder` varchar(50) NOT NULL,
  `blank_prohibited` tinyint(1) NOT NULL DEFAULT '0',
  `suppress_on_search` tinyint(1) NOT NULL,
  `onchange` varchar(40) NOT NULL,
  `zero_is_null` tinyint(1) NOT NULL COMMENT 'do not include 0 values in search clause array',
  PRIMARY KEY (`field_id`),
  KEY `entity_slug` (`entity_slug`),
  KEY `field_group` (`group_slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=94 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_email`
--

CREATE TABLE IF NOT EXISTS `wp_wic_email` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Email ID',
  `constituent_id` int(10) unsigned NOT NULL,
  `email_type` smallint(10) unsigned DEFAULT NULL,
  `email_address` varchar(254) DEFAULT NULL COMMENT 'Email address',
  `last_updated_time` date NOT NULL,
  `last_updated_by` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `constituent_id` (`constituent_id`),
  KEY `email_address` (`email_address`),
  KEY `email_type` (`email_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10602 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_form_field_groups`
--

CREATE TABLE IF NOT EXISTS `wp_wic_form_field_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_slug` varchar(30) NOT NULL,
  `group_slug` varchar(30) NOT NULL,
  `group_label` varchar(40) NOT NULL,
  `group_legend` text NOT NULL,
  `group_order` smallint(6) NOT NULL DEFAULT '0',
  `initial_open` tinyint(1) NOT NULL,
  `sidebar_location` tinyint(1) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_phone`
--

CREATE TABLE IF NOT EXISTS `wp_wic_phone` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Email ID',
  `constituent_id` int(10) unsigned NOT NULL,
  `phone_type` smallint(10) unsigned DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `extension` varchar(10) NOT NULL,
  `last_updated_time` date NOT NULL,
  `last_updated_by` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `constituent_id` (`constituent_id`),
  KEY `email_address` (`phone_number`),
  KEY `email_type` (`phone_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_search_log`
--

CREATE TABLE IF NOT EXISTS `wp_wic_search_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `time` varchar(20) NOT NULL,
  `entity` varchar(30) NOT NULL,
  `serialized_search_array` text NOT NULL,
  `download_time` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user-time` (`user_id`,`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1734 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
