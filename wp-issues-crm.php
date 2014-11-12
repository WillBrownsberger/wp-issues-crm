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
// include plugin_dir_path( __FILE__ ) . 'class-wic-table.php';
/* new files 
include plugin_dir_path( __FILE__ ) . 'class-wic-data-dictionary.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-db-access.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-entity.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-form.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-form-constituent-search.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-form-constituent-update.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-control.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-constituent.php';
include plugin_dir_path( __FILE__ ) . 'class-wic-list.php';
/* old files
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
*/

function wp_issues_crm_autoloader( $class ) {
	if ( 'WIC_' == substr ($class, 0, 4 ) ) {
		$subdirectory = 'php'. DIRECTORY_SEPARATOR . strtolower( substr( $class, 4, ( strpos ( $class, '_', 4  ) - 4 )  ) ) . DIRECTORY_SEPARATOR ;
		$class = strtolower( str_replace( '_', '-', $class ) );
	   require_once plugin_dir_path( __FILE__ ) . $subdirectory .  'class-' . str_replace ( '_', '-', $class ) . '.php'; 
	}	
}

spl_autoload_register('wp_issues_crm_autoloader', false, true);

function wp_issue_crm_setup_styles() {

	wp_register_style(
		'wp-issues-crm-styles',
		plugins_url( 'css' . DIRECTORY_SEPARATOR . 'wp-issues-crm.css' , __FILE__ )
		);
	wp_enqueue_style('wp-issues-crm-styles');

}

add_action( 'wp_enqueue_scripts', 'wp_issue_crm_setup_styles');



function wic_utilities_script_setup() {
	if ( !is_admin() ) {
		wp_register_script(
			'wic-utilities',
			plugins_url( 'js' . DIRECTORY_SEPARATOR . 'wic-utilities.js' , __FILE__ ) 
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
		var_dump($_POST);
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
		$control_array['action_requested'] = 'new_form';
		$button_value = implode ( ',' , $control_array );		
		echo '<button class = "wic-form-button" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . __( 'New Constituent Search', 'wp-issues-crm' ) . '</button>';
	
		$control_array['form_requested'] = 'issue';
		$control_array['action_requested'] = 'new';
		$button_value = implode ( ',' , $control_array );		
		echo '<button class = "wic-form-button" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . __( 'New Issue Search', 'wp-issues-crm' ) . '</button>';

		echo '</form>';		

		/* 
		* This is the central request handler for the entire plugin.
		* It distributes button submissions (all of which have the same name, with an array of values) 
		*   to an class entity class with an action request and arguments.
		*/
		if ( isset ( $_POST['wic_form_button'] ) ) {
			 $control_array = explode( ',', $_POST['wic_form_button'] ); 
			if ( '' == $control_array[0] ) {
				$this->show_dashboard();		
			} else {
				$class_name = 'WIC_Entity_' . initial_cap ( $control_array[0] ); // entity_requested
				$action_requested 		= $control_array[1];
				$args = array (
					'id_requested'			=>	$control_array[2],
					'referring_parent' 	=> $control_array[3],
					'new_form'				=> $control_array[4],
					'instance'				=> '', // unnecessary in this context, absence will not create an error but here for consistency about arguments;
				);
				
				${ 'wic_entity_'. $control_array[0]} = new $class_name ( $action_requested, $args ) ;		
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
//			echo '<p>' . $wic_form_utilities->create_text_control ( $args ) . '</p>';		 		
 				
			$args = array (
							'field_name_id'		=> '9eUlFP34Ju',
							'field_label'			=>	'Testing 2',
							'value'					=> '',
							'read_only_flag'		=>	false, 
							'field_label_suffix'	=> '', 								
						);
//			echo '<p>' . $wic_form_utilities->create_text_control ( $args ) . '</p>';		

	echo '<button class = "wic-form-button" type="submit" name = "wic_test_button" value = "test">Test button only</button>';


	}
	
	public function show_open_issues_for_user ( $user, $case_type ) {
		echo 'need to rebuild open issues!';
		/*
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
		}*/

	}

}

$wp_issues_crm = new WP_Issues_CRM;


function initial_cap ( $string ) {
	$string[0] = strtoupper ( $string[0] );
	return ( $string ); 
}

function wic_generic_sanitizor ( $value ) {
		return sanitize_text_field ( stripslashes ( $value ) );	
}

	
function validate_individual_email( $email ) { 
	$error = filter_var( $email, FILTER_VALIDATE_EMAIL ) ? '' : __( 'Email address appears to be not valid. ', 'wp-issues-crm' );
	return $error;	
}	

function foobar ( $value ) {
		echo $value . '???? Fucked up beyond all recognition!';	
}