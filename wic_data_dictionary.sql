-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 02, 2014 at 08:06 AM
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=91 ;

--
-- Dumping data for table `wp_wic_data_dictionary`
--

INSERT INTO `wp_wic_data_dictionary` (`field_id`, `entity_slug`, `group_slug`, `field_slug`, `field_type`, `is_date`, `is_int`, `field_label`, `field_order`, `listing_order`, `sort_clause_order`, `required`, `dedup`, `readonly`, `hidden`, `field_default`, `like_search_enabled`, `secondary_alpha_search`, `transient`, `wp_query_parameter`, `input_class`, `label_class`, `placeholder`, `blank_prohibited`, `suppress_on_search`, `onchange`, `zero_is_null`) VALUES
(1, 'activity', 'activity', 'screen_deleted', 'deleted', 0, 0, 'x', 1, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(2, 'constituent', 'registration', 'ID', 'text', 0, 0, 'Internal Id', 0, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(3, 'constituent', 'contact', 'first_name', 'text', 0, 0, 'Name', 10, 10, 30, 'group', 1, 0, 0, '', 2, '', 0, '', 'wic-input', 'wic-label', 'First', 0, 0, '', 0),
(4, 'constituent', 'contact', 'middle_name', 'text', 0, 0, 'Middle Name', 20, 20, 40, '', 1, 0, 0, '', 2, '', 0, '', 'wic-input', 'wic-label', 'Middle ', 0, 0, '', 0),
(5, 'constituent', 'contact', 'last_name', 'text', 0, 0, 'Last Name', 30, 30, 20, 'group', 1, 0, 0, '', 2, '', 0, '', 'wic-input', 'wic-label', 'Last', 0, 0, '', 0),
(6, 'constituent', 'contact', 'phone', 'multivalue', 0, 0, 'Phones', 40, 40, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(7, 'constituent', 'contact', 'email', 'multivalue', 0, 0, 'Emails', 50, 50, 0, 'group', 1, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(8, 'constituent', 'contact', 'address', 'multivalue', 0, 0, 'Addresses', 60, 60, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(10, 'constituent', 'contact', 'activity', 'multivalue', 0, 0, 'Activities', 80, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(11, 'constituent', 'case', 'case_assigned', 'select', 0, 1, 'Staff', 110, -3, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, 'changeCaseStatus()', 1),
(12, 'constituent', 'case', 'case_status', 'select', 0, 0, 'Status', 120, -2, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(13, 'constituent', 'case', 'case_review_date', 'range', 1, 0, 'Review Date', 130, -1, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(14, 'constituent', 'personal', 'date_of_birth', 'range', 1, 0, 'Date of Birth', 210, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(15, 'constituent', 'personal', 'gender', 'select', 0, 0, 'Gender', 220, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(16, 'constituent', 'personal', 'occupation', 'text', 0, 0, 'Occupation', 230, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(17, 'constituent', 'personal', 'organization', 'text', 0, 0, 'Organization', 240, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(18, 'constituent', 'personal', 'is_deceased', 'checked', 0, 0, 'Deceased?', 250, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(19, 'constituent', 'registration', 'voter_status', 'select', 0, 0, 'Voter Status', 310, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(20, 'constituent', 'registration', 'reg_date', 'range', 1, 0, 'Registration Date', 320, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(21, 'constituent', 'registration', 'party', 'select', 0, 0, 'Party', 330, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(22, 'constituent', 'registration', 'ward', 'text', 0, 0, 'Ward', 340, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(23, 'constituent', 'registration', 'precinct', 'text', 0, 0, 'Precinct', 350, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(24, 'constituent', 'registration', 'ssid', 'text', 0, 0, 'Secretary of State ID', 360, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(25, 'constituent', 'registration', 'civi_id', 'text', 0, 0, 'CiviCRM ID', 410, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(26, 'constituent', 'registration', 'van_id', 'text', 0, 0, 'VAN ID', 420, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(27, 'constituent', 'registration', 'last_updated_time', 'text', 0, 0, 'Last Updated Time', 430, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(28, 'constituent', 'registration', 'last_updated_by', 'text', 0, 0, 'Last Updated User', 440, 0, 0, '', 0, 1, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(29, 'activity', 'activity', 'ID', 'text', 0, 0, 'Internal ID for Activity', 400, 0, 0, '', 0, 0, 1, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(30, 'activity', 'activity', 'constituent_id', 'text', 0, 0, 'Constituent ID for Activity', 10, 0, 0, '', 0, 0, 1, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(31, 'activity', 'activity', 'activity_date', 'range', 1, 0, 'Date', 30, 0, 10, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(32, 'activity', 'activity', 'activity_type', 'select', 0, 0, '', 20, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'Type', 0, 0, '', 0),
(33, 'activity', 'activity_issue', 'issue', 'select', 0, 0, '', 40, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'Issue', 0, 0, '', 0),
(34, 'activity', 'activity_issue', 'pro_con', 'select', 0, 0, '', 50, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'Pro/Con', 0, 0, '', 0),
(35, 'activity', 'activity_note', 'activity_note', 'textarea', 0, 0, '', 60, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', ' . . . notes . . .', 0, 0, '', 0),
(36, 'address', 'address_line_1', 'ID', 'text', 0, 0, 'Internal ID for Address', 0, 0, 0, '', 0, 0, 1, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(37, 'address', 'address_line_1', 'constituent_id', 'text', 0, 0, 'Constituent ID for Address', 20, 0, 0, '', 0, 0, 1, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(38, 'address', 'address_line_1', 'address_type', 'select', 0, 0, '', 30, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'Type', 0, 0, '', 0),
(39, 'address', 'address_line_1', 'address_line', 'text', 0, 0, 'Street Address', 40, 0, 0, '', 0, 0, 0, '', 1, 'street_name', 0, '', 'wic-input', 'wic-label', '123', 0, 0, '', 0),
(43, 'address', 'address_line_2', 'city', 'text', 0, 0, 'City', 80, 100, 0, 'individual', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'hidden-template', 'City', 0, 0, '', 0),
(44, 'address', 'address_line_2', 'state', 'select', 0, 0, '', 90, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'State', 0, 0, '', 0),
(45, 'address', 'address_line_2', 'zip', 'text', 0, 0, '', 100, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'Zip', 0, 0, '', 0),
(46, 'address', 'address_line_1', 'screen_deleted', 'deleted', 0, 0, 'x', 1, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(47, 'email', 'email_row', 'ID', 'text', 0, 0, 'Internal ID for Email', 0, 0, 0, '', 0, 0, 1, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(48, 'email', 'email_row', 'constituent_id', 'text', 0, 0, 'Constituent ID for Email', 10, 0, 0, '', 0, 0, 1, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(49, 'email', 'email_row', 'email_type', 'select', 0, 0, '', 20, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'Type', 0, 0, '', 0),
(50, 'email', 'email_row', 'email_address', 'text', 0, 0, 'Email Address', 30, 100, 0, 'individual', 1, 0, 0, '', 1, '', 0, '', 'wic-input', 'hidden-template', '', 0, 0, '', 0),
(51, 'email', 'email_row', 'screen_deleted', 'deleted', 0, 0, 'x', 1, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(52, 'phone', 'phone_row', 'ID', 'text', 0, 0, 'Internal ID for Phone', 0, 0, 0, '', 0, 0, 1, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(53, 'phone', 'phone_row', 'constituent_id', 'text', 0, 0, 'Constituent ID for Phone', 10, 0, 0, '', 0, 0, 1, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(54, 'phone', 'phone_row', 'phone_type', 'select', 0, 0, '', 20, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'Type', 0, 0, '', 0),
(55, 'phone', 'phone_row', 'phone', 'text', 0, 0, 'Phone Number', 30, 100, 0, 'individual', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'hidden-template', '', 0, 0, '', 0),
(56, 'phone', 'phone_row', 'extension', 'text', 0, 0, '', 40, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'Ext.', 0, 1, '', 0),
(57, 'phone', 'phone_row', 'screen_deleted', 'deleted', 0, 0, 'x', 1, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(58, 'constituent', 'search_parms', 'retrieve_limit', 'select', 0, 0, '# of Constituents to Show', 10, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 1, 0, '', 0),
(59, 'constituent', 'search_parms', 'compute_total', 'checked', 0, 0, 'Show Total Count', 30, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(60, 'constituent', 'search_parms', 'sort_order', 'checked', 0, 0, 'Sort Records', 40, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(61, 'constituent', 'search_parms', 'match_level', 'select', 0, 0, 'Name Match', 20, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 1, 0, '', 0),
(62, 'constituent', 'contact', 'mark_deleted', 'text', 0, 0, 'Mark Deleted', 999, 0, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', 'DELETED', 0, 0, '', 0),
(63, 'constituent', 'search_parms', 'show_deleted', 'checked', 0, 0, 'Include Deleted', 50, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(64, 'issue', 'issue_content', 'post_content', 'textarea', 0, 0, 'Issue Content', 20, 0, 0, '', 0, 0, 0, '', 0, '', 0, 'post_content', 'wic-input', 'wic-label', ' . . . issue content . . .', 0, 1, '', 0),
(65, 'issue', 'issue_classification', 'tags_input', 'text', 0, 0, 'Tags', 10, 0, 0, '', 0, 0, 0, '', 0, '', 0, 'tag', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(66, 'issue', 'issue_classification', 'post_category', 'multiselect', 0, 0, 'Categories', 20, 50, 0, 'individual', 0, 0, 0, '', 0, '', 0, 'cat', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(67, 'issue', 'issue_content', 'post_title', 'text', 0, 0, 'Issue Title', 10, 40, 0, 'individual', 0, 0, 0, '', 0, '', 0, 'post_title', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(68, 'issue', 'issue_management', 'issue_staff', 'select', 0, 0, 'Staff', 10, 10, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, 'changeFollowUpStatus()', 0),
(69, 'issue', 'issue_management', 'wic_live_issue', 'select', 0, 0, 'Activity', 20, 20, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(70, 'issue', 'issue_management', 'follow_up_status', 'select', 0, 0, 'Status', 30, 30, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(71, 'issue', 'issue_management', 'review_date', 'range', 1, 0, 'Review Date', 40, -1, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(72, 'issue', 'issue_creation', 'post_author', 'select', 0, 0, 'Created By', 10, 0, 0, '', 0, 1, 0, '', 0, '', 0, 'author', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(73, 'issue', 'issue_creation', 'post_date', 'range', 1, 0, 'Created Date', 20, 0, 10, '', 0, 1, 0, '', 0, '', 0, 'date', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(74, 'issue', 'issue_creation', 'post_status', 'select', 0, 0, 'Visibility', 30, 60, 0, '', 0, 1, 0, '', 0, '', 0, 'post_status', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(75, 'issue', 'issue_creation', 'ID', 'text', 0, 0, 'Post ID', 40, 1, 0, '', 0, 1, 0, '', 0, '', 0, 'p', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(81, 'search_log', 'search_log', 'id', 'text', 0, 0, 'ID', 10, -1, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(82, 'search_log', 'search_log', 'user_id', 'text', 0, 0, 'User ID', 15, 10, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(83, 'search_log', 'search_log', 'time', 'text', 0, 0, 'SearchTime', 20, 20, 10, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(84, 'search_log', 'search_log', 'entity', 'text', 0, 0, 'Entity', 30, 30, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(86, 'search_log', 'search_log', 'serialized_search_array', 'text', 0, 0, 'Search Details', 40, 40, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(87, 'search_log', 'search_log', 'download_time', 'text', 0, 0, 'Last Download', 50, 50, 0, '', 0, 0, 0, '', 0, '', 0, '', 'wic-input', 'wic-label', '', 0, 0, '', 0),
(88, 'issue', 'search_parms', 'category_search_mode', 'radio', 0, 0, 'Category Search Mode', 10, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 1, 0, '', 0),
(89, 'issue', 'search_parms', 'retrieve_limit', 'select', 0, 0, '# of Issues to Show', 20, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 1, 0, '', 0),
(90, 'constituent', 'save_options', 'no_dupcheck', 'checked', 0, 0, 'Suppress Dup Checking', 10, 0, 0, '', 0, 0, 0, '', 0, '', 1, '', 'wic-input', 'wic-label', '', 0, 1, '', 0);

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
  `sidebar_location` tinyint(1) NOT NULL,
  `save_update_only` tinyint(1) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `wp_wic_form_field_groups`
--

INSERT INTO `wp_wic_form_field_groups` (`group_id`, `entity_slug`, `group_slug`, `group_label`, `group_legend`, `group_order`, `initial_open`, `search_only`, `sidebar_location`, `save_update_only`) VALUES
(1, 'constituent', 'contact', 'Contact', '', 10, 1, 0, 0, 0),
(5, 'constituent', 'case', 'Case Management', '', 30, 1, 0, 1, 0),
(6, 'constituent', 'personal', 'Personal Info', '', 40, 0, 0, 1, 0),
(7, 'constituent', 'registration', 'Codes', 'These fields are read only -- searchable, but not updateable.', 50, 0, 0, 1, 0),
(10, 'activity', 'activity_note', 'Activity Note', '', 20, 0, 0, 0, 0),
(11, 'address', 'address_line_1', 'Address Line 1', '', 10, 1, 0, 0, 0),
(12, 'address', 'address_line_2', 'Address Line 2', '', 20, 0, 0, 0, 0),
(13, 'email', 'email_row', 'Email Row', '', 10, 1, 0, 0, 0),
(14, 'phone', 'phone_row', 'Phone Row', '', 10, 1, 0, 0, 0),
(15, 'constituent', 'search_parms', 'Search Options', '\n\n', 25, 1, 1, 1, 0),
(16, 'activity', 'activity', '', '', 10, 0, 0, 0, 0),
(17, 'activity', 'activity_issue', '', '', 15, 0, 0, 0, 0),
(18, 'issue', 'issue_content', 'Issue Content', '', 10, 1, 0, 0, 0),
(19, 'issue', 'issue_classification', 'Classification', '', 20, 1, 0, 0, 0),
(20, 'issue', 'issue_management', 'Issue Management', '', 30, 1, 0, 1, 0),
(21, 'issue', 'issue_creation', 'Codes', 'These fields are not updateable except through the regular Wordpress admin screens.', 40, 0, 0, 1, 0),
(22, 'constituent', 'comment', 'Latest Online Comments', 'Note: If the online user''s email is not in WP-Issues-CRM, the online activity will not be shown here.  Online activity shown here can only be altered through the WP backend.  ', 20, 0, 0, 0, 0),
(23, 'comment', 'comment', 'Online Comments', '', 10, 0, 0, 0, 0),
(24, 'issue', 'search_parms', 'Search Options', 'Other conditions are always joined by ''AND'', but you can select options for the categories search.', 25, 10, 1, 1, 0),
(25, 'constituent', 'save_options', 'Save Options', '', 27, 1, 0, 1, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
