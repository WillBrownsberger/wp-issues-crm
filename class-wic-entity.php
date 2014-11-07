<?php
/*
*
* class-wic-entity.php
*
* base class for wic tables/entities
*
*
* 
*/

abstract class WIC_Entity {
	
	protected $entity		= ''; // e.g., constituent, activity, issue
	protected $fields = array(); // will be initialized as field_slug => type from wp_wic_data_dictionary
	protected $data_array = array(); 		// will be initialized as field_slug => value from $fields and type classes (may make some values arrays and some strings, but will not add slugs) 
		
	abstract protected function set_entity_parms (); // must be included to set entity

	/*
	*
	* constructor just initializes minimal blank structure and passes control to named action requested
	* 
	* note that the current class is an abstract parent class WIC_Entity
	* 	-- entity is chosen in the wp-issues-crm which initializes the corresponding child class  -- e.g. WIC_Constituent
	*
	*/
	public function __construct ( $action_requested, $args ) {
		$this->set_entity_parms();
		$this->$action_requested( $args );
	}

	/*
	*
	* initialize_data_array gets all entity field slugs with blank values (string or array according to field type)
	*
	*/
	protected function initialize_data_array() { 
		foreach ( $this->fields as $field ) {
			$class_name = 'WIC_' . $field->field_type . '_Control';
			$this->data_array[$field->field_slug] = $class_name::get_initial_value();
		}		
	}

	/*
	*
	* get_values_from_submitted_form just copies values into working array ( or takes initialized values if not set )
	*
	*/
	protected function initialize_data_array_from_submitted_form() { 		
		foreach ( $this->fields as $field ) {  	
			$class_name = 'WIC_' . $field->field_type . '_Control';		
			$this->data_array[$field->field_slug] = isset ( $_POST[$field->field_slug] ) ? $_POST[$field->field_slug] : $class_name::get_initial_value();	
		} 
	}	
	
	/*
	*
	* get_values_from_submitted_form just copies values into working array ( or takes initialized values if not set )
	*
	*/
	protected function initialize_data_array_from_found_record( &$wic_query) { 		
		foreach ( $this->fields as $field ) {  	
			$class_name = 'WIC_' . $field->field_type . '_Control';		
			$this->data_array[$field->field_slug] = $wic_query->result[0]->{$field->field_slug};	
		} 
	}	
	
		
	/*
	*
	* form_save-- maintained as separate function from save per se, so that class can later be used with other AJAX/JSON which may not submit full form
	*
	*/
	protected function form_save ( $args ) {	
		$this->get_values_from_submitted_form();
	}





/***************THE LOGIC FLOW TO REPLACE *********************
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


	protected function save() {
		initialize_fields_post();
		$wic_query = $wpdb->get_results( prepare_search_sql ('dup') );
		$this->error_messages = $this->missing_fields . $this->error_messages;
		if ( 0 == $wpdb->num_rows || $this->error_messages > '' ) { // putting error condition here puts form error checking ahead of dup checking 
			if ( $this->error_messages > '' ) { 
				$this->guidance	=	__( 'Please correct form errors: ', 'wp-issues-crm' );
				$this->next_action 	=	'save';
			} else {
				$success = $wpdb->insert( $table, prepare_save_update_array() );
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
		} else {
			$this->guidance = '';
			$this->search_notices	=	sprintf ( __( 'Record not saved -- other records match the new combination of %s. View matches below.', 'wp-issues-crm' ), $this->create_dup_check_fields_list());
			$this->next_action 	=	'search';
			$show_list = true;
		}						
		
	}

	protected function update() {
		initialize_fields_post();
		$wic_query = $wpdb->get_results( prepare_search_sql ('dup') );
		$this->error_messages = $this->missing_fields . $this->error_messages;
		// next form action will be update iff any of three possibilities  . . .
			// submitted non-dup dupcheck values (OK to do update) OR							
		if ( 0 == $wpdb->num_rows || 
			// submitted dupcheck values not changed (OK to do update) OR					
			( 1 == $wpdb->num_rows && $wic_query[0]->ID == $this->fields['ID']->value ) ||
			// there are form errors (must correct and resubmit update)  
			$this->error_messages > '' ) { 
			// next action is update after an update whether or not successful (unless poss dup)						
			$this->next_action 	=	'update'; 
			if ( $this->error_messages > '' ) { 
				$this->guidance	=	__( 'Please correct form errors: ', 'wp-issues-crm' );	
			} else {
				$success = '';//FIX LATER;$wpdb->insert( $table, prepare_save_update_array(), array ( 'ID' = $this->fields['ID']->value ) ); 
				if ( ! $success )  { 
					$this->guidance = __( 'Please retry -- there were database errors. ', 'wp-issues-crm' );
					$this->error_messages = __( 'Unknown database error in save/update.', 'wp-issues-crm' );
				} else { 
					$this->guidance = __( 'Update successful -- you can further update this record.', 'wp-issues-crm' );								
				/* fix this	if ( trim( $next_form_output[ 'wic_post_content' ] ) > '' ) { // update to database
						$this->old_wic_post_content'] = $wic_form_utilities->format_wic_post_content( $this->wic_post_content'] ) . $this->old_wic_post_content'];
						$this->wic_post_content'] = ''; */
				}					
			}
		// error if form values match a record other than the original record	
		} else { 
			$this->guidance = '';						
			$this->fields['ID']->value = 0; // reset so search does not bring back the original record
			$this->search_notices	=	sprintf ( __( 'Record not updated -- other records match the new combination of %s. View matches below.', 'wp-issues-crm' ), $this->create_dup_check_fields_list());
			$this->next_action 	=	'search';
			$show_list = true;
		}						

	}

	protected function populate_fields( $query_result ) {
		foreach ( $this->fields as $field ) {
			$field->set_value ( $query_result->$field['name'] );		
		}
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

