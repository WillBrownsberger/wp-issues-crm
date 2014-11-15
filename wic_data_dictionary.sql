-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 15, 2014 at 04:34 PM
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
-- Table structure for table `wp_wic_data_dictionary`
--

CREATE TABLE IF NOT EXISTS `wp_wic_data_dictionary` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_slug` varchar(20) NOT NULL,
  `group_slug` varchar(30) NOT NULL,
  `field_slug` varchar(20) NOT NULL,
  `field_type` varchar(30) NOT NULL COMMENT 'name of entity supplying multiple rows for this field',
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
  `transient` tinyint(1) NOT NULL,
  `wp_query_parameter` varchar(30) NOT NULL,
  `sanitize_call_back` varchar(30) NOT NULL,
  `validate_call_back` varchar(30) NOT NULL,
  `format_call_back` varchar(30) NOT NULL,
  `enum_values` varchar(255) NOT NULL,
  `field_label_suffix` varchar(5) NOT NULL,
  `input_class` varchar(30) NOT NULL DEFAULT 'wic-input',
  `label_class` varchar(30) NOT NULL DEFAULT 'wic-label',
  `placeholder` varchar(50) NOT NULL,
  PRIMARY KEY (`field_id`),
  KEY `entity_slug` (`entity_slug`),
  KEY `field_group` (`group_slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=58 ;

--
-- Dumping data for table `wp_wic_data_dictionary`
--

