<?php
/**
*
* class-wic-admin-dashboard.php
*/


class WIC_Admin_Dashboard {

	/* 
	*  This is the central request handler for the working screens of the plugin -- all requests are button submits named wic_form_button.
	*	WIC_Admin_Navigation just handles the page selection from the wordpress menu and checks nonces and user capabilities.
	*
	*  The constructor of this function, when instantiated by WIC_Admin_Navigation, distributes button submissions 
	*    (all of which have the same name, with a string of values) to an entity class with an action request and arguments.
	*
	*	See WIC_Form_Parent::create_wic_form_button for button interface (exclusive main form button creator for system)
	*
	*  Only other entry points ( other than fields/options/settings ) is at WIC_List_Constituent_Export 
	*	 same security tests as in Navigation are done there -- is logged in and, other than for dashboard first screen (my cases) have nonce
	*
	*/
	
	public function __construct() { 

		// is submitting a previous form; 
		if ( isset ( $_POST['wic_form_button'] ) ) {
				
			//parse button arguments
			$control_array = explode( ',', $_POST['wic_form_button'] ); 
			if ( '' == $control_array[0] || 'dashboard' == $control_array[0] ) { 
				// $control_array[0] should never be empty if button is set, but bullet proof to dashboard, which is also bullet proof
				$this->show_dashboard( $control_array [1] );		
			} else {
				$class_name = 'WIC_Entity_' . $control_array[0]; // entity_requested
				$action_requested 		= $control_array[1]; 
				$args = array (
					'id_requested'			=>	$control_array[2],
					'instance'				=> '', // unnecessary in this context, absence will not create an error but here for consistency about arguments;
				);
				$this->show_top_menu_buttons ( $control_array[0], $control_array[1], $control_array[2] );
				${ 'wic_entity_'. $control_array[0]} = new $class_name ( $action_requested, $args ) ;		
			}
			
		// logged in user, but not coming from form -- show first form
		} else {
			$this->show_dashboard( WIC_DB_Access_WP_User::get_wic_user_preference( 'first_form' ) );
		}		
	}

	private function show_top_menu_buttons ( $class_requested, $action_requested, $id_requested ) {  

		global $wp_issues_crm_enable_backward;
		global $wp_issues_crm_enable_forward; 

		echo '<form id = "wic-top-level-form" method="POST" autocomplete = "on">';
		wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ); 

		$user_id = get_current_user_id();

