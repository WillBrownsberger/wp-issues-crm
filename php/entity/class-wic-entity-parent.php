<?php
/*
*
* class-wic-entity-parent.php
*
* base class for wic tables/entities
*
*
* 
*/

abstract class WIC_Entity_Parent {
	
	protected $entity		= ''; 						// e.g., constituent, activity, issue
	protected $entity_instance = '';					// relevant where entity is a row of multivalue array as in emails for a constituent
	protected $fields = array(); 						// will be initialized as field_slug => type array from wp_wic_data_dictionary
	protected $data_object_array = array(); 		// will be initialized as field_slug => control object 
	protected $outcome = '';							// results of latest request 
	protected $outcome_dups = false;					// supplementary outcome information -- dups among error causes
	protected $outcome_dups_query_object;			// results of dup query for listing	
	protected $explanation	= '';						// explanation for outcome
	protected $made_changes = false;					// after a save_update, were changes made?
	
		
	abstract protected function set_entity_parms ( $args ); // must be included in child to set entity and possibly instance
	
	/*
	*
	* constructor just initializes minimal blank structure and passes control to named action requested
	* 
	* note that the current class is an abstract parent class WIC_Entity_Parent
	* 	-- entity is chosen in the wp-issues-crm which initializes the corresponding child class  -- e.g. WIC_Constituent
	*  
	* args is an associative array, which MAY be populated as follows:
	*		'id_requested'			=>	$control_array[2] passed by wp-issues-crm from form button for an ID search
	*		'instance'				=> passed in the case of the object being initialized as a row in multi-value field:	
	*		'id_array'				=> array of id's -- used when passed from issue form to comment entity for conversion to constituent listing
	*		'search_id'				=> pass through of issue search log id that will be used to reconstruct constituent search 
	*
	*/
	public function __construct ( $action_requested, $args ) {
		$this->set_entity_parms( $args );
		$this->$action_requested( $args );
	}

	/*************************************************************************************
	*
	*  METHODS FOR SETTING UP AND POPULATING THE DATA_OBJECT_ARRAY
	*
	**************************************************************************************
	*
	* The major entities retain their logical properties in a single data_object_array of control objects
	*	Some of these controls are multivalue controls, which in turn are arrays of entity objects each with their own array of controls
	*	Have to handle the multivalue controls as arrays.
	*
	* To up the entity object (this sequence is built in to each populate function): 
	*  (1) get the entity fields/properties from the data dictionary ( calling $wic_db_dictionary->get_form_fields)
	*  (2) initialize the data object array by instantiating controls for each (each dictionary control type having a corresponding control class )
	*  (3) populating the control objects
	*
	*/
	protected function initialize_data_object_array()  {
		// get fields for the entity
		global $wic_db_dictionary;
		$this->fields = $wic_db_dictionary->get_form_fields( $this->entity );
		// initialize_data_object_array as field_slug => control object 
		foreach ( $this->fields as $field ) { 
			$this->data_object_array[$field->field_slug] = WIC_Control_Factory::make_a_control( $field->field_type );
			$this->data_object_array[$field->field_slug]->initialize_default_values(  $this->entity, $field->field_slug, $this->entity_instance );
		}		
	}

	protected function populate_data_object_array_from_submitted_form() {

		$this->initialize_data_object_array();

		foreach ( $this->fields as $field ) {  	
			if ( isset ( $_POST[$field->field_slug] ) ) {		
				$this->data_object_array[$field->field_slug]->set_value( $_POST[$field->field_slug] );
			}	
		} 
	}	

	protected function populate_data_object_array_from_found_record( &$wic_query, $offset=0 ) {

		$this->initialize_data_object_array();

		foreach ( $this->data_object_array as $field_slug => $control ) { 
			if ( ! $control->is_multivalue() && ! $control->is_transient()  ) { 
				$control->set_value ( $wic_query->result[$offset]->{$field_slug} );
			} elseif ( $control->is_multivalue() ) { // for multivalue fields, set_value wants array of row arrays -- 
						// query results don't have that form or even an appropriate field slug, 
						// so have to search by parent ID  
				$control->set_value_by_parent_pointer( $wic_query->result[$offset]->ID );
			}
		} 
	}	

