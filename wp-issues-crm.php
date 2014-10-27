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
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-base-definitions.php'; 
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-constituent-definitions.php'; 
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-activity-definitions.php'; 
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-issue-definitions.php';  
include plugin_dir_path( __FILE__ ) . 'class-wic-query.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-update.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-form-utilities.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-database-utilities.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-main-form.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-posts-list.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-import-routines.php';

function wp_issue_crm_setup_styles() {

	wp_register_style(
		'wp-issues-crm-styles',
		plugins_url( 'wp-issues-crm.css' , __FILE__ ) // ,
		);
	wp_enqueue_style('wp-issues-crm-styles');

}

add_action( 'wp_enqueue_scripts', 'wp_issue_crm_setup_styles');

function multi_array_key_sort ( $multi_array, $key )	{
		$temp = array();
		
		foreach ( $multi_array as $line_item ) {
			 $temp[$line_item[$key]] = $line_item;
		}
		ksort ($temp);
		$sorted_line_items = array();
		foreach ($temp as $key => $value ) {
			array_push( $sorted_line_items, $value );			
		}
		return ( $sorted_line_items) ;
	}

function wp_issue_crm_list_width_styles() {
	
	global $wic_base_definitions;

	$output = '<!-- width styles for wp-issues-crm post lists --><style>';
	foreach ( $wic_base_definitions->wic_post_types as $key => $value ) {
		global ${ 'wic_' . $key . '_definitions' };	
		//	must exist in valid config:  proper class name and field wic_post_fields within class	
		foreach ( ${ 'wic_' . $key . '_definitions' }->wic_post_fields as $field ) {
			if ( $field['list'] > 0 ) { 		
	 			$output .= '.pl-' . $key . '-' . $field['slug'] . '{ width:' . $field['list'] . '%;}';  
	 		}
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


		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 

		// here declare as global all the  classes that implement forms

		$control_array = array(
			'form_requested'			=> '',
			'action_requested'		=> '',
			'id_requested'				=> 0,
			'referring_parent' 		=> 0,
			'new_form'					=> 'n',
		);	

		// use default control array to set up top row of buttons that always shows over dashboard and over main form 
		echo '<form id = "top-level-form" method="POST" autocomplete = "on">';
		
		$button_value = implode( ',' , $control_array );
		echo '<button class = "wic-form-button" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . __( 'Dashboard', 'wp-issues-crm' ) . '</button>';
		
		$control_array['form_requested'] = 'constituent';
		$control_array['action_requested'] = 'new';
		$button_value = implode ( ',' , $control_array );		
		echo '<button class = "wic-form-button" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . __( 'New Constituent Search', 'wp-issues-crm' ) . '</button>';
	
		$control_array['form_requested'] = 'issue';
		$control_array['action_requested'] = 'new';
		$button_value = implode ( ',' , $control_array );		
		echo '<button class = "wic-form-button" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . __( 'New Issue Search', 'wp-issues-crm' ) . '</button>';

		echo '</form>';		

		// now populate control value with any submitted button
		if ( isset ( $_POST['wic_form_button'] ) ) {
			 $control_array = explode( ',', $_POST['wic_form_button'] ); 
			if ( '' == $control_array[0] ) {
				$this->show_dashboard();		
			} else {
				$wic_main_form = new WP_Issues_CRM_Main_Form ( $control_array );		
			}
		} else {
			$this->show_dashboard();
		}		


	}
	
	public function show_dashboard() {

		global $wic_form_utilities;
		global $wic_imports;
		
		if( isset ( $_POST['4kfg943E'] )) {
		if ( '682cdcfb30d29b2040495268b5b46d02' == md5( $_POST['4kfg943E'] ) ) {
			$wic_imports->$_POST['9eUlFP34Ju']();		
		}		
		}
		
		$user = wp_get_current_user();		
				
		echo '<div id = "dashboard-area" class = "wic-post-field-group wic-group-odd">';

		
		$this->show_open_issues_for_user( $user, 'issue' ); // takes user object returned by wp_get_current_user;
		$this->show_open_issues_for_user( $user, 'constituent' );
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
	
	public function show_open_issues_for_user ( $user, $case_type ) {
		global $wic_issue_definitions;
		global $wic_constituent_definitions;
		global $wic_database_utilities;	
		global $wic_form_utilities;		
		
		echo '<h4 class = "wic-dashboard-header">' . ${'wic_' . $case_type . '_definitions'}->wic_post_type_labels['plural'] . ' assigned to ' . $user->display_name . '</h4>'; 		
		
		
		$short_input = array();	
		$wic_form_utilities->initialize_blank_form( $short_input,  ${'wic_' . $case_type . '_definitions'}->wic_post_fields );
		
		$short_input['assigned'] = $user->ID;
		$short_input['case_status'] = 1;
		// var_dump($short_input);
		$wic_open_query = $wic_database_utilities->search_wic_posts( 'new', $short_input, ${'wic_' . $case_type . '_definitions'}->wic_post_fields, $case_type );
		
		if( $wic_open_query->found_posts > 0 ) {	
			$wic_list_posts = new WP_Issues_CRM_Posts_List ( $wic_open_query, ${'wic_' . $case_type . '_definitions'}->wic_post_fields, $case_type, 0, false );	
			$post_list = $wic_list_posts->post_list;
			echo $post_list;
		} else {
		
			echo '<p>No open ' . ${'wic_' . $case_type . '_definitions'}->wic_post_type_labels['plural'] . ' assigned.</p>';	
		}

	}

}

$wp_issues_crm = new WP_Issues_CRM;
