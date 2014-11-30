<?php
/*
* wic-control-multivalue.php
*
* NOTE: CANNOT HAVE MULTIVALUE WITH IN MULTIVALUE -- SET VALUE FOR MULTIVALUE BYPASSES ARRAYS
*
*/
class WIC_Control_Multivalue extends WIC_Control_Parent {
	
	/*************************************************************************************
	*
	*	Multi value control needs special methods for initialization and population, 
	*		since its value is not a scalar but an array of objects.
	*	
	*
	**************************************************************************************/
	
	public function reset_value() {  
		$this->value = array();
	}		
	
	protected function set_blank_first_row () { // called in search control set up
		// here initializing the first row of the multi-value array -- field slug for the multi value is the class of the rows
		$class_name = 'WIC_Entity_' . $this->field->field_slug; // note -- class_name is not case sensitive, but autoloader looks from WIC_ (sic) 
		$args = array(
			'instance' => '0'		
		);
		// each control within the new row object will have its own plain field_slug from the db_dictionary, 
		// BUT $default_control_arg['field_slug'] will be wrapped with the array location of the field slug -- Multivalue slug and row number
		// this happens in parent::initialize_default_values when $instance is passed as non-empty string;
		$this->value[] = new $class_name( 'initialize', $args );
	}

	/*
	* In WIC_Control_Parent, set_value just passes the value through, but in this multivalue context have to create the whole array of objects
	* 	 based on the array of post values -- set value is called by populate_data_object_array_from_submitted_form()
	*   in WIC_Parent_Entity.  At that stage, the multivalue control has been created and initiated with an empty array.
	*	 Need to fill that array with objects by parsing the form
	*
	*	If the form includes deleted rows, get rid of them at this stage: Discard if not from db or do the delete.  
	*
	*/
	public function set_value ( $value ) { // value is an array created by multi-value field coming back from $_Post
		$this->value = array();
		$class_name = 'WIC_Entity_' . $this->field->field_slug;
		$instance_counter = 0;
		foreach ( $value as $key=>$form_row_array ) {
			$args = array (
				'instance' => strval( $instance_counter ),
				'form_row_array' => $form_row_array, // have to pass whole row, since can't assume $_POST numbering is the same							
			);
			if ( strval($key) != 'row-template' ) { // skip the template row created by all multivalue fields
				if ( isset ( $form_row_array['screen_deleted'] ) ) {
					// delete screen deleted items if they came from db, otherwise, they only existed on screen, so do nothing			
					if ( $form_row_array['ID'] > 0 ) {
						$wic_access_object = WIC_DB_Access_Factory::make_a_db_access_object( $this->field->field_slug );
						$wic_access_object->delete_by_id( $form_row_array['ID'] ); 
					}
				} else { // not deleted rows -- may be blank
					$values_set = false;
					foreach ( $form_row_array as $value ){
						if ( '' != $value && 
								! is_array ( $value ) ) { 
								// the second half of this condition keeps blank date search ranges and multiselect fields from automatically looking like values
								// it limits future flexibility to create multivalue fields within multivalue fields -- probably OK
							$values_set = true;
							break;						
						}	
					}
					if ( $values_set ) {					
						$this->value[$instance_counter] = new $class_name( 'populate_from_form', $args );
						$instance_counter++;
					}
				}
			}
		}
	}

	/*
	*	Called in lieu of set_value when value is in the form a query return which does not include a reference to 
	*    the summary name of the multivalue control -- e.g., it will have ID, constituent ID, email_address, 
	*    but not the column named email.  Also, it may have multiple rows per email if, for example, there are also multiple phones
	*    So, when retrieved a top level entity like constituent, will work from the ID of that constituent to construct the possible array
	*    of emails, phones or other multivalues for that entity.
	*/
	public function set_value_by_parent_pointer ( $pointer ) { // pointer is the ID of the top-level entity -- constituent
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->field->field_slug );
		$search_parameters = array(  
			'select_mode' => '*',
			'show_deleted' => true,  // get all values for each		
			); 
		$wic_query->search ( 		
			array ( // double layer array to standardize a return that allows multivalue fields
					array (
						'table'	=> $this->field->field_slug,
						'key' 	=> $this->field->entity_slug . '_id',
						'value'	=> $pointer,
						'compare'=> '=',
					)
				),   // get child records
				$search_parameters
			);

