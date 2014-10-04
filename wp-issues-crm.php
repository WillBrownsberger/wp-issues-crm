<?php
/**
 * Plugin Name: WP Issues CRM
 * Plugin URI: 
 * Description: Constituent Relationship Management for organizations that respond to constituents primarily on issues (e.g., legislators); does support basic case notes as well. 
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

include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-definitions.php'; // note that order of load may matter here -- want definitions constructed first to sort field arrays once for all
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-constituents.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-constituents-list.php';


function wp_issue_crm_setup_styles() {

	wp_register_style(
		'wp-issues-crm-styles',
		plugins_url( 'wp-issues-crm.css' , __FILE__ ) // ,
		);
	wp_enqueue_style('wp-issues-crm-styles');

}

add_action( 'wp_enqueue_scripts', 'wp_issue_crm_setup_styles');

function wp_issue_crm_list_width_styles() {
	
	global $wic_definitions;
	$output = '<!-- width styles for wp-issues-crm constituent list --><style>';
	foreach ( $wic_definitions->constituent_fields as $field ) {
		if ( $field['list'] > 0 ) { 		
 			$output .= '.cl-' . $field['slug'] . '{ width:' . $field['list'] . '%;}'; 
 		}
	}
	$output .= '</style>';
	echo $output;
}
add_action( 'wp_head', 'wp_issue_crm_list_width_styles' );


function wic_utilities_script_setup() {
	if ( !is_admin() ) {
		wp_register_script(
			'wic-utilities',
			plugins_url( 'wic-utilities.js' , __FILE__ ) 
		);
		
	wp_enqueue_script('wic-utilities');
	}
}
add_action('wp_enqueue_scripts', 'wic_utilities_script_setup');