		$top_menu_buttons = array (
			// go to constituent options
			array ( 'search_log', 	'back',	'<span class="dashicons dashicons-arrow-left-alt"></span>' , __( 'Previous search or item', 'wp-issues-crm' ), 0 ==  $wp_issues_crm_enable_backward ),
			array ( 'search_log', 	'forward', '<span class="dashicons dashicons-arrow-right-alt"></span>', __( 'Next search or item', 'wp-issues-crm' ), 0 ==  $wp_issues_crm_enable_forward  ),
			array ( 'constituent', 	'new_blank_form',	'<span class="dashicons dashicons-plus-alt"></span><span class="dashicons dashicons-smiley">' , __( 'New constituent.', 'wp-issues-crm' ), false ), // new
			array ( 'constituent', 	'new_form',		'<span class="dashicons dashicons-search"></span><span class="dashicons dashicons-smiley"></span>', __( 'Search constituents.', 'wp-issues-crm' ), false ), // search
			array ( 'dashboard', 	'my_cases',	 '<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-smiley"></span>', __( 'Constituents assigned to me.', 'wp-issues-crm' ), false  ),
			// array ( 'constituent', 	'get_latest',	'<span class="dashicons dashicons-smiley"></span><span class="dashicons dashicons-arrow-left-alt"></span>', __( 'Last constituent.', 'wp-issues-crm' ), false ), // new
			// go to issue options
			array ( 'issue', 			'new_blank_form',	'<span class="dashicons dashicons-plus-alt"></span><span class="dashicons dashicons-format-aside"></span>', __( 'New issue.', 'wp-issues-crm' ), false ),
			array ( 'issue', 			'new_form',		'<span class="dashicons dashicons-search"></span><span class="dashicons dashicons-format-aside"></span>', __( 'Search for issues.', 'wp-issues-crm' ), false ),
			array ( 'dashboard', 	'my_issues',	'<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-format-aside"></span>', __( 'Issues assigned to me.', 'wp-issues-crm' ), false ),
			// array ( 'issue', 			'get_latest',	'<span class="dashicons dashicons-format-aside"></span><span class="dashicons dashicons-arrow-left-alt"></span>', __( 'Last issue.', 'wp-issues-crm' ), false ),
			// analyze/download			
			array ( 'trend', 			'new_form',		'<span class="dashicons dashicons-chart-line"></span>', __( 'Get activity/issue counts.', 'wp-issues-crm' ), false ), 
			// go to search history
			array ( 'dashboard', 	'search_history',	'<span class="dashicons dashicons-arrow-left-alt"></span><span class="dashicons dashicons-arrow-left-alt"></span>', __( 'Recent searches.', 'wp-issues-crm' ), false ),		
			);		


	
		foreach ( $top_menu_buttons as $top_menu_button ) {
			$selected_class = $this->is_selected ( $class_requested, $action_requested, $top_menu_button[0], $top_menu_button[1] ) ? 'wic-form-button-selected' : '';
			$button_class = 'button button-primary wic-top-menu-button ' . $selected_class;	
			$button_class .= ( 'back' == $top_menu_button[1] || 'forward' == $top_menu_button[1] ) ? ' wic-nav-button ' : '' ; 		
			$button_args = array (
				'entity_requested'	=> $top_menu_button[0],
				'action_requested'	=> $top_menu_button[1],
				// 'id_requested'			=> 'get_latest' == $top_menu_button[1] ? $user_id : 0, // not actually used in the methods responsive to these buttons, except in the get_latest methods
				'button_class'			=> $button_class, 	
				'button_label'			=>	$top_menu_button[2],
				'title'					=>	$top_menu_button[3],
				'disabled'				=>	$top_menu_button[4],
			);
			echo WIC_Form_Parent::create_wic_form_button( $button_args );
		}				
		echo '</form>';		
	}

	// for semantic highlight of top buttons (note, in this function referring to class as in entity, not as in css class )
	private function is_selected ( $class_requested, $action_requested, $button_class, $button_action ) {
		// if last pressed the button, show it as selected 
		if ( $class_requested == $button_class && $action_requested == $button_action ) {
			return true; 
		} else { 
			return false;
		}
	}
	/**************************************************************************************************
	*
	* Dashboard display and dashboard action functions	
	*
	***************************************************************************************************/	
	
	// show the top menu buttons and call the action requested for the dashboard	
	private function show_dashboard( $action_requested ) {
		
		$this->show_top_menu_buttons ( 'dashboard', $action_requested, NULL );
	
		$user_ID = get_current_user_id();	
		
		// bullet proofed to always yield an action, but $action_requested should always be specified
		// exception: user has not specified a first screen preference, defaults to my cases
		if ( 'my_issues' == $action_requested || 'search_history' == $action_requested || 'my_cases' == $action_requested ) {
			$this->$action_requested( $user_ID );
		} else {
			$this->my_cases ( $user_ID );		
		}
			
	}	
	
	// display a list of cases assigned to user
	private function my_cases( $user_ID ) {
		
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( 'constituent' );

		$search_parameters= array(
			'sort_order' => true,
			'compute_total' 	=> false,
			'retrieve_limit' 	=> 9999999999,// kludge here:  this retrieve limit is a sentinel to the lister not to show the back button
			'show_deleted' 	=> false,
			'select_mode'		=> 'id',
			'log_search'		=> false,
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
			$list = $lister->format_entity_list( $wic_query, __( 'My Cases: ', 'wp-issues-crm' ) );
			echo $list;			
		}
	}
		
	// display a list of issues assigned to user	
	private function my_issues( $user_ID ) {
		
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( 'issue' );

		$search_parameters= array(
			'sort_order' => true,
			'compute_total' 	=> false,
			'retrieve_limit' 	=> 9999999999, // kludge here:  this retrieve limit is a sentinel to the lister not to show the back button
			'show_deleted' 	=> false,
			'select_mode'		=> 'id',
			'log_search'		=> false,
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
			$list = $lister->format_entity_list( $wic_query,  __( 'My Issues: ', 'wp-issues-crm' ) );
			echo $list;			
		}
	}

	// display user's search log ( which includes form searches, items selected from lists and also items saved )
	private function search_history( $user_ID ) {
	
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( 'search_log' );

		$search_parameters= array(
			'sort_order' => true,
			'compute_total' 	=> false,
			'retrieve_limit' 	=> 50,
			'show_deleted' 	=> true,
			'select_mode'		=> 'id',
			'sort_direction'	=> 'DESC',
			'log_search'		=> false,
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
			$list = $lister->format_entity_list( $wic_query, '' );
			echo $list;			
		}
	}
	
	/**************************************************************************************************
	*
	*	Maintenance of session navigation cookie -- sends a cookie that carries string of pointers back to the search log
	*	Walk back through search log; act like browser back/forward, but avoiding caching issues and possile double submission of data
	*	Items in the search log are constituents/issues viewed (including saved/updated) and searches.  
	*
	*  Invoked in WIC_Admin_Setup through admin_init hook so is called before any content sent by wordpress
	*  placed here for ease of maintenance with in conjunction with main navigation buttons
	*
	***************************************************************************************************/
	public static function maintain_log_cookie () { // set these non-persistent cookies even if not visiting wp_issues_crm;
	
		global $wp_issues_crm_enable_backward;
		global $wp_issues_crm_enable_forward;	
		
		// define and initialize variables that will be set into a cookie array
		$user							= get_current_user_id();
		$log							= array( '0' );	// previous visited search log entries as a stack (string, while actually in the cookie)-- latest visited = last
		$log_pointer				= -1; // will be incremented when user does a loggable action
		$possible_new_log_entry	= 1;	// 1 says new step, outside chain -- on next receive, must roll array; 
 		$last_log_entry			= 0;	// retain this when slicing array, so that don't add it back on next pass through			

		// if cookie is set, update cookie values as necessary
		if ( isset ( $_COOKIE['wp_issues_crm_log'] ) ) {
			if ( $user == $_COOKIE['wp_issues_crm_log']['user'] ) { // just make sure that logged in user from this browser has not changed
				// extract other cookie components (physically, separate cookies)
				$log							=  explode ( ',', $_COOKIE['wp_issues_crm_log']['log'] );
				$log_pointer				= 	$_COOKIE['wp_issues_crm_log']['log_pointer'];
				$possible_new_log_entry =  $_COOKIE['wp_issues_crm_log']['possible_new_log_entry'];
				$last_log_entry			=  $_COOKIE['wp_issues_crm_log']['last_log_entry'];
				// first, figure out whether last form generated loggable event that needs to be added -- this catch-up:
				// the "last form" was processed in the same submit that generated the cookie being evaluated, but after it was generated 
				// can't update cookie after processing form submission, because wordpress has already sent headers when wp_issues_crm is processing form
				// so, have to look back to previous form submission through the database search log in order to to maintain the cookie  
				if ( 1 == $possible_new_log_entry ) {  
					// get actual latest log entry  
					$latest_log_entry = WIC_DB_Access::search_log_last_entry( get_current_user_id() );
					if ( $latest_log_entry ) { // if search log has any entries for this user, false is a boundary condition 
						// if latest log entry is not already in the cookie, put it in (may be from the prior session)
						if ( -1 == $log_pointer ) { // first log entry for this cookie, initialize array, another boundary condition
							$log = array ( $latest_log_entry );
							$log_pointer++;
						} elseif ( $last_log_entry != $latest_log_entry && $latest_log_entry != $log[$log_pointer] ) {  
							// normal processing -- compare cookie log last entry prior to slice to latest db log entry and also avoid dups
							$log[] = $latest_log_entry; // push new new log entry to end of log array
							$log_pointer++; // increment the log pointer
						} // doing nothing if no database search_log entries 
					}	
				}
				// now look at latest action requested and set pointer (possibly truncating log array) and set possible new entry flag
				if ( isset (  $_POST['wic_form_button'] ) ) {			
					$control_array = explode( ',', $_POST['wic_form_button'] );
					// if back or forward, move pointer 
					if ( 'search_log' == $control_array[0] && ( 'back' == $control_array[1] || 'forward' == $control_array[1]  ) ) {
						if ( 'back' == $control_array[1] ) {
							if ( $log_pointer > 0 ) {
								$log_pointer--; // decrement log pointer					
							}				
						} elseif ( 'forward' == $control_array[1]  )  {
							if ( $log_pointer < ( count ( $log ) - 1 ) ) {
								$log_pointer++; // decrement log pointer					
							}					
						}
						// lower new log_entry_flag here, because either (a) I am just continuing a walk up/down the log
						// or (b), I digressed from the log to a non-loggable action, but have returned to the log with a back action (log has been truncated)			
						$possible_new_log_entry = 0; 
								
					} elseif ( 'search_log' == $control_array[0] && 'id_search' == $control_array[1] ) {
						// a search log id search is essentially a pointer move combined with a new log entry
						$slice_length = ( -1 == $log_pointer ) ? 1 : $log_pointer + 1; 
						$log = array_slice ( $log, 0, $slice_length ); // do a slice because branching to a new location in the log 
						$log[] = $control_array[2]; // add the search log entry to the cookie log
						$log_pointer++; // increment the pointer
						$possible_new_log_entry = 0; // the new log entry is already done, so don't set flag to do catch up
					} else { // its something other than a pointer move, new flag should be set to 1 since maybe going do a new loggable event
						$possible_new_log_entry = 1;
						// drop array entries forward of current pointer, because am walking in a new direction
						$last_log_entry = $log [ count ( $log ) - 1 ]; // last log entry (subtract 1 because count starts from 1, indexing from zero) 
						$slice_length = ( -1 == $log_pointer ) ? 1 : $log_pointer + 1; 
						$log = array_slice ( $log, 0, $slice_length ); // if slice length = count, slice is identity, as in initial position or extension of the log 
					}
				}
				// limit length of cookie 
				if ( count ( $log ) > 20 ) { 
					$discard = array_shift ( $log );
					$log_pointer = count ( $log ) - 1; // should equal limit - 1; 
				} 
			} 
		}

		// having updated log for previous form submission and set pointer and flag from current form submission, set the cookie 
		$log_string = implode( ',', $log ); 
		setcookie ( 'wp_issues_crm_log[user]', 						$user, 						0, '/wp-admin/', '', false, true );
		setcookie ( 'wp_issues_crm_log[log]', 							$log_string, 				0, '/wp-admin/', '', false, true );
		setcookie ( 'wp_issues_crm_log[log_pointer]', 				$log_pointer, 				0, '/wp-admin/', '', false, true );		
		setcookie ( 'wp_issues_crm_log[possible_new_log_entry]', $possible_new_log_entry,0, '/wp-admin/', '', false, true );
		setcookie ( 'wp_issues_crm_log[last_log_entry]', 			$last_log_entry, 			0, '/wp-admin/', '', false, true );
		
		
		// back and forward buttons will be responding to the cookie just sent, because
		// 	in the form, they are blind to the cookie content -- see this->show_top_menu_buttons
		// 	so by the time they are acted on,we have the cookie back --
		//		wic_entity_search_log->back actually handles the back request looking at this cookie
		// need global variables to tell buttons whether to setup as enabled	--
		// 	this is a static function so not available when get around to instantiating this class as an object	
		$wp_issues_crm_enable_backward = ( $log_pointer > 0 );
		$wp_issues_crm_enable_forward =  $log_pointer <  count ( $log ) - 1 && $log_pointer > - 1  ;	
	
	}

}

