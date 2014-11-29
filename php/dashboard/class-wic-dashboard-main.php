<?php
/**
*
* class-wic-dashboard-main.php
*

* This is the central request handler for the entire plugin.
* It distributes button submissions (all of which have the same name, with an array of values) 
*   to an class entity class with an action request and arguments.
*
* Only other entry is at WIC_List_Constituent_Export 
*	 same security tests there -- is logged in and, other than for dashboard first screen (my cases) have nonce?
*
*/
class WIC_Dashboard_Main {

	// construct registers short code; this class instantiated in wp-issues-crm.php
	public function __construct() {
		add_shortcode( 'wp_issues_crm', array( $this, 'wp_issues_crm' ) );
	}

	// short code handler
	public function wp_issues_crm() {

		// is user logged in as administrator; if not, return
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 

		// is the logged in user purporting to submit a previous form; if so, have a nonce?
		if ( isset ( $_POST['wic_form_button'] ) ) {
			// check nonces and die if not OK			
			if ( isset($_POST['wp_issues_crm_post_form_nonce_field']) &&
				wp_verify_nonce($_POST['wp_issues_crm_post_form_nonce_field'], 'wp_issues_crm_post' ) && 
				check_admin_referer( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field')) 
				{ } else { die ('cheating, huh?'); }
				
			//parse button arguments
			$control_array = explode( ',', $_POST['wic_form_button'] ); 
			if ( '' == $control_array[0] || 'dashboard' == $control_array[0] ) {
				$this->show_dashboard( $control_array [1] );		
			} else {
				$class_name = 'WIC_Entity_' . $control_array[0]; // entity_requested
				$action_requested 		= $control_array[1];
				$args = array (
					'id_requested'			=>	$control_array[2],
					'instance'				=> '', // unnecessary in this context, absence will not create an error but here for consistency about arguments;
				);
				$this->show_top_menu_buttons ( $control_array[0], $control_array[1] );
				${ 'wic_entity_'. $control_array[0]} = new $class_name ( $action_requested, $args ) ;		
			}
			
		// logged in user, but not coming from form -- show first form
		} else {
			$this->show_dashboard( 'my_cases' );
		}		
	}

	private function show_top_menu_buttons ( $class_requested, $action_requested ) {
		echo '<form id = "top-level-form" method="POST" autocomplete = "on">';
		wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ); 

		$top_menu_buttons = array (
			array ( 'dashboard', 	'my_cases',		0, 	__( 'My Cases', 'wp-issues-crm' ) ),
			array ( 'dashboard', 	'my_issues',	0, 	__( 'My Issues', 'wp-issues-crm' ) ),
			array ( 'constituent', 	'new_form',		0, 	__( 'Constituents', 'wp-issues-crm' ) ),
			array ( 'issue', 			'new_form',		0, 	__( 'Issues', 'wp-issues-crm' ) ),
			array ( 'issue', 			'new_issue',	0, 	__( 'New Issue', 'wp-issues-crm') ),
			array ( 'dashboard', 	'search_history',0,	__( 'Recent', 'wp-issues-crm' ) ),		
			); 
		
		foreach ( $top_menu_buttons as $top_menu_button ) {
			$selected_class = ( $top_menu_button[0] == $class_requested && $top_menu_button[1] == $action_requested ) ? 'wic-form-button-selected' : '';
			$button_value = $top_menu_button[0] . ',' . $top_menu_button[1] . ',' . $top_menu_button[2];
			echo '<button class = "wic-form-button ' . $selected_class . '" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . __( $top_menu_button[3], 'wp-issues-crm' ) . '</button>';
		}				
		echo '</form>';		
	}

	
	public function show_dashboard( $action_requested ) {
		
		$this->show_top_menu_buttons ( 'dashboard', $action_requested );
		
		$user_ID = get_current_user_id();		
		
		switch ( $action_requested ) {
			
			case 'my_cases':
			
				$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( 'constituent' );

				$search_parameters= array(
					'sort_order' => true,
					'compute_total' 	=> false,
					'retrieve_limit' 	=> 999999999,
					'show_deleted' 	=> false,
					'select_mode'		=> 'id',
				);

				$search_array = array (
					array (
						 'table'	=> 'constituent',
						 'key'	=> 'case_assigned',
						 'value'	=>  $user_ID, 
						 'compare'	=> '=', 
						 'wp_query_parameter' => '',
					),
					array (
						 'table'	=> 'constituent',
						 'key'	=> 'case_status',
						 'value'	=> '0', 
						 'compare'	=> '!=', 
						 'wp_query_parameter' => '',
					), 
				);
		
				$wic_query->search ( $search_array, $search_parameters ); // get a list of id's meeting search criteria
				$sql = $wic_query->sql;
				if ( 0 == $wic_query->found_count ) {
					echo '<h3>' . __( 'No cases assigned.', 'wp-issues-crm' ) . '</h3>';		
				} else {
					$lister_class = 'WIC_List_Constituent' ;
					$lister = new $lister_class;
					$list = $lister->format_entity_list( $wic_query, false );
					echo $list;			
				}
				break;
				
			case 'my_issues':
				$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( 'issue' );

				$search_parameters= array(
					'sort_order' => true,
					'compute_total' 	=> false,
					'retrieve_limit' 	=> 999999999,
					'show_deleted' 	=> false,
					'select_mode'		=> 'id',
				);

				$search_array = array (
					array (
						 'table'	=> 'issue',
						 'key'	=> 'issue_staff',
						 'value'	=> $user_ID,
						 'compare'	=> '=', 
						 'wp_query_parameter' => '',
					),
					array (
						 'table'	=> 'issue',
						 'key'	=> 'follow_up_status',
						 'value'	=> 'open', 
						 'compare'	=> '=', 
						 'wp_query_parameter' => '',
					), 
				);
		
				$wic_query->search ( $search_array, $search_parameters ); // get a list of id's meeting search criteria
				$sql = $wic_query->sql;
				if ( 0 == $wic_query->found_count ) {
					echo '<h3>' . __( 'No issues assigned.', 'wp-issues-crm' ) . '</h3>';		
				} else {
					$lister_class = 'WIC_List_Issue' ;
					$lister = new $lister_class;
					$list = $lister->format_entity_list( $wic_query, false );
					echo $list;			
				}
				break;


			case 'search_history':
			
				$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( 'search_log' );

				$search_parameters= array(
					'sort_order' => true,
					'compute_total' 	=> false,
					'retrieve_limit' 	=> 200,
					'show_deleted' 	=> true,
					'select_mode'		=> 'id',
					'sort_direction'	=> 'DESC',
				);

				$search_array = array (
					array (
						 'table'	=> 'search_log',
						 'key'	=> 'user_id',
						 'value'	=> $user_ID,
						 'compare'	=> '=', 
						 'wp_query_parameter' => '',
					),				
				);
		
				$wic_query->search ( $search_array, $search_parameters ); // get a list of id's meeting search criteria
				$sql = $wic_query->sql;
				if ( 0 == $wic_query->found_count ) {
					echo '<h3>' . __( 'Search logs purged since last search.', 'wp-issues-crm' ) . '</h3>';		
				} else {
					$lister_class = 'WIC_List_Search_Log' ;
					$lister = new $lister_class;
					$list = $lister->format_entity_list( $wic_query, false );
					echo $list;			
				}
				break;

			}

	}
	


}

