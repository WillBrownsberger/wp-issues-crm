-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 20, 2015 at 04:14 PM
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
  `placeholder` varchar(50) NOT NULL,
  `option_group` varchar(50) NOT NULL,
  `onchange` varchar(40) NOT NULL,
  `list_formatter` varchar(50) NOT NULL,
  PRIMARY KEY (`field_id`),
  KEY `entity_slug` (`entity_slug`),
  KEY `field_group` (`group_slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=94 ;

--
-- Dumping data for table `wp_wic_data_dictionary`
--

INSERT INTO `wp_wic_data_dictionary` (`field_id`, `entity_slug`, `group_slug`, `field_slug`, `field_type`, `field_label`, `field_order`, `listing_order`, `sort_clause_order`, `required`, `dedup`, `readonly`, `hidden`, `field_default`, `like_search_enabled`, `transient`, `wp_query_parameter`, `placeholder`, `option_group`, `onchange`, `list_formatter`) VALUES
(1, 'activity', 'activity', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', ''),
(2, 'constituent', 'registration', 'ID', 'text', 'Internal Id', 0, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', ''),
(3, 'constituent', 'contact', 'first_name', 'text', 'Name', 10, 10, 30, 'group', 1, 0, 0, '', 2, 0, '', 'First', '', '', ''),
(4, 'constituent', 'contact', 'middle_name', 'text', 'Middle Name', 20, 20, 40, '', 1, 0, 0, '', 2, 0, '', 'Middle ', '', '', ''),
(5, 'constituent', 'contact', 'last_name', 'text', 'Last Name', 30, 30, 20, 'group', 1, 0, 0, '', 2, 0, '', 'Last', '', '', ''),
(6, 'constituent', 'contact', 'phone', 'multivalue', 'Phones', 40, 40, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'phone_formatter'),
(7, 'constituent', 'contact', 'email', 'multivalue', 'Emails', 50, 50, 0, 'group', 1, 0, 0, '', 0, 0, '', '', '', '', 'email_formatter'),
(8, 'constituent', 'contact', 'address', 'multivalue', 'Addresses', 60, 60, 0, '', 1, 0, 0, '', 0, 0, '', '', '', '', ''),
(10, 'constituent', 'contact', 'activity', 'multivalue', 'Activities', 80, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(11, 'constituent', 'case', 'case_assigned', 'select', 'Staff', 110, -3, 0, '', 0, 0, 0, '', 0, 0, '', '', 'get_administrator_array', 'changeCaseStatus()', ''),
(12, 'constituent', 'case', 'case_status', 'select', 'Status', 120, -2, 0, '', 0, 0, 0, '', 0, 0, '', '', 'case_status_options', '', ''),
(13, 'constituent', 'case', 'case_review_date', 'date', 'Review Date', 130, -1, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(14, 'constituent', 'personal', 'date_of_birth', 'date', 'Date of Birth', 210, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(15, 'constituent', 'personal', 'gender', 'select', 'Gender', 220, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'gender_options', '', ''),
(16, 'constituent', 'personal', 'occupation', 'text', 'Occupation', 230, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(17, 'constituent', 'personal', 'organization', 'text', 'Organization', 240, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(18, 'constituent', 'personal', 'is_deceased', 'checked', 'Deceased?', 250, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(19, 'constituent', 'registration', 'voter_status', 'select', 'Voter Status', 310, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', 'voter_status_options', '', ''),
(20, 'constituent', 'registration', 'reg_date', 'date', 'Registration Date', 320, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', ''),
(21, 'constituent', 'registration', 'party', 'select', 'Party', 330, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', 'party_options', '', ''),
(22, 'constituent', 'registration', 'ward', 'text', 'Ward', 340, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', ''),
(23, 'constituent', 'registration', 'precinct', 'text', 'Precinct', 350, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', ''),
(24, 'constituent', 'registration', 'ssid', 'text', 'Secretary of State ID', 360, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', ''),
(25, 'constituent', 'registration', 'civi_id', 'text', 'CiviCRM ID', 410, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', ''),
(26, 'constituent', 'registration', 'van_id', 'text', 'VAN ID', 420, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', ''),
(27, 'constituent', 'registration', 'last_updated_time', 'date', 'Last Updated Time', 430, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', '', '', ''),
(28, 'constituent', 'registration', 'last_updated_by', 'select', 'Last Updated User', 440, 0, 0, '', 0, 1, 0, '', 0, 0, '', '', 'constituent_last_updated_by', '', ''),
(29, 'activity', 'activity', 'ID', 'text', 'Internal ID for Activity', 400, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', ''),
(30, 'activity', 'activity', 'constituent_id', 'text', 'Constituent ID for Activity', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', ''),
(31, 'activity', 'activity', 'activity_date', 'date', 'Date', 30, 0, 10, 'individual', 0, 0, 0, 'get_today', 0, 0, '', 'Date', '', '', ''),
(32, 'activity', 'activity', 'activity_type', 'select', 'Type', 20, 0, 0, 'individual', 0, 0, 0, '', 0, 0, '', 'Type', 'activity_type_options', '', ''),
(33, 'activity', 'activity_issue', 'issue', 'select', 'Issue', 40, 0, 0, 'individual', 0, 0, 0, '', 0, 0, '', 'Issue', 'get_issue_options', 'changeActivityIssueButtonDestination()', ''),
(34, 'activity', 'activity_issue', 'pro_con', 'select', '', 50, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Pro/Con', 'pro_con_options', '', ''),
(35, 'activity', 'activity_note', 'activity_note', 'textarea', '', 60, 0, 0, '', 0, 0, 0, '', 0, 0, '', ' . . . notes . . .', '', '', ''),
(36, 'address', 'address_line_1', 'ID', 'text', 'Internal ID for Address', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', ''),
(37, 'address', 'address_line_1', 'constituent_id', 'text', 'Constituent ID for Address', 20, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', ''),
(38, 'address', 'address_line_1', 'address_type', 'select', '', 30, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Type', 'address_type_options', '', ''),
(39, 'address', 'address_line_1', 'address_line', 'alpha', 'Street Address', 40, 0, 0, '', 1, 0, 0, '', 1, 0, '', '123', '', '', ''),
(43, 'address', 'address_line_2', 'city', 'text', 'City', 80, 100, 0, 'individual', 1, 0, 0, '', 0, 0, '', 'City', '', '', ''),
(44, 'address', 'address_line_2', 'state', 'select', '', 90, 0, 0, '', 1, 0, 0, '', 0, 0, '', 'State', 'state_options', '', ''),
(45, 'address', 'address_line_2', 'zip', 'text', '', 100, 0, 0, '', 1, 0, 0, '', 0, 0, '', 'Zip', '', '', ''),
(46, 'address', 'address_line_1', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', ''),
(47, 'email', 'email_row', 'ID', 'text', 'Internal ID for Email', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', ''),
(48, 'email', 'email_row', 'constituent_id', 'text', 'Constituent ID for Email', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', ''),
(49, 'email', 'email_row', 'email_type', 'select', '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Type', 'email_type_options', '', ''),
(50, 'email', 'email_row', 'email_address', 'text', 'Email Address', 30, 100, 0, 'individual', 1, 0, 0, '', 1, 0, '', '', '', '', ''),
(51, 'email', 'email_row', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', ''),
(52, 'phone', 'phone_row', 'ID', 'text', 'Internal ID for Phone', 0, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', ''),
(53, 'phone', 'phone_row', 'constituent_id', 'text', 'Constituent ID for Phone', 10, 0, 0, '', 0, 0, 1, '', 0, 0, '', '', '', '', ''),
(54, 'phone', 'phone_row', 'phone_type', 'select', '', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Type', 'phone_type_options', '', ''),
(55, 'phone', 'phone_row', 'phone_number', 'text', 'Phone Number', 30, 100, 0, 'individual', 0, 0, 0, '', 0, 0, '', '', '', '', 'phone_number_formatter'),
(56, 'phone', 'phone_row', 'extension', 'text', '', 40, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'Ext.', '', '', ''),
(57, 'phone', 'phone_row', 'screen_deleted', 'deleted', 'x', 1, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', ''),
(58, 'constituent', 'search_parms', 'retrieve_limit', 'select', '# of Constituents to Show', 10, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', 'retrieve_limit_options', '', ''),
(59, 'constituent', 'search_parms', 'compute_total', 'checked', 'Show Total Count', 30, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', ''),
(60, 'constituent', 'search_parms', 'sort_order', 'checked', 'Sort records before retrieval', 40, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', ''),
(61, 'constituent', 'search_parms', 'match_level', 'select', 'Name Match', 20, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', 'match_level_options', '', ''),
(62, 'constituent', 'contact', 'mark_deleted', 'text', 'Mark Deleted', 999, 0, 0, '', 0, 0, 0, '', 0, 0, '', 'DELETED', '', '', ''),
(63, 'constituent', 'search_parms', 'show_deleted', 'checked', 'Include Deleted', 50, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', ''),
(64, 'issue', 'issue_content', 'post_content', 'textarea', 'Issue Content', 20, 0, 0, '', 0, 0, 0, '', 0, 0, 'post_content', ' . . . issue content . . .', '', '', ''),
(65, 'issue', 'issue_classification', 'tags_input', 'text', 'Tags', 10, 0, 0, '', 0, 0, 0, '', 0, 0, 'tag', '', '', '', ''),
(66, 'issue', 'issue_classification', 'post_category', 'multiselect', 'Categories', 20, 50, 0, 'individual', 0, 0, 0, '', 0, 0, 'cat', '', 'get_post_category_options', '', ''),
(67, 'issue', 'issue_content', 'post_title', 'text', 'Issue Title', 10, 40, 0, 'individual', 0, 0, 0, '', 0, 0, 'post_title', '', '', '', ''),
(68, 'issue', 'issue_management', 'issue_staff', 'select', 'Staff', 10, 10, 0, '', 0, 0, 0, '', 0, 0, '', '', 'get_administrator_array', 'changeFollowUpStatus()', 'issue_staff_formatter'),
(69, 'issue', 'activity_open', 'wic_live_issue', 'select', 'Activity Assignment', 20, 20, 0, '', 0, 0, 0, '', 0, 0, '', '', 'get_wic_live_issue_options', '', 'wic_live_issue_formatter'),
(70, 'issue', 'issue_management', 'follow_up_status', 'select', 'Status', 30, 30, 0, '', 0, 0, 0, '', 0, 0, '', '', 'follow_up_status_options', '', 'follow_up_status_formatter'),
(71, 'issue', 'issue_management', 'review_date', 'date', 'Review Date', 40, -1, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(72, 'issue', 'issue_creation', 'post_author', 'select', 'Created By', 10, 0, 0, '', 0, 1, 0, '', 0, 0, 'author', '', 'get_post_author_options', '', ''),
(73, 'issue', 'issue_creation', 'post_date', 'date', 'Created Date', 20, 0, 10, '', 0, 1, 0, '', 0, 0, 'date', '', '', '', ''),
(74, 'issue', 'issue_creation', 'post_status', 'select', 'Visibility', 30, 60, 0, '', 0, 1, 0, '', 0, 0, 'post_status', '', 'post_status_options', '', 'post_status_formatter'),
(75, 'issue', 'issue_creation', 'ID', 'text', 'Post ID', 40, 1, 0, '', 0, 1, 0, '', 0, 0, 'p', '', '', '', ''),
(81, 'search_log', 'search_log', 'id', 'text', 'ID', 10, -1, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(82, 'search_log', 'search_log', 'user_id', 'text', 'User ID', 15, 10, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'user_id_formatter'),
(83, 'search_log', 'search_log', 'time', 'text', 'SearchTime', 20, 20, 10, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'time_formatter'),
(84, 'search_log', 'search_log', 'entity', 'text', 'Entity', 30, 30, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(86, 'search_log', 'search_log', 'serialized_search_array', 'text', 'Search Details', 40, 40, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'serialized_search_array_formatter'),
(87, 'search_log', 'search_log', 'download_time', 'text', 'Last Download', 50, 50, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', 'download_time_formatter'),
(88, 'issue', 'search_parms', 'category_search_mode', 'radio', 'Category Search Mode', 10, 0, 0, '', 0, 0, 0, 'cat', 0, 1, '', '', 'category_search_mode_options', '', ''),
(89, 'issue', 'search_parms', 'retrieve_limit', 'select', '# of Issues to Show', 20, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', 'retrieve_limit_options', '', ''),
(90, 'constituent', 'save_options', 'no_dupcheck', 'checked', 'Suppress Dup Checking', 10, 0, 0, '', 0, 0, 0, '', 0, 1, '', '', '', '', ''),
(91, 'trend', 'trend', 'activity_date', 'date', 'Trend Period', 10, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', '', '', ''),
(92, 'trend', 'trend', 'activity_type', 'select', 'Activity Type', 20, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'activity_type_options', '', ''),
(93, 'trend', 'trend', 'last_updated_by', 'select', 'Activity Last Updated By', 30, 0, 0, '', 0, 0, 0, '', 0, 0, '', '', 'activity_last_updated_by', '', '');

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

--
-- Dumping data for table `wp_wic_form_field_groups`
--

INSERT INTO `wp_wic_form_field_groups` (`group_id`, `entity_slug`, `group_slug`, `group_label`, `group_legend`, `group_order`, `initial_open`, `sidebar_location`) VALUES
(1, 'constituent', 'contact', 'Contact', '', 10, 1, 0),
(5, 'constituent', 'case', 'Case Management', '', 30, 1, 1),
(6, 'constituent', 'personal', 'Personal Info', '', 40, 0, 1),
(7, 'constituent', 'registration', 'Codes', 'These fields are read only -- searchable, but not updateable.', 50, 0, 1),
(10, 'activity', 'activity_note', 'Activity Note', '', 20, 0, 0),
(11, 'address', 'address_line_1', 'Address Line 1', '', 10, 1, 0),
(12, 'address', 'address_line_2', 'Address Line 2', '', 20, 0, 0),
(13, 'email', 'email_row', 'Email Row', '', 10, 1, 0),
(14, 'phone', 'phone_row', 'Phone Row', '', 10, 1, 0),
(15, 'constituent', 'search_parms', 'Search Options', '\n\n', 25, 1, 1),
(16, 'activity', 'activity', '', '', 10, 0, 0),
(17, 'activity', 'activity_issue', '', '', 15, 0, 0),
(18, 'issue', 'issue_content', 'Issue Content', '', 10, 1, 0),
(19, 'issue', 'issue_classification', 'Classification', '', 20, 1, 0),
(20, 'issue', 'issue_management', 'Issue Management', '', 30, 1, 1),
(21, 'issue', 'issue_creation', 'Codes', 'These fields are not updateable except through the regular Wordpress admin screens.', 40, 1, 1),
(22, 'constituent', 'comment', 'Latest Online Comments', 'Note: If the online user''s email is not in WP-Issues-CRM, the online activity will not be shown here.  Online activity shown here can only be altered through the WP backend.  ', 20, 0, 0),
(23, 'comment', 'comment', 'Online Comments', '', 10, 0, 0),
(24, 'issue', 'search_parms', 'Search Options', 'You can select options for the categories search. Note: Tags are always joined by OR. Conditions collectively are always joined by ''AND''.', 25, 10, 1),
(25, 'constituent', 'save_options', 'Save Options', '', 27, 1, 1),
(26, 'issue', 'activity_open', 'Activity Tracking', 'Set issue as open for assignment of activities to make the issue appear on the issue drop down for activities.', 25, 1, 1),
(27, 'trend', 'trend', 'Activity Trends', '', 10, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `wp_wic_option_values`
--

CREATE TABLE IF NOT EXISTS `wp_wic_option_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_group` varchar(50) NOT NULL,
  `option_value` varchar(50) NOT NULL,
  `option_label` varchar(200) NOT NULL,
  `value_order` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`,`option_group`,`value_order`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=67 ;

--
-- Dumping data for table `wp_wic_option_values`
--

INSERT INTO `wp_wic_option_values` (`id`, `option_group`, `option_value`, `option_label`, `value_order`, `enabled`) VALUES
(1, 'case_status_options', '', '', 30, 1),
(2, 'case_status_options', '0', 'Closed', 10, 1),
(3, 'case_status_options', '1', 'Open', 20, 1),
(4, 'gender_options', '', '', 30, 1),
(5, 'gender_options', 'm', 'Male', 10, 1),
(6, 'gender_options', 'f', 'Female', 20, 1),
(7, 'party_options', '', '', 90, 1),
(8, 'party_options', 'd', 'Democrat', 10, 1),
(9, 'party_options', 'r', 'Republican', 20, 1),
(10, 'party_options', 'u', 'Unenrolled', 30, 1),
(11, 'party_options', 'l', 'Libertarian', 40, 1),
(12, 'party_options', 'j', 'Green-Rainbow', 50, 1),
(13, 'party_options', 'g', 'Green-Party USA', 60, 1),
(14, 'party_options', 's', 'Socialist', 70, 1),
(15, 'party_options', 'o', 'Other', 80, 1),
(16, 'retrieve_limit_options', '50', 'Up to 50', 0, 1),
(17, 'retrieve_limit_options', '100', 'Up to 100', 10, 1),
(18, 'retrieve_limit_options', '500', 'Up to 500', 20, 1),
(19, 'match_level_options', '1', 'Right wild card', 0, 1),
(20, 'match_level_options', '2', 'Soundex', 10, 1),
(21, 'match_level_options', '0', 'Strict', 20, 1),
(22, 'voter_status_options', '', '', 40, 1),
(23, 'voter_status_options', 'a', 'Active', 10, 1),
(24, 'voter_status_options', 'i', 'Inactive', 20, 1),
(25, 'voter_status_options', 'x', 'Not Registered', 30, 1),
(26, 'pro_con_options', '', 'Pro/Con?', 30, 1),
(27, 'pro_con_options', '0', 'Pro', 10, 1),
(28, 'pro_con_options', '1', 'Con', 20, 1),
(29, 'activity_type_options', '', 'Type?', 80, 1),
(30, 'activity_type_options', '0', 'eMail', 10, 1),
(31, 'activity_type_options', '1', 'Call', 20, 1),
(32, 'activity_type_options', '2', 'Petition', 30, 1),
(33, 'activity_type_options', '3', 'Meeting', 40, 1),
(34, 'activity_type_options', '4', 'Letter', 50, 1),
(35, 'activity_type_options', '5', 'Web Contact', 60, 1),
(36, 'activity_type_options', '6', 'Petition', 70, 1),
(37, 'address_type_options', '', 'Type?', 50, 1),
(38, 'address_type_options', '0', 'Home', 10, 1),
(39, 'address_type_options', '1', 'Work', 20, 1),
(40, 'address_type_options', '2', 'Mail', 30, 1),
(41, 'address_type_options', '3', 'Other', 40, 1),
(42, 'state_options', '', 'State?', 20, 1),
(43, 'state_options', 'MA', 'MA', 10, 1),
(44, 'email_type_options', '', 'Type?', 40, 1),
(45, 'email_type_options', '0', 'Personal', 10, 1),
(46, 'email_type_options', '1', 'Work', 20, 1),
(47, 'email_type_options', '2', 'Other', 30, 1),
(48, 'category_search_mode_options', 'cat', 'Post must have ANY of selected categories and child categories will be included.', 0, 1),
(49, 'category_search_mode_options', 'category__in', 'Post must have ANY of selected categories and child categories will NOT be included.', 10, 1),
(50, 'category_search_mode_options', 'category__and', 'Post must have ALL selected categories.', 20, 1),
(51, 'category_search_mode_options', 'category__not_in', 'Post must have NONE of selected categories.', 30, 1),
(52, 'follow_up_status_options', '', '', 30, 1),
(53, 'follow_up_status_options', 'closed', 'Closed', 10, 1),
(54, 'follow_up_status_options', 'open', 'Open', 20, 1),
(55, 'post_status_options', '', '', 30, 1),
(56, 'post_status_options', 'publish', 'Public', 10, 1),
(57, 'post_status_options', 'private', 'Private', 20, 1),
(61, 'phone_type_options', '0', 'Home', 5, 1),
(62, 'phone_type_options', '1', 'Cell', 10, 1),
(63, 'phone_type_options', '2', 'Work', 20, 1),
(64, 'phone_type_options', '3', 'Fax', 30, 1),
(65, 'phone_type_options', '4', 'Other', 40, 1),
(66, 'phone_type_options', '', 'Type?', 50, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
