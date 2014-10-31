<?php
/*
*
* class-wic-table.php
*
* base class for wic tables/entities
*
*
* 
*/

abstract class WIC_Table {
	
	// misc parameters
	public $labels = array (
		'singular' => '', 
		'plural'	  => ''	
	);

	public $sort_order = array (
		'orderby' => '', // field_slug
		'order'	  => '' // ASC or DSC
	);	
	
	public $max_records = 100;

	/*
	* main definitions array
	* see class-wic-fields for associative array keys in individual field definition arrays */
	*/
	public $field_definitions = array( 
		array( // 1
			'dedup'	=>	true,	
			/*  . . . */
		),		
		/* . . . */
	);

	protected $field_groups = array ( // for form display
		array (
			'name'		=> '', 
			'label'		=>	'',  
			'legend'		=>	'', // fine print below group header in form
			'order'		=>	0, // numeric 
			'initial-open'	=> true, // open state on first form display
		),
	);
	
	/* will hold array of field objects after __construct */
	protected $fields = array();

	/* will hold exceptions from __construct */
	protected $error_messages = '';
	protected $missing_fields = '';
	protected $search_notices = '';
	protected $guidance					=  'Enter just a little information and do a full text search.'; // note 4a
	protected $search_notices			=	'';					// note 4c
	protected $next_action				=	'search';			// note 5
	protected $strict_match				=	false;				// note 6
	protected $initial_form_state		= 	'wic-form-open';  // note 7 
	protected $initial_sections_open = array();			// note 8


	protected function __construct ( $action_requested ) {

		/* sort fields for form presentation */
		$this->field_definitions = multi_array_key_sort ( $this->field_definitions, 'order' );
		$this->initialize_from_post();
		$this->$action_requested;

	}

	protected function initialize_from_post() {
		/* for each defined field, instantiate a field object (sanitize and validate post input) */		
		$group_required_test = '';
		$group_required_label = '';		
		foreach ( $this->field_definitions as $args ) {
			$class_name = 'WIC_' .  $args['type'] . '_Field';
			${$args['name']} = new $class_name ( $args );
			$this->fields[] = ${$args['name']};  
			$this->error_messages .= ${$args['name']}->validation_errors;	
			if ( '' = ${$args['name']}->present && "individual" == ${$args['name']}->required )
				$this->missing_fields .= ' ' . sprintf( __( ' %s is a required field. ' , 'wp-issues-crm' ), ${$args['name']}->label );
			}
			if  ( "group" == ${$args['name']}->required ) {
 				$group_required .= ${$args['name']}->present;
 				$group_required_label .= ( '' == $group_required_label ) ? '' : ', ';	
 				$group_required_label .= ${$args['name']}->label;
			}
		if ( '' == $group_required_test && $group_required_label > '' ) {
			$this->missing_fields .= sprintf ( __( ' At least one among %s is required. ', 'wp-issues-crm' ), $group_required_label );
   	}
	}

/***************THE LOGIC FLOW TO REPLACE *********************/
				if ( 0 == $this->id_requested ) { 
					// clean and validate POST input and populate next form output	
					$wic_form_utilities->sanitize_validate_input( $next_form_output, $this->working_post_fields );
					// do search in all submitted cases, but do only on dup check fields if request is a save or update (does not alter next_form_output)
					$search_mode = ( 'search' == $this->action_requested ) ? 'new' : 'dup';
				} else { 
					$search_mode = 'db_check';
					$next_form_output['wic_post_id']	= $this->id_requested;	
				}
				$wic_query = $wic_database_utilities->search_wic_posts( $search_mode, $next_form_output, $this->working_post_fields, $this->form_requested ); 
	