	protected function populate_data_object_array_with_search_parameters ( $search ) {
	
		$this->initialize_data_object_array();
		// reformat $search_parameters array
		$key_value_array = array();		
		foreach ( $search['unserialized_search_array'] as $search_array ) {
			if ( $search_array['table'] == $this->entity ) {
				$key_value_array[$search_array['key']] = $search_array['value'];
			} else {
				// spoof an incoming form array for a multivalue control	
				// (to the top entity multivalue control looks like any other, but its set value function needs an array)			
				if ( ! isset ( $key_value_array[$search_array['table']] ) ) {
					$key_value_array[$search_array['table']] = array();
					$key_value_array[$search_array['table']][0] = array();				
				}
				$key_value_array[$search_array['table']][0][$search_array['key']] = $search_array['value'];		
			}
		}

		$combined_form_values = array_merge ( $key_value_array, $search['unserialized_search_parameters']);

		// pass data object array and see if have values
		foreach ( $this->data_object_array as $field_slug => $control ) { 
			if ( isset ( $combined_form_values[$field_slug] ) ) {
					$control->set_value ( $combined_form_values[$field_slug] );
			}
		} 
	}
	
	// initialize data object array for an id, but don't display a form
	protected function initialize_only ( $args ) {
		$this->id_search_generic ( $args['id_requested'], '', '' );	
	}

	/*************************************************************************************
	*
	*  METHODS FOR SANITIZING VALIDATING THE DATA_OBJECT_ARRAY
	*     Results stored in object properties -- outcome, outcome_dups, explanation
	*
	**************************************************************************************/
	private function update_ready( $save ) { // true is save, false is update
		// runs all four sanitize/validate functions
		$this->sanitize_values();
		$this->validate_values();
		$this->required_check();
		// do dup check last -- required fields are part of dup check
		// outcome starts as empty string, set to false if validation or required errors
		// check that dup checking not overriden
		if ( '' === $this->outcome && ! isset ( $_POST['no_dupcheck'] ) ) {		
			$this->dup_check ( $save );
		}
	}
	
	public function sanitize_values() {
		// have each control sanitize itself
		foreach ( $this->data_object_array as $field => $control ) {
			$control->sanitize();
		}
	}
	
	// check for dups -- $save is true/false for save/update
	protected function dup_check ( $save ) {
		global $wic_db_dictionary;
		$dup_check = false;
		// first check whether any fields are set for dup checking
		foreach ( $this->data_object_array as $field_slug => $control ) {
			if	( $control->dup_check() ) {
				$dup_check = true;
			}	
		}
		
		// if there are some dup check fields defined, proceed to do do dupcheck	
		if ( $dup_check ) {
			$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
			$search_parameters = array(
				'select_mode' => 'id',
				'show_deleted' => false,		
			);
			$search_clause_args = array(
				'match_level' => '0',
				'dup_check' => true,
				'category_search_mode' => '',
			);
			// assembling meta_query with strict match and dedup requested
			$wic_query->search ( $this->assemble_meta_query_array( $search_clause_args ), $search_parameters );  // true indicates a dedup search
			if ( $wic_query->found_count > 1 || ( ( 1 == $wic_query->found_count ) && 
						( $wic_query->result[0]->ID != $this->data_object_array['ID']->get_value() ) )
						// for update, 1 group OK iff same record
					|| ( $save && $wic_query->found_count > 0 ) ) {
						// for save, dups are never OK
				$this->outcome = false;
				$dup_check_string = $wic_db_dictionary->get_dup_check_string ( $this->entity );
				$this->explanation .= sprintf ( __( 'Other records found with same combination of %s.' , 'wp-issues-crm' ), $dup_check_string );
				$this->outcome_dups = true;
				$this->outcome_dups_query_object = $wic_query;		
			}
		}		 
	}

