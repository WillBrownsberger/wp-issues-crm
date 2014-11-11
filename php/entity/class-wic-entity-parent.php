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

abstract class WIC_Entity_Parent {
	
	protected $entity		= ''; 						// e.g., constituent, activity, issue
	protected $entity_instance = '';					// relevant where entity is a row of multivalue array as in emails for a constituent
	protected $fields = array(); 						// will be initialized as field_slug => type from wp_wic_data_dictionary
	protected $data_object_array = array(); 		// will be initialized as field_slug => control object 
	protected $outcome = '';							// results of latest request 
	protected $outcome_dups = false;					// supplementary outcome information -- dups among error causes	
	protected $explanation	= '';						// explanation for outcome
	
		
	abstract protected function set_entity_parms ( $args ); // must be included to set entity
	abstract protected function new_form();
	abstract protected function form_search();
	abstract	protected function id_search( $args );
	abstract protected function form_update ( $args );
	abstract	protected function form_save ( $args );
	
	/*
	*
	* constructor just initializes minimal blank structure and passes control to named action requested
	* 
	* note that the current class is an abstract parent class WIC_Entity
	* 	-- entity is chosen in the wp-issues-crm which initializes the corresponding child class  -- e.g. WIC_Constituent
	*  
	* args is an associative array, which MAY be populated as follows:
	*	-- the following are arguments in the control array from form buttons
	*		'id_requested'			=>	$control_array[2],
	*		'referring_parent' 	=> $control_array[3],
	*		'new_form'				=> $control_array[4],
	*  -- the following will be passed in the case of the object being initialized as a multi-value field
	*		'instance'				=> '',	
	*
	*/
	public function __construct ( $action_requested, $args ) {
		$this->set_entity_parms( $args );
		$this->$action_requested( $args );

	}

	/*************************************************************************************
	*
	*  METHODS FOR FILLING THE DATA_OBJECT_ARRAY
	*
	**************************************************************************************/
	protected function initialize_data_object_array()  {
		// initialize_data_object_array as field_slug => control object 
		foreach ( $this->fields as $field ) { 
			$this->data_object_array[$field->field_slug] = WIC_Control_Factory::make_a_control( $field->field_type );
			$this->data_object_array[$field->field_slug]->initialize_default_values(  $this->entity, $field->field_slug, $this->entity_instance );
		}		
	}

	protected function populate_data_object_array_from_submitted_form() {
		foreach ( $this->fields as $field ) {  	
			$this->data_object_array[$field->field_slug] = WIC_Control_Factory::make_a_control( $field->field_type );
			$this->data_object_array[$field->field_slug]->initialize_default_values(  $this->entity, $field->field_slug, $this->entity_instance  );
			if ( isset ( $_POST[$field->field_slug] ) ) {		
				$this->data_object_array[$field->field_slug]->set_value ( $_POST[$field->field_slug] );
			}	
		} 
	}	

	protected function populate_data_object_array_from_found_record( &$wic_query) {
		foreach ( $this->data_object_array as $field_slug => $control ) { 
			if ( ! $control->is_multivalue() ) {
				$control->set_value ( $wic_query->result[0]->{$field_slug} );
			} else { // for multivalue fields, set_value wants array of row arrays -- 
						// query results don't have that form or even an appropriate field slug  
				$control->set_value_by_parent_pointer( $wic_query->result[0]->ID );
			}
		} 
	}	

	/*************************************************************************************
	*
	*  METHODS FOR SANITIZING VALIDATING THE DATA_OBJECT_ARRAY
	*     Results stored in object properties -- outcome, outcome_dups, explanation
	*
	**************************************************************************************/
	private function update_ready( $save ) {
		// runs all four sanitize/validate functions
		$this->sanitize_values();
		$this->dup_check ( $save );
		$this->validate_values();
		$this->required_check();
	}
	
	public function sanitize_values() {
		// have each control sanitize itself
		foreach ( $this->data_object_array as $field => $control ) {
			$control->sanitize();
		}
	}

	protected function dup_check ( $save ) {
		// check for dups -- $save is true/false for save/update 
		$dup_check_array = array();
		foreach ( $this->data_object_array as $field_slug => $control ) {
			if	( $control->dup_check() ) {
				$dup_check_array[$field_slug] = $control;
			}	
		}	
		if ( count ($dup_check_array ) > 0 ) {
			$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
			$wic_query->search ( $this->assemble_meta_query_array( true ) );  // true indicates a dedup search
			if ( $wic_query->found_count > 1 || ( ( 1 == $wic_query->found_count ) && 
						( $wic_query->result[0]->ID != $this->data_object_array['ID']->get_value() ) )
						// for update, 1 group OK iff same record
					|| ( $save && $wic_query->found_count > 0 ) ) {
						// for save, dups are never OK
				$this->outcome = false;
				$dup_check_string = WIC_DB_Dictionary::get_dup_check_string ( $this->entity );
				$this->explanation .= sprintf ( __( 'Other records found with same combination of %s' , 'wp-issues-crm' ), $dup_check_string );
				$this->outcome_dups = true;		
			}
		}		 
	}

