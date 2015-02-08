-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 08, 2015 at 03:58 PM
-- Server version: 5.5.41-0ubuntu0.14.04.1
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
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_slug` varchar(20) NOT NULL,
  `group_slug` varchar(30) NOT NULL,
  `field_slug` varchar(30) NOT NULL,
  `field_type` varchar(30) NOT NULL COMMENT 'name of entity supplying multiple rows for this field',
  `field_label` varchar(60) NOT NULL,
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
  `placeholder` varchar(50) NOT NULL,
  `option_group` varchar(50) NOT NULL,
  `onchange` varchar(40) NOT NULL,
  `list_formatter` varchar(50) NOT NULL,
  `reverse_sort` tinyint(1) NOT NULL DEFAULT '0',
  `customizable` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `mark_deleted` varchar(10) NOT NULL,
  `last_updated_by` bigint(20) NOT NULL,
  `last_updated_time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `entity_slug` (`entity_slug`),
  KEY `field_group` (`group_slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=149 ;

--
-- Dumping data for table `wp_wic_data_dictionary`
--

INSERT INTO `wp_wic_data_dictionary` (`ID`, `entity_slug`, `group_slug`, `field_slug`, `field_type`, `field_label`, `field_order`, `listing_order`, `sort_clause_order`, `required`, `dedup`, `readonly`, `hidden`, `field_default`, `like_search_enabled`, `transient`, `wp_query_parameter`, `placeholder`, `option_group`, `onchange`, `list_formatter`, `reverse_sort`, `customizable`, `enabled`, `mark_deleted`, `last_updated_by`, `last_updated_time`) VALUES
(1, 'activity', 'activity', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(2, 'constituent', 'registration', 'ID', 'text', 'Internal Id', 420, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(3, 'constituent', 'contact', 'first_name', 'text', 'Name', 10, 10, 30, 'group', 1, 0, 0, '', 2, 0, '', 'First', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(4, 'constituent', 'contact', 'middle_name', 'text', 'Middle Name', 20, 20, 40, '', 1, 0, 0, '', 2, 0, '', 'Middle ', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(5, 'constituent', 'contact', 'last_name', 'text', 'Last Name', 30, 30, 20, 'group', 1, 0, 0, '', 2, 0, '', 'Last', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(6, 'constituent', 'contact', 'phone', 'multivalue', 'Phones', 40, 40, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'phone_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(7, 'constituent', 'contact', 'email', 'multivalue', 'Emails', 50, 50, 0, 'group', 1, 0, 0, '', 0, 0, '', '', '', '', 'email_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(8, 'constituent', 'contact', 'address', 'multivalue', 'Addresses', 60, 60, 0, '', 1, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(10, 'constituent', 'contact', 'activity', 'multivalue', 'Activities', 80, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 1, 0, 1, '', 0, '0000-00-00 00:00:00'),
(11, 'constituent', 'case', 'case_assigned', 'select', 'Staff', 110, -3, 0, '', 0, 0, 0, '', 0, 0, '', '', 'get_administrator_array', 'changeCaseStatus()', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(12, 'constituent', 'case', 'case_status', 'select', 'Status', 120, -2, 0, '', 0, 0, 0, '', 0, 0, '', '', 'case_status_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(13, 'constituent', 'case', 'case_review_date', 'date', 'Review Date', 130, -1, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(14, 'constituent', 'personal', 'date_of_birth', 'date', 'Date of Birth', 85, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(15, 'constituent', 'personal', 'gender', 'select', 'Gender', 90, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'gender_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(18, 'constituent', 'personal', 'is_deceased', 'checked', 'Deceased?', 95, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(27, 'constituent', 'registration', 'last_updated_time', 'date', 'Last Updated Time', 430, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(28, 'constituent', 'registration', 'last_updated_by', 'select', 'Last Updated User', 440, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', 'constituent_last_updated_by', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(29, 'activity', 'activity', 'ID', 'text', 'Internal ID for Activity', 400, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(30, 'activity', 'activity', 'constituent_id', 'text', 'Constituent ID for Activity', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(31, 'activity', 'activity', 'activity_date', 'date', 'Date', 30, 0, 10, 'individual', 0, 0, 0, 'get_today', 0, 0, '', 'Date', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(32, 'activity', 'activity', 'activity_type', 'select', 'Type', 20, 0, 0, 'individual', 0, 0, 0, '', 0, 0, '', 'Type', 'activity_type_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(33, 'activity', 'activity_issue', 'issue', 'select', 'Issue', 40, 0, 0, 'individual', 0, 0, 0, '', 0, 0, '', 'Issue', 'get_issue_options', 'changeActivityIssueButtonDestination()', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(34, 'activity', 'activity_issue', 'pro_con', 'select', '', 50, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Pro/Con', 'pro_con_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(35, 'activity', 'activity_note', 'activity_note', 'textarea', '', 60, 0, 0, '', 0, 0, 0, '', 0, 0, '', ' . . . notes . . .', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(36, 'address', 'address_line_1', 'ID', 'text', 'Internal ID for Address', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(37, 'address', 'address_line_1', 'constituent_id', 'text', 'Constituent ID for Address', 20, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(38, 'address', 'address_line_1', 'address_type', 'select', '', 30, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Type', 'address_type_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(39, 'address', 'address_line_1', 'address_line', 'alpha', 'Street Address', 40, 0, 0, '', 1, 0, 0, '', 1, 0, '', '123 Main St', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(43, 'address', 'address_line_2', 'city', 'text', 'City', 80, 100, 0, 'individual', 1, 0, 0, '', 0, 0, '', 'City', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(44, 'address', 'address_line_2', 'state', 'select', '', 90, 0, 0, '', 1, 0, 0, '', 0, 0, '', 'State', 'state_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(45, 'address', 'address_line_2', 'zip', 'text', '', 100, 0, 0, '', 1, 0, 0, '', 0, 0, '', 'Zip', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(46, 'address', 'address_line_1', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(47, 'email', 'email_row', 'ID', 'text', 'Internal ID for Email', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(48, 'email', 'email_row', 'constituent_id', 'text', 'Constituent ID for Email', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(49, 'email', 'email_row', 'email_type', 'select', '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Type', 'email_type_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(50, 'email', 'email_row', 'email_address', 'text', 'Email Address', 30, 100, 0, 'individual', 1, 0, 0, '', 1, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(51, 'email', 'email_row', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(52, 'phone', 'phone_row', 'ID', 'text', 'Internal ID for Phone', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(53, 'phone', 'phone_row', 'constituent_id', 'text', 'Constituent ID for Phone', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(54, 'phone', 'phone_row', 'phone_type', 'select', '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Type', 'phone_type_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(55, 'phone', 'phone_row', 'phone_number', 'text', 'Phone Number', 30, 100, 0, 'individual', 0, 0, 0, '', 0, 0, '', '', '', '', 'phone_number_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(56, 'phone', 'phone_row', 'extension', 'text', '', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Ext.', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(57, 'phone', 'phone_row', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(58, 'constituent', 'search_parms', 'retrieve_limit', 'select', '# of Constituents to Show', 10, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', 'retrieve_limit_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(59, 'constituent', 'search_parms', 'compute_total', 'checked', 'Show Total Count', 30, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(60, 'constituent', 'search_parms', 'sort_order', 'checked', 'Sort records before retrieval', 40, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(61, 'constituent', 'search_parms', 'match_level', 'select', 'Name Match', 20, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', 'match_level_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(62, 'constituent', 'contact', 'mark_deleted', 'text', 'Mark Deleted', 999, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'DELETED', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(63, 'constituent', 'search_parms', 'show_deleted', 'checked', 'Include Deleted', 50, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(64, 'issue', 'issue_content', 'post_content', 'textarea', 'Issue Content', 20, 0, 0, '', 0, 0, 0, '', 0, 0, 'post_content', ' . . . issue content . . .', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(65, 'issue', 'issue_classification', 'tags_input', 'text', 'Tags', 10, 0, 0, '', 0, 0, 0, '', 0, 0, 'tag', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(66, 'issue', 'issue_classification', 'post_category', 'multiselect', 'Categories', 20, 50, 0, 'individual', 0, 0, 0, '', 0, 0, 'cat', '', 'get_post_category_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(67, 'issue', 'issue_content', 'post_title', 'text', 'Issue Title', 10, 40, 0, 'individual', 0, 0, 0, '', 0, 0, 'post_title', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(68, 'issue', 'issue_management', 'issue_staff', 'select', 'Staff', 10, 10, 0, '', 0, 0, 0, '', 0, 0, '', '', 'get_administrator_array', 'changeFollowUpStatus()', 'issue_staff_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(69, 'issue', 'activity_open', 'wic_live_issue', 'select', 'Activity Assignment', 20, 20, 0, '', 0, 0, 0, '', 0, 0, '', '', 'wic_live_issue_options', '', 'wic_live_issue_options', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(70, 'issue', 'issue_management', 'follow_up_status', 'select', 'Status', 30, 30, 0, '', 0, 0, 0, '', 0, 0, '', '', 'follow_up_status_options', '', 'follow_up_status_options', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(71, 'issue', 'issue_management', 'review_date', 'date', 'Review Date', 40, -1, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(72, 'issue', 'issue_creation', 'post_author', 'select', 'Created By', 10, 0, 0, '', 0, 1, 0, '', 0, 0, 'author', '', 'get_post_author_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(73, 'issue', 'issue_creation', 'post_date', 'date', 'Created Date', 20, 0, 10, '', 0, 1, 0, '', 0, 0, 'date', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(74, 'issue', 'issue_creation', 'post_status', 'select', 'Visibility', 30, 60, 0, '', 0, 1, 0, '', 0, 0, 'post_status', '', 'post_status_options', '', 'post_status_options', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(75, 'issue', 'issue_creation', 'ID', 'text', 'Post ID', 40, 1, 0, '', 0, 1, 0, '', 0, 0, 'p', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(81, 'search_log', 'search_log', 'id', 'text', 'ID', 10, -1, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(82, 'search_log', 'search_log', 'user_id', 'text', 'User ID', 15, 10, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'user_id_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(83, 'search_log', 'search_log', 'time', 'text', 'SearchTime', 20, 20, 10, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'time_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(84, 'search_log', 'search_log', 'entity', 'text', 'Entity', 30, 30, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(86, 'search_log', 'search_log', 'serialized_search_array', 'text', 'Search Details', 40, 40, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'serialized_search_array_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(87, 'search_log', 'search_log', 'download_time', 'text', 'Last Download', 50, 50, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'download_time_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(88, 'issue', 'search_parms', 'category_search_mode', 'radio', 'Category Search Mode', 10, 0, 0, '', 0, 0, 0, 'cat', 0, 1, '', '', 'category_search_mode_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(89, 'issue', 'search_parms', 'retrieve_limit', 'select', '# of Issues to Show', 20, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', 'retrieve_limit_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(90, 'constituent', 'save_options', 'no_dupcheck', 'checked', 'Suppress Dup Checking', 10, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(91, 'trend', 'trend', 'activity_date', 'date', 'Trend Period', 10, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(92, 'trend', 'trend', 'activity_type', 'select', 'Activity Type', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'activity_type_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(93, 'trend', 'trend', 'last_updated_by', 'select', 'Entered By', 30, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'activity_last_updated_by', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(94, 'option_group', 'option_group', 'option_group_slug', 'text', 'Option Group Slug', 10, 0, 10, 'individual', 1, 0, 0, '', 1, 0, '', 'no_spaces_allowed', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(95, 'option_group', 'option_group', 'option_group_desc', 'text', 'Description', 20, 20, 0, '', 0, 0, 0, '', 1, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(96, 'option_group', 'option_group', 'enabled', 'select', 'Enabled', 30, 30, 0, '', 1, 0, 0, '1', 0, 0, '', '', 'enabled_disabled_array', '', 'enabled_disabled_array', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(98, 'option_value', 'option_value', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(99, 'option_value', 'option_value', 'ID', 'text', 'Internal ID for Option Value', 400, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(100, 'option_value', 'option_value', 'option_group_id', 'text', 'Option Group Id', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(101, 'option_value', 'option_value', 'option_value', 'text', 'Database', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(102, 'option_value', 'option_value', 'option_label', 'text', 'Visible', 30, 10, 0, '', 0, 0, 0, '', 0, 0, '', 'Visible in drop down.', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(103, 'option_value', 'option_value', 'value_order', 'select', 'Order', 40, 0, 10, '', 0, 0, 0, '', 0, 0, '', '', 'order_array', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(104, 'option_value', 'option_value', 'enabled', 'select', 'Enabled', 50, 0, 0, '', 0, 0, 0, '1', 0, 0, '', '', 'enabled_disabled_array', '', 'enabled_disabled_array', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(105, 'option_group', 'option_group', 'ID', 'text', 'Internal ID for OptionGroup', 5, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(106, 'option_group', 'option_group', 'option_value', 'multivalue', 'Option Values', 40, 40, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'option_label_list_formatter', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(107, 'option_group', 'option_group', 'last_updated_by', 'select', 'Last Updated By', 60, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', 'constituent_last_updated_by', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(108, 'option_group', 'option_group', 'last_updated_time', 'text', 'Last Updated Time', 70, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(109, 'data_dictionary', 'data_dictionary', 'entity_slug', 'text', 'entity_slug', 1, 0, 0, '', 0, 1, 1, 'constituent', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(110, 'data_dictionary', 'data_dictionary', 'group_slug', 'select', 'Screen Group ', 30, 10, 10, 'individual', 0, 0, 0, '', 0, 0, '', '', 'customizable_groups', '', 'customizable_groups', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(111, 'data_dictionary', 'data_dictionary', 'field_slug', 'text', 'Database Name (fixed)', 10, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(112, 'data_dictionary', 'data_dictionary', 'field_type', 'select', 'Field Type', 60, 40, 0, 'individual', 0, 0, 0, '', 0, 0, '', '', 'custom_field_types', 'setFieldTypeDefaults()', 'custom_field_types', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(113, 'data_dictionary', 'data_dictionary', 'field_label', 'text', 'Visible Name', 20, 30, 20, 'individual', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(114, 'data_dictionary', 'data_dictionary', 'field_default', 'text', 'Default', 50, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(115, 'data_dictionary', 'data_dictionary', 'readonly', 'checked', 'Read Only', 90, 0, 0, '', 0, 0, 0, '0', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(116, 'data_dictionary', 'data_dictionary', 'option_group', 'select', 'Option Group ', 70, 50, 0, '', 0, 0, 0, '', 0, 0, '', '', 'list_option_groups', 'setFieldType()', 'decode_option_groups', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(117, 'data_dictionary', 'data_dictionary', 'list_formatter', 'select', 'List Formatter', 80, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'list_option_groups', '', 'decode_option_groups', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(118, 'data_dictionary', 'data_dictionary', 'enabled', 'select', 'Enabled', 100, 80, 0, '', 0, 0, 0, '1', 0, 0, '', '', 'enabled_disabled_array', '', 'enabled_disabled_array', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(119, 'data_dictionary', 'data_dictionary', 'field_order', 'select', 'Screen Order', 40, 0, 0, 'individual', 0, 0, 0, '', 0, 0, '', '', 'order_array', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(120, 'data_dictionary', 'data_dictionary', 'ID', 'text', 'Internal ID for Field', 2, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(124, 'data_dictionary', 'data_dictionary', 'like_search_enabled', 'select', 'Wild card searching', 84, 0, 0, '', 0, 0, 0, '1', 0, 0, '', '', 'like_search', '', 'like_search', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(147, 'search_log', 'search_log', 'result_count', 'text', 'Result Count', 110, 45, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(141, 'user', 'user', 'max_issues_to_show', 'select', 'Number to Show', 50, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'count_to_ten', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(142, 'user', 'user', 'ID', 'text', 'Wordpress User ID', 10, 0, 0, 'individual', 0, 0, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(143, 'user', 'user', 'display_name', 'text', 'User ', 20, 0, 0, '', 0, 1, 1, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(144, 'user', 'user', 'show_viewed_issue', 'checked', 'Show Viewed Issue', 30, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(145, 'user', 'user', 'show_latest_issues', 'select', 'Show Used Issues', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'show_latest_issues_options', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00'),
(146, 'search_log', 'search_log', 'serialized_search_parameters', 'text', 'Serialized Search Parameters', 100, 100, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', '', 0, 0, 1, '', 0, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_form_field_groups`
--

CREATE TABLE IF NOT EXISTS `wp_wic_form_field_groups` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `entity_slug` varchar(30) NOT NULL,
  `group_slug` varchar(30) NOT NULL,
  `group_label` varchar(40) NOT NULL,
  `group_legend` text NOT NULL,
  `group_order` smallint(6) NOT NULL DEFAULT '0',
  `initial_open` tinyint(1) NOT NULL,
  `sidebar_location` tinyint(1) NOT NULL,
  `last_updated_time` datetime NOT NULL,
  `last_updated_by` bigint(20) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

--
-- Dumping data for table `wp_wic_form_field_groups`
--

INSERT INTO `wp_wic_form_field_groups` (`ID`, `entity_slug`, `group_slug`, `group_label`, `group_legend`, `group_order`, `initial_open`, `sidebar_location`, `last_updated_time`, `last_updated_by`) VALUES
(1, 'constituent', 'contact', 'Contact', '', 10, 1, 0, '0000-00-00 00:00:00', 0),
(5, 'constituent', 'case', 'Case Management', '', 30, 1, 1, '0000-00-00 00:00:00', 0),
(6, 'constituent', 'personal', 'Personal Info', '', 40, 0, 1, '0000-00-00 00:00:00', 0),
(7, 'constituent', 'registration', 'Codes', 'These fields are read only -- searchable, but not updateable.', 50, 0, 1, '0000-00-00 00:00:00', 0),
(10, 'activity', 'activity_note', 'Activity Note', '', 20, 0, 0, '0000-00-00 00:00:00', 0),
(11, 'address', 'address_line_1', 'Address Line 1', '', 10, 1, 0, '0000-00-00 00:00:00', 0),
(12, 'address', 'address_line_2', 'Address Line 2', '', 20, 0, 0, '0000-00-00 00:00:00', 0),
(13, 'email', 'email_row', 'Email Row', '', 10, 1, 0, '0000-00-00 00:00:00', 0),
(14, 'phone', 'phone_row', 'Phone Row', '', 10, 1, 0, '0000-00-00 00:00:00', 0),
(15, 'constituent', 'search_parms', 'Search Options', '\n\n', 25, 1, 1, '0000-00-00 00:00:00', 0),
(16, 'activity', 'activity', '', '', 10, 0, 0, '0000-00-00 00:00:00', 0),
(17, 'activity', 'activity_issue', '', '', 15, 0, 0, '0000-00-00 00:00:00', 0),
(18, 'issue', 'issue_content', 'Issue Content', '', 10, 1, 0, '0000-00-00 00:00:00', 0),
(19, 'issue', 'issue_classification', 'Classification', '', 20, 1, 0, '0000-00-00 00:00:00', 0),
(20, 'issue', 'issue_management', 'Issue Management', '', 30, 1, 1, '0000-00-00 00:00:00', 0),
(21, 'issue', 'issue_creation', 'Codes', 'These fields are not updateable except through the regular Wordpress admin screens.', 40, 1, 1, '0000-00-00 00:00:00', 0),
(22, 'constituent', 'comment', 'Latest Online Comments', 'Note: If the online user''s email is not in WP-Issues-CRM, the online activity will not be shown here.  Online activity shown here can only be altered through the WP backend.  ', 20, 0, 0, '0000-00-00 00:00:00', 0),
(23, 'comment', 'comment', 'Online Comments', '', 10, 0, 0, '0000-00-00 00:00:00', 0),
(24, 'issue', 'search_parms', 'Search Options', 'You can select options for the categories search. Note: Tags are always joined by OR. Conditions collectively are always joined by ''AND''.', 25, 10, 1, '0000-00-00 00:00:00', 0),
(25, 'constituent', 'save_options', 'Save Options', '', 27, 1, 1, '0000-00-00 00:00:00', 0),
(26, 'issue', 'activity_open', 'Activity Tracking', 'Set issue as open for assignment of activities to make the issue appear on the issue drop down for activities.', 25, 1, 1, '0000-00-00 00:00:00', 0),
(27, 'trend', 'trend', 'Activity Trends', '', 10, 1, 0, '0000-00-00 00:00:00', 0),
(28, 'option_group', 'option_group', 'Option Groups', '', 10, 1, 0, '0000-00-00 00:00:00', 0),
(29, 'option_value', 'option_value', 'Option Values', '', 0, 0, 0, '0000-00-00 00:00:00', 0),
(30, 'data_dictionary', 'data_dictionary', 'Customizable Fields', '', 10, 1, 0, '0000-00-00 00:00:00', 0),
(31, 'user', 'user', 'WP Issues CRM User Preferences', '', 0, 1, 0, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_option_group`
--

CREATE TABLE IF NOT EXISTS `wp_wic_option_group` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_group_slug` varchar(30) NOT NULL,
  `option_group_desc` varchar(100) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_updated_time` datetime NOT NULL,
  `last_updated_by` bigint(20) NOT NULL,
  `mark_deleted` varchar(10) NOT NULL,
  `is_system_reserved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

--
-- Dumping data for table `wp_wic_option_group`
--

INSERT INTO `wp_wic_option_group` (`ID`, `option_group_slug`, `option_group_desc`, `enabled`, `last_updated_time`, `last_updated_by`, `mark_deleted`, `is_system_reserved`) VALUES
(1, 'activity_type_options', 'Activity Types', 1, '2015-02-02 11:55:19', 15, '', 0),
(2, 'address_type_options', 'Address Types', 1, '2015-01-26 00:00:00', 15, '', 0),
(3, 'case_status_options', 'Issue/Case Status Options', 1, '2015-01-28 00:00:00', 15, '', 0),
(4, 'category_search_mode_options', 'Category Search Mode Options', 1, '2015-01-24 00:00:00', 15, '', 1),
(5, 'email_type_options', 'Email Types', 1, '2015-01-24 00:00:00', 15, '', 0),
(6, 'follow_up_status_options', 'Follow up status options', 1, '2015-01-28 00:00:00', 15, '', 0),
(7, 'gender_options', 'Gender Codes', 1, '0000-00-00 00:00:00', 0, '', 0),
(8, 'match_level_options', 'match_level_options', 1, '0000-00-00 00:00:00', 0, '', 1),
(9, 'party_options', 'Political Party', 1, '2015-01-24 00:00:00', 15, '', 0),
(10, 'phone_type_options', 'Phone Types', 1, '2015-02-02 14:36:25', 15, '', 0),
(11, 'post_status_options', 'post_status_options', 1, '0000-00-00 00:00:00', 0, '', 1),
(12, 'pro_con_options', 'Pro/Con Options', 1, '0000-00-00 00:00:00', 0, '', 0),
(13, 'retrieve_limit_options', 'retrieve_limit_options', 1, '0000-00-00 00:00:00', 0, '', 1),
(14, 'state_options', 'State Options', 1, '2015-01-26 00:00:00', 15, '', 0),
(15, 'voter_status_options', 'Voter Status Options', 1, '0000-00-00 00:00:00', 0, '', 0),
(20, 'customizable_groups', 'Groups suitable for custom fields', 1, '2015-01-24 00:00:00', 15, '', 1),
(21, 'custom_field_types', 'Field Types', 1, '2015-01-25 00:00:00', 15, '', 1),
(23, 'like_search', 'Like Search Enabled', 1, '2015-01-25 00:00:00', 15, '', 1),
(24, 'enabled_disabled_array', 'Enabled/Disabled', 1, '2015-01-26 00:00:00', 15, '', 1),
(25, 'wic_live_issue_options', 'Live Issue Options', 1, '2015-01-26 00:00:00', 15, '', 1),
(27, 'show_latest_issues_options', 'Options for Activity Issues Dropdown', 1, '0000-00-00 00:00:00', 0, '', 1),
(28, 'count_to_ten', 'Number of issues to retrieve', 1, '0000-00-00 00:00:00', 0, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_option_value`
--

CREATE TABLE IF NOT EXISTS `wp_wic_option_value` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_group_id` varchar(50) NOT NULL,
  `option_value` varchar(50) NOT NULL,
  `option_label` varchar(200) NOT NULL,
  `value_order` smallint(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `last_updated_time` datetime NOT NULL,
  `last_updated_by` bigint(20) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `enabled` (`enabled`,`option_group_id`,`value_order`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=116 ;

--
-- Dumping data for table `wp_wic_option_value`
--

INSERT INTO `wp_wic_option_value` (`ID`, `option_group_id`, `option_value`, `option_label`, `value_order`, `enabled`, `last_updated_time`, `last_updated_by`) VALUES
(1, '3', '', '', 30, 1, '2015-01-28 00:00:00', 15),
(2, '3', '0', 'Closed', 10, 1, '2015-01-28 00:00:00', 15),
(3, '3', '1', 'Open', 20, 1, '2015-01-28 00:00:00', 15),
(4, '7', '', '', 30, 1, '0000-00-00 00:00:00', 0),
(5, '7', 'm', 'Male', 10, 1, '0000-00-00 00:00:00', 0),
(6, '7', 'f', 'Female', 20, 1, '0000-00-00 00:00:00', 0),
(7, '9', '', '', 90, 1, '2015-01-24 00:00:00', 15),
(8, '9', 'd', 'Democrat', 10, 1, '2015-01-24 00:00:00', 15),
(9, '9', 'r', 'Republican', 20, 1, '2015-01-24 00:00:00', 15),
(10, '9', 'u', 'Unenrolled', 30, 1, '2015-01-24 00:00:00', 15),
(11, '9', 'l', 'Libertarian', 40, 1, '2015-01-24 00:00:00', 15),
(12, '9', 'j', 'Green-Rainbow', 50, 1, '2015-01-24 00:00:00', 15),
(13, '9', 'g', 'Green-Party USA', 60, 1, '2015-01-24 00:00:00', 15),
(14, '9', 's', 'Socialist', 70, 1, '2015-01-24 00:00:00', 15),
(15, '9', 'o', 'Other', 80, 1, '2015-01-24 00:00:00', 15),
(16, '13', '50', 'Up to 50', 0, 1, '0000-00-00 00:00:00', 0),
(17, '13', '100', 'Up to 100', 10, 1, '0000-00-00 00:00:00', 0),
(18, '13', '500', 'Up to 500', 20, 1, '0000-00-00 00:00:00', 0),
(19, '8', '1', 'Right wild card', 0, 1, '0000-00-00 00:00:00', 0),
(20, '8', '2', 'Soundex', 10, 1, '0000-00-00 00:00:00', 0),
(21, '8', '0', 'Strict', 20, 1, '0000-00-00 00:00:00', 0),
(22, '15', '', '', 40, 1, '0000-00-00 00:00:00', 0),
(23, '15', 'a', 'Active', 10, 1, '0000-00-00 00:00:00', 0),
(24, '15', 'i', 'Inactive', 20, 1, '0000-00-00 00:00:00', 0),
(25, '15', 'x', 'Not Registered', 30, 1, '0000-00-00 00:00:00', 0),
(26, '12', '', 'Pro/Con?', 30, 1, '0000-00-00 00:00:00', 0),
(27, '12', '0', 'Pro', 10, 1, '0000-00-00 00:00:00', 0),
(28, '12', '1', 'Con', 20, 1, '0000-00-00 00:00:00', 0),
(30, '1', '0', 'eMail', 10, 1, '2015-02-02 11:55:19', 15),
(31, '1', '1', 'Call', 20, 1, '2015-01-28 00:00:00', 15),
(32, '1', '2', 'Petition', 30, 1, '2015-01-28 00:00:00', 15),
(33, '1', '3', 'Meeting', 40, 1, '2015-01-28 00:00:00', 15),
(34, '1', '4', 'Letter', 50, 1, '2015-01-28 00:00:00', 15),
(35, '1', '6', 'Conversion', 75, 1, '2015-01-28 00:00:00', 15),
(36, '1', '5', 'Web Contact', 70, 1, '2015-01-28 00:00:00', 15),
(37, '2', '', 'Type?', 50, 1, '2015-01-26 00:00:00', 15),
(38, '2', '0', 'Home', 10, 1, '2015-01-26 00:00:00', 15),
(39, '2', '1', 'Work', 20, 1, '2015-01-26 00:00:00', 15),
(40, '2', '2', 'Mail', 30, 1, '2015-01-26 00:00:00', 15),
(41, '2', '3', 'Other', 40, 1, '2015-01-26 00:00:00', 15),
(43, '14', 'MA', 'MA', 10, 1, '2015-01-26 00:00:00', 15),
(44, '5', '', 'Type?', 40, 1, '2015-01-24 00:00:00', 15),
(45, '5', '0', 'Personal', 10, 1, '2015-01-24 00:00:00', 15),
(46, '5', '1', 'Work', 20, 1, '2015-01-24 00:00:00', 15),
(47, '5', '2', 'Other', 30, 1, '2015-01-24 00:00:00', 15),
(48, '4', 'cat', 'Post must have ANY of selected categories and child categories will be included.', 1, 1, '2015-01-24 00:00:00', 15),
(49, '4', 'category__in', 'Post must have ANY of selected categories and child categories will NOT be included.', 10, 1, '2015-01-24 00:00:00', 15),
(50, '4', 'category__and', 'Post must have ALL selected categories.', 20, 1, '2015-01-24 00:00:00', 15),
(51, '4', 'category__not_in', 'Post must have NONE of selected categories.', 30, 1, '2015-01-24 00:00:00', 15),
(52, '6', '', '', 30, 1, '2015-01-28 00:00:00', 15),
(53, '6', 'closed', 'Closed', 10, 1, '2015-01-28 00:00:00', 15),
(54, '6', 'open', 'Open', 20, 1, '2015-01-28 00:00:00', 15),
(55, '11', '', '', 30, 1, '0000-00-00 00:00:00', 0),
(56, '11', 'publish', 'Public', 10, 1, '0000-00-00 00:00:00', 0),
(57, '11', 'private', 'Private', 20, 1, '0000-00-00 00:00:00', 0),
(61, '10', '0', 'Home', 5, 1, '2015-01-23 00:00:00', 15),
(62, '10', '1', 'Cell', 10, 1, '2015-01-23 00:00:00', 15),
(63, '10', '2', 'Work', 20, 1, '2015-01-23 00:00:00', 15),
(64, '10', '3', 'Fax', 30, 1, '2015-01-23 00:00:00', 15),
(65, '10', '4', 'Other', 40, 1, '2015-02-02 14:36:25', 15),
(66, '10', '', 'Type?', 50, 1, '2015-01-23 00:00:00', 15),
(68, '16', '1', 'clo', 10, 1, '2015-01-24 00:00:00', 15),
(69, '16', 'did', 'mulasdf', 0, 1, '2015-01-24 00:00:00', 15),
(70, '16', 'd', '', 0, 1, '2015-01-24 00:00:00', 15),
(77, '17', 'e', '', 20, 1, '2015-01-24 00:00:00', 15),
(78, '17', 'f', '', 190, 1, '2015-01-24 00:00:00', 15),
(79, '17', 'fe', '', 10, 1, '2015-01-24 00:00:00', 15),
(80, '1', '', 'Type?', 0, 1, '2015-02-02 11:55:19', 15),
(81, '20', '', 'N/A', 5, 1, '2015-01-24 00:00:00', 15),
(82, '20', 'personal', 'Personal Info', 40, 1, '2015-01-24 00:00:00', 15),
(83, '20', 'registration', 'Codes', 30, 1, '2015-01-24 00:00:00', 15),
(84, '20', 'case', 'Case Management', 20, 1, '2015-01-24 00:00:00', 15),
(85, '20', 'contact', 'Main Contact Group', 10, 1, '2015-01-24 00:00:00', 15),
(86, '21', 'date', 'Date (text as yyyy-mm-dd )', 30, 1, '2015-01-25 00:00:00', 15),
(87, '21', 'select', 'Drop Down', 20, 1, '2015-01-25 00:00:00', 15),
(88, '21', 'text', 'Text (255 max characters)', 10, 1, '2015-01-25 00:00:00', 15),
(93, '23', '1', 'Search with right wild card by default', 10, 1, '2015-01-25 00:00:00', 15),
(94, '23', '0', 'Always search exact match (or range)', 20, 1, '2015-01-25 00:00:00', 15),
(95, '24', '0', 'Disabled', 20, 1, '2015-01-26 00:00:00', 15),
(96, '24', '1', 'Enabled', 10, 1, '2015-01-26 00:00:00', 15),
(97, '25', 'closed', 'Closed for WP Issues CRM', 30, 1, '2015-01-26 00:00:00', 15),
(98, '25', 'open', 'Open for WP Issues CRM', 20, 1, '2015-01-26 00:00:00', 15),
(99, '25', '', '''Open/Closed?''', 10, 1, '2015-01-26 00:00:00', 15),
(101, '27', 'x', 'Show only the open issues', 10, 1, '0000-00-00 00:00:00', 0),
(102, '27', 'l', 'Show open and also recently used issues', 20, 1, '0000-00-00 00:00:00', 0),
(103, '27', 'f', 'Show open issues and also frequently used issues', 30, 1, '0000-00-00 00:00:00', 0),
(110, '28', '1', '1', 1, 1, '0000-00-00 00:00:00', 0),
(111, '28', '2', '2', 2, 1, '0000-00-00 00:00:00', 0),
(112, '28', '3', '3', 3, 1, '0000-00-00 00:00:00', 0),
(113, '28', '5', '5', 5, 1, '0000-00-00 00:00:00', 0),
(114, '28', '7', '7', 7, 1, '0000-00-00 00:00:00', 0),
(115, '28', '10', '10', 10, 1, '0000-00-00 00:00:00', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