	public function validate_values() {
		// have each control validate itself and report
		$validation_errors = '';		
		foreach ( $this->data_object_array as $field => $control ) {
			$validation_errors .= $control->validate();
		}
		if ( $validation_errors > '' ) {
			$this->outcome = false;		
			$this->explanation .= $validation_errors;
			return ( $validation_errors . sprintf( __( ' ( Message from %1$s object, instance %2$s. ) ', 'wp-issues-crm' ), 
				$this->entity, $this->entity_instance + 1 ) );		
		} else {
			return ('');		
		}
	}

	public function required_check () {
		global $wic_db_dictionary;
		// have each control see if it is present as required 
		$required_errors = '';
		$there_is_a_required_group = false;
		$a_required_group_member_is_present = false;		
		foreach ( $this->data_object_array as $field_slug => $control ) {
			$required_errors .= $control->required_check();	
			if ( $control->is_group_required() ) {
				$there_is_a_required_group = true;			
				$a_required_group_member_is_present = $control->is_present() ? true : $a_required_group_member_is_present ;
			}
		}
		// report cross-control group required result
		if ( $there_is_a_required_group && ! $a_required_group_member_is_present ) {	
			$required_errors .= sprintf ( __( ' At least one among %s is required. ', 'wp-issues-crm' ), $wic_db_dictionary->get_required_string( $this->entity, "group" ) );
		}
		if ( $required_errors > '' ) {
			$this->outcome = false;
			$this->explanation .= $required_errors;		
		}
		
		return ( $required_errors );
   }

	/*************************************************************************************
	*
	*  METHODS FOR COMPILING SEARCH REQUESTS FROM CONTROLS 
	*	This function lives in this class so that it can be called publicly by a multivalue control
	*	   which may be assembling query conditions from entities within it to contribute to a 
	*     search involving a multi-table join.  The corresponding update assembly lives in the database access
	*		layer which, although it will support multi-entity searches, only updates one entity.
	*
	**************************************************************************************/
	public function assemble_meta_query_array ( $search_clause_args ) {
		extract ( $search_clause_args, EXTR_OVERWRITE );
		$meta_query_array = array ();
		foreach ( $this->data_object_array as $field => $control ) {
			$query_clause = '';
			if ( ! $dup_check || $control->dup_check() ) { // all fields if not dupchecking, otw, only dup_check fields
				$query_clause = $control->create_search_clause( $search_clause_args );
				if ( is_array ( $query_clause ) ) { // not making array elements unless field returned a query clause
					$meta_query_array = array_merge ( $meta_query_array, $query_clause ); // will do append since the arrays of arrays are not keyed arrays 
				}
			}
		}	
		return $meta_query_array;
	}	

	/*************************************************************************************
	*
	*  REQUEST HANDLERS: new form, save from search, save/update,  id search, general search, redo search from metaquery
	*     Child class functions are wrap arounds to choose next forms
	*
	**************************************************************************************/

	protected function new_form_generic( $form, $guidance = '' ) {
		global $wic_db_dictionary;
		$this->fields = $wic_db_dictionary->get_form_fields( $this->entity );
		$this->initialize_data_object_array();
		$new_search = new $form;
		$new_search->layout_form( $this->data_object_array, $guidance, 'guidance' );
	}	
	
	protected function search_form_from_search_array ( $form, $guidance, $serialized_search_array ) {
		$this->populate_data_object_array_with_search_parameters ( $serialized_search_array ); 
		$new_search = new $form;
		$new_search->layout_form( $this->data_object_array, $guidance, 'guidance' );
	}

	// handle a save request coming from search -- 
	// need to lose readonly fields from search form, so show save form, rather than proceeding directly
	protected function save_from_search ( $entity_save_form, $message = '', $message_level = 'good_news', $sql = ''  ) {
		$this->populate_data_object_array_from_submitted_form();
		$save_form = new $entity_save_form;
		$save_form->layout_form ( $this->data_object_array, $message, $message_level, $sql );		
	}