				// do last form requests and define form_notices and next_action based on results of sanitize_validate, search_wic_posts and save/update requests  
				switch ( $this->action_requested ) {	

					case 'update':
						// in this branch after a dup_check search on an updated record -- next action will be update iff any of three possibilities  . . .
							// updated to non-dup dupcheck values (OK to do update) OR							
						if ( 0 == $wic_query->found_posts || 
							// updated but dupcheck values not changed (OK to do update) OR					
							( 1 == $wic_query->found_posts && $wic_query->posts[0]->ID == $next_form_output['wic_post_id'] ) ||
							// there are form errors (must correct and resubmit update)  
							$next_form_output['error_messages'] > '' ) { 
							// always proceed to further update after an update whether or not successful (unless poss dup)						
							$next_form_output['next_action'] 	=	'update'; 
							if ( $next_form_output['error_messages'] > '' ) { 
								$next_form_output['guidance']	=	__( 'Please correct form errors: ', 'wp-issues-crm' );	
							} else {
								$outcome = $wic_database_utilities->save_update_wic_post( $next_form_output, $this->working_post_fields, $this->form_requested );
								if ( $outcome['notices'] > '' )  { 
									$next_form_output['guidance'] = __( 'Please retry -- there were database errors. ', 'wp-issues-crm' );
									$next_form_output['error_messages'] = $outcome['notices'];
								} else { 
									$next_form_output['guidance'] = __( 'Update successful -- you can further update this record.', 'wp-issues-crm' );								
									if ( trim( $next_form_output[ 'wic_post_content' ] ) > '' ) { // update to database
										$next_form_output['old_wic_post_content'] = $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] ) . $next_form_output['old_wic_post_content'];
										$next_form_output['wic_post_content'] = '';
									}
								}					
							}
						// error if form values match a record other than the original record	
						} else { 
							$next_form_output['guidance'] = '';						
							$next_form_output['wic_post_id'] = 0; // reset so search does not bring back the original record
							$next_form_output['search_notices']	=	sprintf ( __( 'Record not updated -- other records match the new combination of %s. View matches below.', 'wp-issues-crm' ), $this->create_dup_check_fields_list());
							$next_form_output['next_action'] 	=	'search';
							$show_list = true;
						}						
						break;				

				} // closes switch statement	
			} // closes handling of cases other than simple referring parent case
			 
			// prepare to show list of posts if found more than one
			if ( $show_list ) {
				$wic_list_posts = new WP_Issues_CRM_Posts_List ( $wic_query, $this->working_post_fields, $this->form_requested, 0, true );			
				$post_list = $wic_list_posts->post_list;
				if ( 'search' == $this->action_requested  && '' == $next_form_output['search_notices'] ) // always show form unless was a search and no search notices
					$next_form_output['initial_form_state'] = 'wic-form-closed';
			} else {
				$post_list = '';			
			}

			// prepare to show list of posts if found exactly one
			$children_list_output = '';
			if ( $next_form_output['wic_post_id'] > 0 && count( $this->child_types ) > 0 ) { 
				$children_lists = $wic_database_utilities->get_children_lists ( $next_form_output['wic_post_id'], $this->form_requested, $this->child_types );
				foreach ( $children_lists as $child_list ) { 
					$wic_list_posts = new WP_Issues_CRM_Posts_List ( $child_list['list_query'], $child_list['fields_array'], $child_list['child_type'], $next_form_output['wic_post_id'],  false );	
					$children_list_output = $wic_list_posts->post_list;		
				}			
			}

			// done with queries
			wp_reset_postdata();
			wp_reset_query();

 		} // close if not just a new query
 		
 		// deliver the results ( if new form)
 		ob_start();
 		$this->display_form( $next_form_output );
 		if ( isset ( $post_list ) ) {
			echo $post_list; 		
 		}
 		if ( isset ( $children_list_output ) ) {
 			echo $children_list_output;	
 		}
 		ob_end_flush();

   } // close function
	