INSERT INTO `wp_wic_data_dictionary` (`field_id`, `entity_slug`, `group_slug`, `field_slug`, `field_type`, `field_label`, `field_order`, `listing_order`, `sort_clause_order`, `required`, `dedup`, `readonly`, `hidden`, `field_default`, `like_search_enabled`, `transient`, `wp_query_parameter`, `sanitize_call_back`, `validate_call_back`, `format_call_back`, `enum_values`, `field_label_suffix`, `input_class`, `label_class`, `placeholder`) VALUES
(1, 'activity', 'activity', 'screen_deleted', 'deleted', 'x', 999, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(2, 'constituent', 'contact', 'ID', 'text', 'Internal Id', 0, 0, 0, '', 0, 1, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(3, 'constituent', 'contact', 'first_name', 'text', 'First Name', 10, 10, 30, 'group', 1, 0, 0, '', 1, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(4, 'constituent', 'contact', 'middle_name', 'text', 'Middle Name', 20, 20, 40, '', 1, 0, 0, '', 1, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(5, 'constituent', 'contact', 'last_name', 'text', 'Last Name', 30, 30, 20, 'group', 1, 0, 0, '', 1, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(6, 'constituent', 'contact', 'phone', 'multivalue', 'Phones', 40, 40, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(7, 'constituent', 'contact', 'email', 'multivalue', 'Emails', 50, 50, 0, '', 1, 0, 0, '', 1, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(8, 'constituent', 'contact', 'address', 'multivalue', 'Addresses', 60, 60, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(9, 'constituent', 'contact', 'notes', 'textarea', 'Notes', 70, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(10, 'constituent', 'activity', 'activity', 'multivalue', 'Activities', 80, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(11, 'constituent', 'case', 'case_assigned', 'text', 'Staff', 110, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(12, 'constituent', 'case', 'case_status', 'select', 'Status', 120, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(13, 'constituent', 'case', 'case_review_date', 'range', 'Review Date', 130, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(14, 'constituent', 'personal', 'date_of_birth', 'range', 'Date of Birth', 210, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(15, 'constituent', 'personal', 'gender', 'select', 'Gender', 220, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(16, 'constituent', 'personal', 'occupation', 'text', 'Occupation', 230, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(17, 'constituent', 'personal', 'organization', 'text', 'Organization', 240, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(18, 'constituent', 'personal', 'is_deceased', 'checked', 'Deceased?', 250, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(19, 'constituent', 'registration', 'voter_status', 'select', 'Voter Status', 310, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(20, 'constituent', 'registration', 'reg_date', 'range', 'Registration Date', 320, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(21, 'constituent', 'registration', 'party', 'select', 'Party', 330, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(22, 'constituent', 'registration', 'ward', 'text', 'Ward', 340, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(23, 'constituent', 'registration', 'precinct', 'text', 'Precinct', 350, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(24, 'constituent', 'registration', 'ssid', 'text', 'Secretary of State ID', 360, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(25, 'constituent', 'legacy', 'civi_id', 'text', 'CiviCRM ID', 410, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(26, 'constituent', 'legacy', 'van_id', 'text', 'VAN ID', 420, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(27, 'constituent', 'legacy', 'last_updated_time', 'range', 'Last Updated Time', 430, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(28, 'constituent', 'legacy', 'last_updated_by', 'text', 'Last Updated User', 440, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(29, 'activity', 'activity', 'ID', 'text', 'Internal ID for Activity', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(30, 'activity', 'activity', 'constituent_id', 'text', 'Constituent ID for Activity', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(31, 'activity', 'activity', 'date', 'range', '', 20, 0, 10, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Date'),
(32, 'activity', 'activity', 'activity_type', 'select', '', 30, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Type'),
(33, 'activity', 'activity', 'issue', 'select', '', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Issue'),
(34, 'activity', 'activity', 'pro_con', 'select', '', 50, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Pro/Con'),
(35, 'activity', 'activity_note', 'notes', 'textarea', '', 60, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Notes'),
(36, 'address', 'address_line_1', 'ID', 'text', 'Internal ID for Address', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(37, 'address', 'address_line_1', 'constituent_id', 'text', 'Constituent ID for Address', 20, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(38, 'address', 'address_line_1', 'address_type', 'text', '', 30, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Address Type'),
(39, 'address', 'address_line_1', 'street_number', 'text', '', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Number'),
(40, 'address', 'address_line_1', 'street_suffix', 'text', '', 50, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Suffix'),
(41, 'address', 'address_line_1', 'street_name', 'text', '', 60, 0, 0, '', 0, 0, 0, '', 1, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Street'),
(42, 'address', 'address_line_1', 'apartment', 'text', '', 70, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Apartment'),
(43, 'address', 'address_line_2', 'city', 'text', '', 80, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'City'),
(44, 'address', 'address_line_2', 'state', 'text', '', 90, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'State'),
(45, 'address', 'address_line_2', 'zip', 'text', '', 100, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Zip'),
(46, 'address', 'address_line_2', 'screen_deleted', 'deleted', 'x', 40, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(47, 'email', 'email_row', 'ID', 'text', 'Internal ID for Email', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(48, 'email', 'email_row', 'constituent_id', 'text', 'Constituent ID for Email', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(49, 'email', 'email_row', 'email_type', 'text', '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Email Type'),
(50, 'email', 'email_row', 'email_address', 'text', '', 30, 100, 0, 'individual', 1, 0, 0, '', 1, 0, '', '', 'validate_individual_email', '', '', '', 'wic-input', 'wic-label', 'Email Address'),
(51, 'email', 'email_row', 'screen_deleted', 'deleted', 'x', 40, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(52, 'phone', 'phone_row', 'ID', 'text', 'Internal ID for Phone', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(53, 'phone', 'phone_row', 'constituent_id', 'text', 'Constituent ID for Phone', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', ''),
(54, 'phone', 'phone_row', 'phone_type', 'text', '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Type'),
(55, 'phone', 'phone_row', 'phone', 'text', '', 30, 100, 0, 'individual', 0, 0, 0, '', 1, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Phone number (any format)'),
(56, 'phone', 'phone_row', 'extension', 'text', '', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', '', 'wic-input', 'wic-label', 'Extension'),
(57, 'phone', 'phone_row', 'screen_deleted', 'deleted', 'x', 50, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', '', 'wic-input', 'wic-label', '');

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_form_field_groups`
--

CREATE TABLE IF NOT EXISTS `wp_wic_form_field_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_slug` varchar(30) NOT NULL,
  `group_slug` varchar(30) NOT NULL,
  `group_label` varchar(30) NOT NULL,
  `group_legend` text NOT NULL,
  `group_order` smallint(6) NOT NULL DEFAULT '0',
  `initial_open` tinyint(1) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `wp_wic_form_field_groups`
--

INSERT INTO `wp_wic_form_field_groups` (`group_id`, `entity_slug`, `group_slug`, `group_label`, `group_legend`, `group_order`, `initial_open`) VALUES
(1, 'constituent', 'contact', 'Contact', '', 10, 1),
(4, 'constituent', 'activity', 'Activities', '', 20, 0),
(5, 'constituent', 'case', 'Case Management', '', 30, 0),
(6, 'constituent', 'personal', 'Personal Information', '', 40, 0),
(7, 'constituent', 'registration', 'Voter Information', 'These fields are read only -- searchable, but not updateable.', 50, 0),
(8, 'constituent', 'legacy', 'Legacy and Internal Codes', 'These fields are read only -- searchable, but not updateable.', 60, 0),
(9, 'activity', 'activity', 'Activity', '', 10, 1),
(10, 'activity', 'activity_note', 'Activity Note', '', 20, 0),
(11, 'address', 'address_line_1', 'Address Line 1', '', 10, 1),
(12, 'address', 'address_line_2', 'Address Line 2', '', 20, 0),
(13, 'email', 'email_row', 'Email Row', '', 10, 1),
(14, 'phone', 'phone_row', 'Phone Row', '', 10, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
