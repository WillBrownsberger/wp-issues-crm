-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 21, 2014 at 07:21 PM
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
  `is_date` tinyint(1) NOT NULL,
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
  `input_class` varchar(30) NOT NULL DEFAULT 'wic-input',
  `label_class` varchar(30) NOT NULL DEFAULT 'wic-label',
  `placeholder` varchar(50) NOT NULL,
  `blank_prohibited` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`),
  KEY `entity_slug` (`entity_slug`),
  KEY `field_group` (`group_slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=64 ;

--
-- Dumping data for table `wp_wic_data_dictionary`
--

INSERT INTO `wp_wic_data_dictionary` (`field_id`, `entity_slug`, `group_slug`, `field_slug`, `field_type`, `is_date`, `field_label`, `field_order`, `listing_order`, `sort_clause_order`, `required`, `dedup`, `readonly`, `hidden`, `field_default`, `like_search_enabled`, `transient`, `wp_query_parameter`, `input_class`, `label_class`, `placeholder`, `blank_prohibited`) VALUES
(1, 'activity', 'activity', 'screen_deleted', 'deleted', 0, 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 0),
(2, 'constituent', 'registration', 'ID', 'text', 0, 'Internal Id', 0, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(3, 'constituent', 'contact', 'first_name', 'text', 0, 'First Name', 10, 10, 30, 'group', 1, 0, 0, '', 2, 0, '', 'wic-input', 'wic-label', '', 0),
(4, 'constituent', 'contact', 'middle_name', 'text', 0, 'Middle Name', 20, 20, 40, '', 1, 0, 0, '', 2, 0, '', 'wic-input', 'wic-label', '', 0),
(5, 'constituent', 'contact', 'last_name', 'text', 0, 'Last Name', 30, 30, 20, 'group', 1, 0, 0, '', 2, 0, '', 'wic-input', 'wic-label', '', 0),
(6, 'constituent', 'contact', 'phone', 'multivalue', 0, 'Phones', 40, 40, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(7, 'constituent', 'contact', 'email', 'multivalue', 0, 'Emails', 50, 50, 0, 'group', 1, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(8, 'constituent', 'contact', 'address', 'multivalue', 0, 'Addresses', 60, 60, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(10, 'constituent', 'contact', 'activity', 'multivalue', 0, 'Activities', 80, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(11, 'constituent', 'case', 'case_assigned', 'select', 0, 'Staff', 110, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(12, 'constituent', 'case', 'case_status', 'select', 0, 'Status', 120, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(13, 'constituent', 'case', 'case_review_date', 'range', 1, 'Review Date', 130, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(14, 'constituent', 'personal', 'date_of_birth', 'range', 1, 'Date of Birth', 210, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(15, 'constituent', 'personal', 'gender', 'select', 0, 'Gender', 220, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(16, 'constituent', 'personal', 'occupation', 'text', 0, 'Occupation', 230, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(17, 'constituent', 'personal', 'organization', 'text', 0, 'Organization', 240, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(18, 'constituent', 'personal', 'is_deceased', 'checked', 0, 'Deceased?', 250, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(19, 'constituent', 'registration', 'voter_status', 'select', 0, 'Voter Status', 310, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(20, 'constituent', 'registration', 'reg_date', 'range', 0, 'Registration Date', 320, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(21, 'constituent', 'registration', 'party', 'select', 0, 'Party', 330, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(22, 'constituent', 'registration', 'ward', 'text', 0, 'Ward', 340, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(23, 'constituent', 'registration', 'precinct', 'text', 0, 'Precinct', 350, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(24, 'constituent', 'registration', 'ssid', 'text', 0, 'Secretary of State ID', 360, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(25, 'constituent', 'registration', 'civi_id', 'text', 0, 'CiviCRM ID', 410, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(26, 'constituent', 'registration', 'van_id', 'text', 0, 'VAN ID', 420, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(27, 'constituent', 'registration', 'last_updated_time', 'text', 0, 'Last Updated Time', 430, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(28, 'constituent', 'registration', 'last_updated_by', 'text', 0, 'Last Updated User', 440, 0, 0, '', 0, 1, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(29, 'activity', 'activity', 'ID', 'text', 0, 'Internal ID for Activity', 400, 0, 0, '', 0, 0, 1, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(30, 'activity', 'activity', 'constituent_id', 'text', 0, 'Constituent ID for Activity', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(31, 'activity', 'activity', 'activity_date', 'range', 1, 'Date', 30, 0, 10, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(32, 'activity', 'activity', 'activity_type', 'select', 0, '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'Type', 0),
(33, 'activity', 'activity_issue', 'issue', 'select', 0, '', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'Issue', 0),
(34, 'activity', 'activity_issue', 'pro_con', 'select', 0, '', 50, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'Pro/Con', 0),
(35, 'activity', 'activity_note', 'activity_note', 'textarea', 0, '', 60, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', ' . . . notes . . .', 0),
(36, 'address', 'address_line_1', 'ID', 'text', 0, 'Internal ID for Address', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(37, 'address', 'address_line_1', 'constituent_id', 'text', 0, 'Constituent ID for Address', 20, 0, 0, '', 0, 0, 1, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(38, 'address', 'address_line_1', 'address_type', 'select', 0, '', 30, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'Type', 0),
(39, 'address', 'address_line_1', 'street_number', 'text', 0, '', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '123', 0),
(40, 'address', 'address_line_1', 'street_suffix', 'text', 0, '', 50, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'R', 0),
(41, 'address', 'address_line_1', 'street_name', 'text', 0, '', 60, 0, 0, '', 0, 0, 0, '', 1, 0, '', 'wic-input', 'wic-label', 'Main St', 0),
(42, 'address', 'address_line_1', 'apartment', 'text', 0, '', 70, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', '101A', 0),
(43, 'address', 'address_line_2', 'city', 'text', 0, 'City', 80, 100, 0, 'individual', 0, 0, 0, '', 0, 0, '', 'wic-input', 'hidden-template', 'City', 0),
(44, 'address', 'address_line_2', 'state', 'select', 0, '', 90, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'State', 0),
(45, 'address', 'address_line_2', 'zip', 'text', 0, '', 100, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'Zip', 0),
(46, 'address', 'address_line_1', 'screen_deleted', 'deleted', 0, 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 0),
(47, 'email', 'email_row', 'ID', 'text', 0, 'Internal ID for Email', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(48, 'email', 'email_row', 'constituent_id', 'text', 0, 'Constituent ID for Email', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(49, 'email', 'email_row', 'email_type', 'select', 0, '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'Type', 0),
(50, 'email', 'email_row', 'email_address', 'text', 0, 'Email Address', 30, 100, 0, 'individual', 1, 0, 0, '', 1, 0, '', 'wic-input', 'hidden-template', '', 0),
(51, 'email', 'email_row', 'screen_deleted', 'deleted', 0, 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 0),
(52, 'phone', 'phone_row', 'ID', 'text', 0, 'Internal ID for Phone', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(53, 'phone', 'phone_row', 'constituent_id', 'text', 0, 'Constituent ID for Phone', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', 'wic-input', 'wic-label', '', 0),
(54, 'phone', 'phone_row', 'phone_type', 'select', 0, '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'Type', 0),
(55, 'phone', 'phone_row', 'phone', 'text', 0, 'Phone Number', 30, 100, 0, 'individual', 0, 0, 0, '', 0, 0, '', 'wic-input', 'hidden-template', '', 0),
(56, 'phone', 'phone_row', 'extension', 'text', 0, '', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'Ext.', 0),
(57, 'phone', 'phone_row', 'screen_deleted', 'deleted', 0, 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 0),
(58, 'constituent', 'search_parms', 'retrieve_limit', 'select', 0, '# of Records to Show', 10, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 1),
(59, 'constituent', 'search_parms', 'compute_total', 'checked', 0, 'Show Total Count', 30, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 0),
(60, 'constituent', 'search_parms', 'sort_order', 'checked', 0, 'Sort Records', 40, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 0),
(61, 'constituent', 'search_parms', 'match_level', 'select', 0, 'Set match approach', 20, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 1),
(62, 'constituent', 'contact', 'mark_deleted', 'text', 0, 'Mark Deleted', 999, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'wic-input', 'wic-label', 'DELETED', 0),
(63, 'constituent', 'search_parms', 'show_deleted', 'checked', 0, 'Include Deleted', 50, 0, 0, '', 0, 0, 0, '', 0, 1, '', 'wic-input', 'wic-label', '', 0);

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
  `search_only` tinyint(1) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `wp_wic_form_field_groups`
--

INSERT INTO `wp_wic_form_field_groups` (`group_id`, `entity_slug`, `group_slug`, `group_label`, `group_legend`, `group_order`, `initial_open`, `search_only`) VALUES
(1, 'constituent', 'contact', 'Contact', '', 10, 1, 0),
(5, 'constituent', 'case', 'Case Management', '', 30, 0, 0),
(6, 'constituent', 'personal', 'Personal Information', '', 40, 0, 0),
(7, 'constituent', 'registration', 'Registration and Internal Codes', 'These fields are read only -- searchable, but not updateable.', 50, 0, 0),
(10, 'activity', 'activity_note', 'Activity Note', '', 20, 0, 0),
(11, 'address', 'address_line_1', 'Address Line 1', '', 10, 1, 0),
(12, 'address', 'address_line_2', 'Address Line 2', '', 20, 0, 0),
(13, 'email', 'email_row', 'Email Row', '', 10, 1, 0),
(14, 'phone', 'phone_row', 'Phone Row', '', 10, 1, 0),
(15, 'constituent', 'search_parms', 'Search Options', 'By default, a maximum of 10 records will be retrieved, unsorted, and no total count of existing records will be computed. You can alter these settings here if needed.  You can also override the default use of wildcard searching on the fields that have a ''(%)'' after them. The default behavior for these fields is to allow soundex searching for names and wildcard searching on the right side for other enabled fields.  Textarea fields are always searched as full text (right and left wildcards) unless strict matching is set.\n\n', 70, 0, 1),
(16, 'activity', 'activity', '', '', 10, 0, 0),
(17, 'activity', 'activity_issue', '', '', 15, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