/**************THE LOGIC FLOW TO REPLACE IS ABOVE *********************************************/


	/* the major actions that can be requested of the object -- search, save, update */
	
	protected function new () {
		
	
	}	
	
	protected function search() {
		initialize_from_post();
		$wic_query = $wpdb->get_results( prepare_search_sql ('new') );
		if ( 0 == $wpdb->num_rows; ) {
			$this->guidance	=	__( 'No matching record found. Try a save? ', 'wp-issues-crm' );
			$this->next_action 	=	'save';
		} elseif ( 1 == $wpdb->num_rows; ) {
			// overwrite form with that unique record's  values
			$wic_database_utilities->populate_form_from_database ( $next_form_output, $this->working_post_fields, $wic_query, $this->form_requested );
			$this->guidance	=	__( 'One matching record found. Try an update?', 'wp-issues-crm' );
			$this->next_action 	=	'update';
		} else {
			$this->guidance	=	__( 'Multiple records found (results below). ', 'wp-issues-crm' );
			$this->next_action 	=	'search';
			$show_list = true;
		}						
	} 
	
	protected function save() {
		initialize_fields_post();
		$wic_query = $wpdb->get_results( prepare_search_sql ('dup') );
		$this->error_messages = $this->missing_fields . $this->error_messages;
		if ( 0 == $wpdb->num_rows || $this->error_messages > '' ) { // putting error condition here puts form error checking ahead of dup checking 
			if ( $this->error_messages > '' ) { 
				$this->guidance	=	__( 'Please correct form errors: ', 'wp-issues-crm' );
				$this->next_action 	=	'save';
			} else {
				$success = $wpdb->insert( $table, prepare_save_update_array) ('save') )
				if ( ! $success ) { 
					$this->guidance	=	__( 'Please retry -- there were database errors: ', 'wp-issues-crm' );
					$this->error_messages = __( 'Unknown database error in save/update.', 'wp-issues-crm' );
					$this->next_action 	=	'save';
				} else {
					$this->ID['value'] = $wpdb->insert_id;	
					$this->guidance	=	__( 'Record saved -- you can further update this record.', 'wp-issues-crm' );
					$this->next_action 	=	'update';
						/* fix this	if ( trim( $next_form_output[ 'wic_post_content' ] )  > '' ) { // parallels update to database
						$this->old_wic_post_content = $wic_form_utilities->format_wic_post_content( $this->wic_post_content ) . $this->old_wic_post_content;
						$this->wic_post_content = ''; */
					}
				}					
			}
		} else {
			$this->guidance = '';
			$this->search_notices	=	sprintf ( __( 'Record not saved -- other records match the new combination of %s. View matches below.', 'wp-issues-crm' ), $this->create_dup_check_fields_list());
			$this->next_action 	=	'search';
			$show_list = true;
		}						
		
	}

	protected function update() {
		initialize_fields_post()
	}


	/* supporting functions -- display form */	
	protected function display_form() {
	}

	protected function prepare_search_sql( $mode ) {
	
		$join = '';
		$where = '';
		$values = array();
		
		foreach ( $this->fields as $field ) {
			if ( ( 'dup' == $mode && $field->dedup ) || 'new' == $mode )  {
				$search_clauses = $field->search_clauses();
				$join .= $search_clauses['join'];
				$where .= $search_clauses['where'];
				// each field will return an array of several values that need to be strung into main values array
				foreach ( $search_clauses['values'] as $value ) { 
					$values[] = $value;			
				}
			} 		
		}
		
		$sql = $wpdb->prepare( "
					SELECT 	* 
					FROM 		$table
					$join
					WHERE 1=1 $where
					ORDER BY $this->sort_order['orderby'] $this->sort_order['order']
					LIMIT 0, $this->max_records
					",
				$values );	
			
		return ( $sql );
	
	}

	protected function prepare_save_update_array() {
				
		$save_update_array = array();		
		
		foreach ( $this->fields as $field ) {
			$field_data_array = $field->data_array();
			// each field will return an array of several values that need to be strung into main values array
			foreach ( $field_data_array as $datum ) { 
				$save_update_array[] = $datum;			
			} 		
		}
		
		return ( $save_update_array );
	
	}




}