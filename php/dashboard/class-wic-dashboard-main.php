<?php
/**
*
* class-wic-dashboard-main.php
*
*/


class WIC_Dashboard_Main {

	public function __construct() {
		add_shortcode( 'wp_issues_crm', array( $this, 'wp_issues_crm' ) );
	}
		
	public function wp_issues_crm() {

		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 

		echo '<form id = "top-level-form" method="POST" autocomplete = "on">';
		wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ); 

		$top_menu_buttons = array (
			array ( 'dashboard', 	'my_cases',		0, 	__( 'My Cases', 'wp-issues-crm' ) ),
			array ( 'dashboard', 	'my_issues',	0, 	__( 'My Issues', 'wp-issues-crm' ) ),
			array ( 'constituent', 	'new_form',		0, 	__( 'Constituents', 'wp-issues-crm' ) ),
			array ( 'issue', 			'new_form',		0, 	__( 'Issues', 'wp-issues-crm' ) ),
			array ( 'issue', 			'new_issue',	0, 	__( 'New Issue', 'wp-issues-crm') ),
			array ( 'dashboard', 	'search_history',0,	__( 'History', 'wp-issues-crm' ) ),		
			); 
		
		foreach ( $top_menu_buttons as $top_menu_button ) {
			$button_value = $top_menu_button[0] . ',' . $top_menu_button[1] . ',' . $top_menu_button[2];
			echo '<button class = "wic-form-button" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . __( $top_menu_button[3], 'wp-issues-crm' ) . '</button>';
		}				
		echo '</form>';		

		/* 
		* This is the central request handler for the entire plugin.
		* It distributes button submissions (all of which have the same name, with an array of values) 
		*   to an class entity class with an action request and arguments.
		*/
		if ( isset ( $_POST['wic_form_button'] ) ) {
			// check nonces			
			
			if ( isset($_POST['wp_issues_crm_post_form_nonce_field']) &&
				wp_verify_nonce($_POST['wp_issues_crm_post_form_nonce_field'], 'wp_issues_crm_post' ) && 
				check_admin_referer( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field')) 
				{ } else { die ('cheating, huh?'); }
			
			//
			 $control_array = explode( ',', $_POST['wic_form_button'] ); 
			if ( '' == $control_array[0] ) {
				$this->show_dashboard();		
			} else {
				$class_name = 'WIC_Entity_' . $control_array[0]; // entity_requested
				$action_requested 		= $control_array[1];
				$args = array (
					'id_requested'			=>	$control_array[2],
					'instance'				=> '', // unnecessary in this context, absence will not create an error but here for consistency about arguments;
				);
				
				${ 'wic_entity_'. $control_array[0]} = new $class_name ( $action_requested, $args ) ;		
			}
		} else {
			$this->show_dashboard();
		}		


	}
	
	public function show_dashboard() {
		$current_user = wp_get_current_user();
		echo '<h3 id = "dashboard-welcome-message">' . sprintf( __( 'New WP-Issues-CRM Session for %1$s, on %2$s.', 'wp-issues-crm' ), $current_user->display_name, current_time( 'Y-m-d H-i-s' ) ) . '</h3>';
	
		echo '<div id = "dashboard-area" class = "wic-post-field-group wic-group-odd">.</div>';

	}
	


}

