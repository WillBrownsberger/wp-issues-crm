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

// add hook to intercept press of download button before any headers sent 
function do_download () {
	if ( isset( $_POST['wic-post-export-button'] ) ) {
		WIC_List_Constituent_Export::do_constituent_download( $_POST['wic-post-export-button'] );	
	}
}
add_action( 'template_redirect', 'do_download' );

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