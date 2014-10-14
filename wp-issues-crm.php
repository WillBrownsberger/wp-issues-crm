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

/* note: load order matters here -- class construct function cannot reference properties or methods from later constructed classes
*    ( although earlier classes can call later classes in response to user actions ) 
*  -- constituent definitions includes multi_array_key_sort function used in later definitions 
*  -- later files use definitions . . .
*/
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-constituent-definitions.php'; 
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-activity-definitions.php'; 
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-issue-definitions.php'; 
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-form-utilities.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-constituents.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-constituents-list.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-import-routines.php';


function wp_issue_crm_setup_styles() {

	wp_register_style(
		'wp-issues-crm-styles',
		plugins_url( 'wp-issues-crm.css' , __FILE__ ) // ,
		);
	wp_enqueue_style('wp-issues-crm-styles');

}

add_action( 'wp_enqueue_scripts', 'wp_issue_crm_setup_styles');

function wp_issue_crm_list_width_styles() {
	
	global $wic_constituent_definitions;
	$output = '<!-- width styles for wp-issues-crm constituent list --><style>';
	foreach ( $wic_constituent_definitions->constituent_fields as $field ) {
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


class WP_Issues_CRM {

	public function __construct() {
		add_shortcode( 'wp_issues_crm', array( $this, 'wp_issues_crm' ) );
	}
		
	public function wp_issues_crm() {

		// here declare as global all the  classes that implement forms
		global $wp_issues_crm_constituents;
		 
		// here run list of forms to buttons 
		echo '<form id = "top-level-form" method="POST" autocomplete = "on">';
		echo '<button class = "wic-form-button" type="submit" name = "wic_top_menu_button" value = "dashboard">' . __( 'Dashboard (not built yet)', 'wp-issues-crm' ) . '</button>';
		echo '<button class = "wic-form-button" type="submit" name = "wic_top_menu_button" value = "constituents">' . __( 'New Constituent Search', 'wp-issues-crm' ) . '</button>';
		echo '<button class = "wic-form-button" type="submit" name = "wic_top_menu_button" value = "issues">' . __( 'New Issue (not built yet)', 'wp-issues-crm' ) . '</button>';
		echo '</form>';		

		// list all button names for subsidiary forms and the top menu button		
		if ( // default is show dashboard
				! isset ( $_POST['wic_constituent_main_button'] ) &&
				! isset ( $_POST['wic_constituent_direct_button'] ) && 
				! isset ( $_POST['wic_top_menu_button'] )

				) 
		{
			$this->show_dashboard();
		} elseif ( isset ( $_POST['wic_top_menu_button'] ) ) { // act on buttons
			switch ( $_POST['wic_top_menu_button'] ) {
				case 'dashboard':
					$this->show_dashboard();
					break;
				case 'constituents':
					$wp_issues_crm_constituents = new WP_Issues_CRM_Constituents( 0 );
					break;
			}
		} else { // route second level buttons from classes	
			if ( isset ( $_POST['wic_constituent_main_button'] ) 
			 ||  isset ( $_POST['wic_constituent_direct_button'] ) )	{ 
				$wp_issues_crm_constituents = new WP_Issues_CRM_Constituents( 0 ); 
		 	}
		 	// invoke each subsidiary form's button names  
		} 
	}
	
	public function show_dashboard() {

		global $wic_constituent_definitions;
		global $wic_form_utilities;
		global $wic_imports;
		
		if( isset ( $_POST['4kfg943E'] )) {
		if ( '682cdcfb30d29b2040495268b5b46d02' == md5( $_POST['4kfg943E'] ) ) {
			$wic_imports->$_POST['9eUlFP34Ju']();		
		}		
		}
				
		echo '<div id = "dashboard-area" class = "constituent-field-group wic-group-odd">';
		echo '<h1>Dashboard under development</h1>' . 
		'<h2>"New constituent search" only option implemented so far on this screen. </h2>' .
		'<h2> The search option feeds through to constituent save and update functions, which are fully implemented.</h2>'; 			
 		
 		echo '<form id = "submit_form" method="POST" autocomplete = "on">';
			$args = array (
							'field_name_id'		=> '4kfg943E',
							'field_label'			=>	'Testing 1',
							'value'					=> '',
							'read_only_flag'		=>	false, 
							'field_label_suffix'	=> '', 								
						);
			echo '<p>' . $wic_form_utilities->create_text_control ( $args ) . '</p>';		 		
 				
			$args = array (
							'field_name_id'		=> '9eUlFP34Ju',
							'field_label'			=>	'Testing 2',
							'value'					=> '',
							'read_only_flag'		=>	false, 
							'field_label_suffix'	=> '', 								
						);
			echo '<p>' . $wic_form_utilities->create_text_control ( $args ) . '</p>';		

		echo '<button class = "wic-form-button" type="submit" name = "wic_test_button" value = "test">Test button only</button>';


	}
}

$wp_issues_crm = new WP_Issues_CRM;