		$this->value = array();
		$class_name = 'WIC_Entity_' . $this->field->field_slug;
		$instance_counter = 0;
		foreach ( $wic_query->result as $form_row_object ) {
			$args = array (
				'instance' => strval( $instance_counter ),
				'form_row_object' => $form_row_object, // have to pass whole row, since can't assume $_POST numbering is the same							
			);
			$this->value[$instance_counter] = new $class_name( 'populate_from_object', $args );
			$instance_counter++;
		}
	}

	/*
	*  Multivalue control passes requests to its components instead of actually doing the action as scalar controls do.
	*  Depends on the components reporting back to it, so it can report back as if it were a scalar control
	*
	*/

	// sanitize -- each row object has its own sanitize function, so this is easy
	public function sanitize() {
		foreach ( $this->value as $row_object ) {
			$row_object->sanitize_values();		
		}	
	}

	// validate  each row object has its own validation function with return, so this is easy
	public function validate() { 
		$error_message = '';
		foreach ( $this->value as $row_object ) { 
			$error_message .= $row_object->validate_values();
		}
		// treat required checks for sub rows of entity as a validation issue -- 
		// should always be done even if row not required -- otherwise, end up with garbage rows.
		$required_notice = '';		
		foreach ( $this->value as $row_object ) {	
			$required_notice .= $row_object->required_check (); 
		} 
		if ( $required_notice > '' ) {
			$error_message .= sprintf ( __( ' %s row has missing elements: ', 'wp-issues-crm' ), $this->field->field_label ) . $required_notice  ; 		
		}
		return ( $error_message );	
	}

	// report whether control value is present -- i.e., as at least one valid row
	public function is_present() {
	/********************************************************************************
	* if at least one row of multi-value passes its own set of required checks 
	* for example, -- to require one email address for a constituent
	* (a) set value of email group as required and (b) define email address as required 
	* -- doing only the second step will serve to prevent population of db with blank addresses,
	* but will not force an email for each constituent.
	*********************************************************************************/
		$is_present = false;		
		if ( count ( $this->value ) > 0  ) {
			foreach ( $this->value as $row_object ) {	
				$error_message = $row_object->required_check ();
				if ( '' == $error_message ) {
					$is_present = true;
					break;				
				} 
			}			
		}
		return ( $is_present ); // true or false		
	}

	//report whether field fails individual requirement, with reasons
	public function required_check() { 
		if ( "individual" == $this->field->required && ! $this->is_present() ) {
			return ( sprintf ( __( ' %s is a required field group. ', 'wp-issues-crm' ), $this->field->field_label ) ) ;		
		} else {
			return '';	// if has non-empty value, then fails check -- consistent with scalar, but here compiled across rows. 	
		}	
	}

	/*************************************************************************************
	*
	*	Multi value controls have to generate a control set for each row. 
	*	
	*
	**************************************************************************************/
	// search control works off a single row, producing controls for that row
	public function search_control () {
		$this->set_blank_first_row(); // needed for searching
		$final_control_args = $this->default_control_args;
		extract ( $final_control_args );
		$field_label_suffix = $like_search_enabled ? '(%)' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' . $field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' && ! ( 1 == $hidden ) ) ? '<label class="' . esc_attr ( $label_class ) .
				 ' ' . esc_attr( $field_slug_css ) . '" for="' . esc_attr( $field_slug ) . '">' . esc_html( $field_label ) . '</label>' : '' ;		
		
		$control .= $this->value[0]->search_row();
	 	return ( $control );
	}

	public function update_control () {
		$control_set = $this->save_update_control ( false ); 
		return ( $control_set );	
	}
	
	public function save_control () {
		$control_set = $this->save_update_control ( true );
		return ( $control_set );	
	}


	/* 
	*
	* update control works with array of values from record or from form input
	*  generates template row
	*
	*/	
	private function save_update_control ( $save ) { // true/false corresponds to save/update
		$final_control_args = $this->default_control_args;
		extract ( $final_control_args );
		$field_label_suffix = $this->set_required_values_marker ( $required );		
		 
		$form_to_call = ( $save ) ? 'save_row' : 'update_row';		 
		 
		$control_set = ( $field_label > '' && ! ( 1 == $hidden ) ) ? '<label class="' . esc_attr ( $label_class ) .
				 ' ' . esc_attr( $field_slug_css ) . '" for="' . esc_attr( $field_slug ) . '">' . esc_html( $field_label ) . '</label>' : '' ;
		// create division opening tag 		
		$control_set .= '<div id = "' . $this->field->field_slug . '-control-set' . '" class = "wic-multivalue-control-set">';
		$control_set .= $this->create_add_button ( $this->field->field_slug, sprintf ( __( 'Add %s ', 'wp-issues-crm' ), $this->field->field_label ) . ' ' . $field_label_suffix ) ;
		// create a hidden template row for adding rows in wic-utilities.js through moreFields() 
		// moreFields will replace the string 'row-template' with row-counter index value after creating the new row
		
		$class_name = 'WIC_Entity_' . $this->field->field_slug; 
		$args = array(
			'instance' => 'row-template'		
			);
		$template = new $class_name( 'initialize', $args );
		// always initialize a save_row for the template, because will be saving that row new regardless of
		// whether main update is a save or an update
		$control_set .= $template->save_row();

		// now proceed to add rows for any existing records from database or previous form
		// this looks like it could be wrong if there were a difference between save and update -- i.e., had readonly fields in subrow
		// each row in $this->value is an entity object
		if ( count ( $this->value ) > 0 ) {
			foreach ( $this->value as $value_row ) {
				$control_set .= $value_row->$form_to_call();
			}
		}		

		$control_set .= '<div class = "hidden-template" id = "' . $this->field->field_slug . '-row-counter">' . count( $this->value ) . '</div>';		

		$control_set .= '</div>';

		return ($control_set);	
	}
	
	// the function called by this button will create a new instance of the templated base paragraph (repeater row) 
	// and insert it above related counter in the DOM
	private function create_add_button ( $base, $button_label ) {
		$button =  
			'<button ' . 
			' class = "row-add-button" ' .
			' id = "' . esc_attr( $base ) . '-add-button" ' .
			' type = "button" ' .
			' onclick="moreFields(\'' . esc_attr( $base ) . '\')" ' .
			' >' . esc_html(  $button_label ) . '</button>'; 

		return ($button);
	}

	/*************************************************************************************
	*
	*  DB ACTION REQUEST HANDLERS: 
	*
	**************************************************************************************/

	// for search, control is compiling and passing values from rows upwards to parent entity
	public function create_search_clause ( $search_clause_args ) {
		if ( count ( $this->value ) > 0 ) {
			// reset returns pointer to first element
			$query_clause = reset( $this->value )->assemble_meta_query_array( $search_clause_args );
			return ( $query_clause );
		} else {
			return ( '' );		
		} 	
	}
	
	// for update control is passing request downwards to the rows and asking them to do the updates	
	public function do_save_updates ( $id  ) {
		$errors = '';
		foreach ( $this->value as $child_entity ) {
			$errors .= $child_entity->do_save_update ( $this->field->entity_slug, $id );		 
		}
		return $errors;
	}


}	