	//handle an update request coming from a form ( $save is true or false )
	protected function form_save_update_generic ( $save, $fail_form, $success_form ) {
		$this->populate_data_object_array_from_submitted_form();
		$this->update_ready( $save ); // false, not a save 
		if ( false === $this->outcome ) {
			$message = __( 'Not successful: ', 'wp-issues-crm' ) . $this->explanation;
			$message_level = 'error';
			$form = new $fail_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level );
			if ( $this->outcome_dups ) {	
				$lister_class = 'WIC_List_' . $this->entity;
				$lister = new $lister_class;
				$list = $lister->format_entity_list( $this->outcome_dups_query_object, false );
				echo $list;
			}	
			return;
		}
		$wic_access_object = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_access_object->save_update( $this->data_object_array ); 
		if ( false === $wic_access_object->outcome ) {
			$message =  $wic_access_object->explanation;
			$message_level = 'error';
			$form = new $fail_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level );
		} else {
			if ( $save ) {
				$this->data_object_array['ID']->set_value( $wic_access_object->insert_id );
			}
			$this->made_changes = $wic_access_object->were_changes_made();
			$this->special_entity_value_hook( $wic_access_object ); // done on both save and updates, but hook may test values
			$message = __( 'Successful.  You can update further. ', 'wp-issues-crm' );
			$message_level = 'good_news';
			$form = new $success_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level );					
		}
	}
	
	// handle a search request for an ID coming from anywhere
	protected function id_search_generic ( $id, $success_form, $sql = '' ) { 
		// passing a blank success form just leaves the array instantiated, but no action taken
		// initialize data array with only the ID and do search
		$this->data_object_array['ID'] = WIC_Control_Factory::make_a_control( 'text' );
		$this->data_object_array['ID']->initialize_default_values(  $this->entity, 'ID', $this->entity_instance );	
		$this->data_object_array['ID']->set_value( $id );
		$wic_query = 	WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$search_parameters = array(
			'select_mode' => '*',
			'show_deleted' => true,		
		);
		$search_clause_args = array(
			'match_level' => '0',
			'dup_check' => false,
			'category_search_mode' => '',
			);

		// assemble metaquery with match_level = 0 (strict match) and dup check set to false
		$wic_query->search ( $this->assemble_meta_query_array( $search_clause_args ), $search_parameters ); 
		// retrieve record if found, otherwise error

		if ( 1 == $wic_query->found_count ) { 
			$message = __( '', 'wp-issues-crm' );
			$message_level =  'guidance';
			$this->populate_data_object_array_from_found_record ( $wic_query );			
			if ( $success_form > '' ) {
				$update_form = new $success_form; 
				$update_form->layout_form ( $this->data_object_array, $message, $message_level, $sql );	
				$this->list_after_form ( $wic_query );
			}
		} else {
			WIC_Function_Utilities::wic_error ( sprintf ( 'Data base corrupted for record ID: %1$s in id_search_generic.' , $id ), __FILE__, __LINE__, __METHOD__, true );		
		} 
	}
	
	// handle a search request coming from a full form
	// the first form passed will be 
	protected function form_search_generic ( $not_found_form, $found_form ) { 

		$this->populate_data_object_array_from_submitted_form();
		$this->sanitize_values();
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$search_parameters= array(
			'sort_order' 		=> isset ( $this->data_object_array['sort_order'] ) ? $this->data_object_array['sort_order']->get_value() : '',
			'compute_total' 	=> isset ( $this->data_object_array['compute_total'] ) ? $this->data_object_array['compute_total']->get_value() : '',
			'retrieve_limit' 	=> isset ( $this->data_object_array['retrieve_limit'] ) ? $this->data_object_array['retrieve_limit']->get_value() : '',
			'show_deleted' 	=> isset ( $this->data_object_array['show_deleted'] ) ? $this->data_object_array['show_deleted']->get_value() : '',
			'select_mode'		=> 'id'
			);
		$search_clause_args = array(
			'match_level' =>  isset ( $this->data_object_array['match_level'] ) ? $this->data_object_array['match_level']->get_value() : '',
			'dup_check' => false,
			'category_search_mode' => isset ( $this->data_object_array['category_search_mode'] ) ? $this->data_object_array['category_search_mode']->get_value() : '',
			);
		// note that the transient search parameter 'match_level' is needed by individual controls in create_search_clause()
				
		$wic_query->search ( $this->assemble_meta_query_array( $search_clause_args ), $search_parameters ); // get a list of id's meeting search criteria
		$this->handle_search_results ( $wic_query, $not_found_form, $found_form );
	}

	// takes action depending on outcome of search
	protected function handle_search_results ( $wic_query, $not_found_form, $found_form ) {
		$sql = $wic_query->sql;
		if ( 0 == $wic_query->found_count ) {
			$message = __( 'No matching record found -- search again, save new or start over.', 'wp-issues-crm' );
			$message_level =  'error';
			$form = new $not_found_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level, $sql );			
		} elseif ( 1 == $wic_query->found_count) { 
			$this->data_object_array = array(); // discard possibly soft matching array values before doing straight id retrieval
			$this->id_search_generic ( $wic_query->result[0]-> ID, $found_form, $sql );	
		} else {
			$lister_class = 'WIC_List_' . $this->entity ;
			$lister = new $lister_class;
			$list = $lister->format_entity_list( $wic_query,true );
			echo $list;	
		}
	}

	public function redo_search_from_meta_query ( $meta_query_array, $save_form, $update_form ) {

		global $wic_db_dictionary;


		$this->fields = $wic_db_dictionary->get_form_fields( $this->entity );
		$this->initialize_data_object_array();
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$search_parameters = array(); // use default parameters, since original unknown
		$wic_query->search ( $meta_query_array, $search_parameters ); 
		$this->handle_search_results ( $wic_query, $save_form, $update_form );
	}

	// determine the latest entity which the user has saved, updated or selected from a list
	protected function compute_latest ( $args ) {
		$user_id = $args['id_requested'];
		$wic_access_object = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$latest_update_array = $wic_access_object->updated_last ( $user_id );
		$latest_search_array = $wic_access_object->search_log_last ( $user_id );
		/*	var_dump ($latest_search_array);
			var_dump ($latest_update_array);
			die; */

		$latest = WIC_Function_Utilities::choose_latest_non_blank ( 
			$latest_update_array['latest_updated'], 
			$latest_update_array['latest_updated_time'],
			$latest_search_array['latest_searched'], 
			$latest_search_array['latest_searched_time']
			);
		return ( $latest );
	}	
	
	// display the latest entity in an update form 
	protected function get_latest ( $args ) {
		$latest = $this->compute_latest ( $args  ); 	
		$args2 = array ( 'id_requested' => $latest );	
		if ( $latest > '' ) {
			$this->id_search ( $args2 ); // id_search lives in the instantiated object and includes a form specific to the instantiated entity
				// calls id_search_generic with the class
		} else {
			$this->new_blank_form( $args2 ); // passing the empty arg		
		} 
	}
	
	// just load the latest entity
	protected function get_latest_no_form ( $args ) {
		$latest = $this->compute_latest ( $args  ); 	
		$this->id_search_generic ( $latest, '', '' ); // just retrieves the record, if no class is passed 	
	}

	// if used after calling get_latest_no_form returns latest
	protected function get_current_ID_and_title () {
		return ( array (
			 'current' 	=> $this->data_object_array['ID']->get_value(),
			 'title' 	=> $this->get_the_title(),
			)
		);
	}

	protected function special_entity_value_hook ( &$wic_access_object ) {
		// available to bring back values from save/update for entity where a value is created by the save process
		// must have correlated language in the save process -- see wic-entity-issue and wic-entity-data-dictionary
	}
	
	protected function list_after_form ( &$wic_query ) {
		// hook for use with list of constituents after issue display -- see WIC_Entity_Issue 	
	}
	
	public function were_changes_made () {
		return ( $this->made_changes );	
	}
}

