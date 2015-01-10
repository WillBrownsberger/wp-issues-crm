<?php
/**
 * Plugin Name: WP Issues CRM
 * Plugin URI: 
 * Description: Constituent Relationship Management for organizations that respond to constituents primarily on issues (e.g., legislators); does support basic case management as well. 
 * Version: 1.0
 * Author: Will Brownsberger
 
 * License: GPL2
 *
 *  Copyright 2014  WILL BROWNSBERGER  (email : willbrownsberger@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
*
* This file initializes the system.
*
**/

// this field is populated by the first call to WIC_DB_Dictionary::get_field_rules
$wp_issues_crm_field_rules_cache = array();

// class autoloader is case insensitive, except that requires WIC_ (sic) as a prefix.
function wp_issues_crm_autoloader( $class ) {
	if ( 'WIC_' == substr ($class, 0, 4 ) ) {
		$subdirectory = 'php'. DIRECTORY_SEPARATOR . strtolower( substr( $class, 4, ( strpos ( $class, '_', 4  ) - 4 )  ) ) . DIRECTORY_SEPARATOR ;
		$class = strtolower( str_replace( '_', '-', $class ) );
		$class_file = plugin_dir_path( __FILE__ ) . $subdirectory .  'class-' . str_replace ( '_', '-', $class ) . '.php';
		if ( file_exists ( $class_file ) ) {  
   		require_once $class_file;
   	} else {
	   	wic_generate_call_trace();
	   	echo 
			die ( '<h3>' . sprintf(  __( 'Fatal configuration error -- missing file %s; failed in autoload in wp-issues-crm.php, line 43.', 'wp_issues_crm' ), $class_file ) . '</h3>' );   
	   } 
	}	
}
spl_autoload_register('wp_issues_crm_autoloader', false, true);

// add metabox to post edit screens to set issues as open for activity assignment
if ( is_admin() ) { 
	$wic_issue_open_metabox = new WIC_Entity_Issue_Open_Metabox;
}

// load css for plugin 
function wp_issue_crm_setup_styles() {

	wp_register_style(
		'wp-issues-crm-styles',
		plugins_url( 'css' . DIRECTORY_SEPARATOR . 'wp-issues-crm.css' , __FILE__ )
		);
	wp_enqueue_style('wp-issues-crm-styles');

}
add_action( 'wp_enqueue_scripts', 'wp_issue_crm_setup_styles');

// load javascript utilities for plugin 
function wic_utilities_script_setup() {
	if ( ! is_admin() ) {
		wp_register_script(
			'wic-utilities',
			plugins_url( 'js' . DIRECTORY_SEPARATOR . 'wic-utilities.js' , __FILE__ ) 
		);
		
	wp_enqueue_script('wic-utilities');
	}
}
add_action('wp_enqueue_scripts', 'wic_utilities_script_setup');

// add action to intercept press of download button before any headers sent 
function do_download () {
	if ( isset( $_POST['wic-post-export-button'] ) ) {
		WIC_List_Constituent_Export::do_constituent_download( $_POST['wic-post-export-button'] );	
	}
}
add_action( 'template_redirect', 'do_download' );

// add action to allow caching of non-save pages to facilitate back navigation in IE 
// while still forcing reload for new record save screens to avoid duping
function wic_change_cache_header() {
	if ( isset ( $_POST['wic_form_button'] ) ) {
		$control_array = explode( ',', $_POST['wic_form_button'] );
		$revalidate_always = array (
			'new_constituent',
			'save_from_search',
			'new_issue',
			'save_from_search_request',
		);  
		if ( in_array( $control_array[1], $revalidate_always ) ) {
			header( "Cache-Control: no-cache, no-store, must-revalidate" );	
			// http://blogs.msdn.com/b/ie/archive/2010/07/14/caching-improvements-in-internet-explorer-9.aspx
			// http://www.mobify.com/blog/beginners-guide-to-http-cache-headers/
			// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.3
			// http://blog.httpwatch.com/2008/10/15/two-important-differences-between-firefox-and-ie-caching/
		} else {
			header( "Cache-Control: private, max-age=0" );			
		}
	}
}
add_action( 'send_headers', 'wic_change_cache_header' );
// https://core.trac.wordpress.org/browser/tags/4.1/src//wp-includes/class-wp.php#L0 (line 448)
// fires after other headers sent, so can override previously sent Cache-Control header

// invoke principal class that displays and handles main buttons 
$wp_issues_crm = new WIC_Dashboard_Main;

// function for catching bad class definitions; 
// here only to support work of people adding new entity classes
function wic_generate_call_trace() { // from http://php.net/manual/en/function.debug-backtrace.php

	$e = new Exception();
	$trace = explode("\n", $e->getTraceAsString());
	// reverse array to make steps line up chronologically
	$trace = array_reverse($trace);
	array_shift($trace); // remove {main}
	array_pop($trace); // remove call to this method
	$length = count($trace);
	$result = array();
	for ($i = 0; $i < $length; $i++) {
		$result[] = ($i + 1) . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
	}
	
	echo "\t" . implode("<br/>\n\t", $result);
}

function wic_set_up_roles_and_capabilities() {
	
	 // give administrators access to the plugin 
    $role = get_role( 'administrator' );
    $role->add_cap( 'manage_wic_constituents' ); 

	// give editors access to the plugin 
   $role = get_role( 'editor' );
   $role->add_cap( 'manage_wic_constituents' ); 

	// define a role that has limited author privileges and access to the plugin
	
	// first remove the role in case the capabilities array below has been revised  	
	remove_role('wic_constituent_manager');

	// now add the role
	$result = add_role(
   	'wic_constituent_manager',
    	__( 'Constituent Manager', 'wp-issues-crm' ),
	   array(
	   		// capacities to add
			  'manage_wic_constituents' 	=> true, // grants access to plugin and all constituent functions
	        'read_private_posts' 			=> true, // necessary for viewing (and so editing) individual private issues through wic interface
	        'upload_files'					=> true,
	        // capacities explicitly (and perhaps unnecessarily) denied
	        'read'								=> false, // denies access to dashboard
           'edit_posts'  					=> false, // limits wp backend access -- can still edit private issues through the wic interface
	        'edit_others_posts'  			=> false, // limits wp backend access -- can still edit private issues through the wic interface 
	        'delete_posts'					=> false,
           'delete_published_posts' 	=> false,
	        'edit_published_posts' 		=> false,
	        'publish_posts'					=> false,
	        'read_private_pages' 			=> false,
	        'edit_private_posts' 			=> false,
	        'edit_private_pages' 			=> false, 
	    )
	);
	
}
register_activation_hook ( __FILE__, 'wic_set_up_roles_and_capabilities' );

  
function keep_private_posts_off_front_end_even_for_administrators( $query ) {
	if ( ! is_admin()  &&  $query->is_main_query() ) { 
		// note that this does not prevent this plugin or widgets from showing private posts to which logged in user has access
   	$query->set( 'post_status', array( 'publish' ) );			
	}
}

add_action( 'pre_get_posts', 'keep_private_posts_off_front_end_even_for_administrators' );