	protected function validate_values( ) {
		// have each control validate itself and report
		$validation_errors = '';		
		foreach ( $this->data_object_array as $field => $control ) {
			$validation_errors .= $control->validate();
		}
		if ( $validation_errors > '' ) {
				$this->outcome = false;		
				$this->explanation .= $validation_errors;
		}
	}

	protected function required_check () {
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
			$required_errors .= sprintf ( __( ' At least one among %s is required. ', 'wp-issues-crm' ), WIC_DB_Dictionary::get_group_required_string( $this->entity ) );
		}
		if ( $required_errors > '' ) {
			$this->outcome = false;
			$this->explanation .= $required_errors;		
		}
   }

	/*************************************************************************************
	*
	*  METHODS FOR COMPILING DATA BASE ACCESS REQUESTS FROM CONTROLS
	*     
	*
	**************************************************************************************/
	public function assemble_meta_query_array ( $dup_check ) {
		$meta_query_array = array ();
		foreach ( $this->data_object_array as $field => $control ) {
			$query_clause = $control->create_search_clause( $dup_check );
			if ( is_array ( $query_clause ) && // skipping empty fields
					( ! $dup_check || $control->dup_check() ) ) { // including all non-empty or only those that are dupcheck fields  
				$meta_query_array = array_merge ( $meta_query_array, $query_clause );
			}
		}	
		return $meta_query_array;
	}	

	/*************************************************************************************
	*
	*  REQUEST HANDLERS: new form, id search, general search, save/update
	*     Child class functions are wrap arounds to choose next forms
	*
	**************************************************************************************/

	protected function new_form_generic( $form ) {
		$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
		$this->initialize_data_object_array();
		$new_search = new $form;
		$new_search->layout_form( $this->data_object_array, 
			__( 'Enter data and search. If record not found, you will be able to save.', 'wp-issues-crm'),
			 'guidance' );
	}	

	//handle an update request coming from a form
	protected function form_save_update_generic ( $args, $save, $fail_form, $success_form ) {
		$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
		$this->populate_data_object_array_from_submitted_form();
		$this->update_ready( $save ); // false, not a save
		if ( false === $this->outcome ) {
			$message = __( 'Not successful: ', 'wp-issues-crm' ) . $this->explanation;
			$message_level = 'error';
			$form = new $fail_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level );
			if ( $this->outcome_dups ) {	
				$lister = new WIC_List_Parent;
				$list = $lister->format_entity_list( $this->data_object_array, false );
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
			$message = __( 'Successful.  You can update further. ', 'wp-issues-crm' );
			$message_level = 'good_news';
			$form = new $success_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level );					
		}
	}
	
	// handle a search request for an ID coming from anywhere
	protected function id_search_generic ( $args, $success_form ) {
		// initialize data array with only the ID and do search
		$this->data_object_array['ID'] = WIC_Control_Factory::make_a_control( 'text' );
		$this->data_object_array['ID']->initialize_default_values(  $this->entity, 'ID' );	
		$this->data_object_array['ID']->set_value( $args['id_requested'] );
		$wic_query = 	WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_query->search ( $this->assemble_meta_query_array( false ) ); 
		// retrieve record if found, otherwise error
		if ( 1==$wic_query->found_count ) {
			$message = __( 'Record Retrieved. Try an update?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
			$this->initialize_data_object_array();	
			$this->populate_data_object_array_from_found_record ( $wic_query );			
			$update_form = new $success_form;
			$update_form->layout_form ( $this->data_object_array, $message, $message_level );	
		} else {
			die ( sprintf ( __( 'Data base corrupted for record ID: %1$s', 'wp-issues-crm' ) , $args['id_requested'] ) );		
		} 
	}
	
	protected function form_search_generic ( $save_form, $update_form ) { 
		$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
		$this->populate_data_object_array_from_submitted_form();
		$this->sanitize_values();
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_query->search ( $this->assemble_meta_query_array( false ) );
		if ( 0 == $wic_query->found_count ) {
			$message = __( 'No matching record found -- try a save?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$form = new $save_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level );			
		} elseif ( 1 == $wic_query->found_count) {
			$message = __( 'One matching record found. Try an update?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$this->populate_data_object_array_from_found_record ( $wic_query );			
			$form = new $update_form;
			$form->layout_form (	$this->data_object_array, $message, $message_level );			
		} else {
			$lister = new WIC_List_Parent;
			$list = $lister->format_entity_list( $wic_query,true );
			echo $list;	
		}						
	}
	

}

