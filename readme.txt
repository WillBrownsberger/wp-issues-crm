
php file naming conventions:
	one file per class
	prefix class-wp-issues-crm-class-name.php
	class-name will WP_Issues_CRM_Class_Name
	php subdirectory is first segment of class name -- e.g. form in WP_Issues_CRM_Form_Constituent_Search
		for forms, name the entity, then the action
		
		
=== WP Issues CRM ===
Contributors: Will Brownsberger
Donate link: 
Tags: contact, crm, constituent, customer, issues, list
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 0.80
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CRM for offices that use Wordpress as their central communication tool, but have constituencies broader than their Wordpress user universe.

== Description ==

WP Issues CRM is a constituent/customer relationship management database designed for organizations that use Wordpress
to organize their content, but have constituencies broader than their Wordpress user universe.  It uses the post and category structure of 
Wordpress to define and classify issues, but uses custom tables for high performance access to a larger constituent database.  It offers
easy downloading for outgoing mail or email communications.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go through the Settings page under WP Issues CRM and make basic configuration decisions
4. Consider your office use of information and add any necessary constituent fields under Fields
5. If you are adding some "Select" fields -- some typology like political party -- define the options under Options.
6. If you have existing data that you are importing, you will need some help from someone who understands databases to run
	necessary upload queries.  Version 1.0 will include an automatic upload function. 


== Frequently Asked Questions ==

Where I can I get support?

Use the Wordpress forums.  If necessary contact the author at WillBrownsberger@gmail.com or by text at 617-771-8274.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==



	  	
