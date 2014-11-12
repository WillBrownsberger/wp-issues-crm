<?php
/*
* wic-control-multivalue.php
*
*/
class WIC_Control_Multivalue extends WIC_Control_Parent {
	
	public function initialize_default_values ( $entity, $field_slug, $instance ) {
		// first just initializing the this multivalue control itself
		parent::initialize_default_values( $entity, $field_slug, $instance );
		// now initializing the multi-value array
		$this->value = array();
		// here initializing the first row of the multi-value array -- field slug for the multi value is the class of the rows
		$class_name = 'WIC_Entity_' . initial_cap ( $field_slug ) ; // note, php would forgive the initial_cap missing in the field_slug, but . . . 
		$args = array(
			'instance' => '0'		
		);
		// each control within the new row object will have its own plain field_slug from the db_dictionary, 
		// BUT $default_control_arg['field_slug'] will be wrapped with the array location of the field slug -- Multivalue slug and row number
		// this happens in parent::initialize_default_values when $instance is passed as non-empty string;
		$this->value[] = new $class_name( 'initialize', $args );
	}

	/*
	* In WIC_Control_Parent, this just passes the value through, but in this multivalue context have to create the whole array of objects
	* 	 based on the array of post values -- set value is called by populate_data_object_array_from_submitted_form()
	*   in WIC_Parent_Entity.  The multivalue control has been created, but not been initialized with a value.
	* Even if it had a value, this would be the appropriate response -- to overlay the value with a new array of objects.  
	*
	*/
	public function set_value ( $value ) {
		$this->value = array();
		$class_name = 'WIC_Entity_' . initial_cap ( $this->field->field_slug );
		$instance_counter = 0;
		foreach ( $value as $form_row_array ) {
			$args = array (
				'instance' => strval( $instance_counter ),
				'form_row_array' => $form_row_array, // have to pass whole row, since can't assume $_POST numbering is the same							
			);
			$this->value[$instance_counter] = new $class_name( 'populate_from_form', $args );
			$instance_counter++;
		}
	}

	/*
	*	Called in lieu of set_value when value is in the form a larger query return which does not include a reference to 
	*    the summary name of the climate pointer  
	*/
	public function set_value_by_parent_pointer ( $pointer ) {
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->field->field_slug );
		$wic_query->search ( 		
			array ( // double layer array to standardize a return that allows multivalue fields
					array (
						'table'	=> $this->field->field_slug,
						'key' 	=> $this->field->entity_slug . '_id',
						'value'	=> $pointer,
						'compare'=> '=',
					)
				)
			);

		$this->value = array();
		$class_name = 'WIC_Entity_' . initial_cap ( $this->field->field_slug );
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
	* 
	* sanitize -- each row object has its own sanitize function, so this is easy
	*
	*/
	public function sanitize() {
		foreach ( $this->value as $row_object ) {
			$row_object->sanitize_values();		
		}	
	}

	/*
	*
	* search control works off a single row, producing controls for that row -- $control_args are ignored
	*
	*/
	public function search_control ( $control_args ) {
		$control = $this->value[0]->search_row();
	 	echo $control;
	}

	public function create_search_clause ( $dup_check ) {
		$query_clause = $this->value[0]->assemble_meta_query_array( $dup_check );
		return ( $query_clause ); 	
	}
	
	public function update_control ( $control_args ) {
		$final_control_args = array_merge ( $this->default_control_args, $control_args );
		extract ( $final_control_args );
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' . $field_label_suffix . '</span>' : '';
		 
		$control_set = ( $field_label > '' && ! ( 1 == $hidden ) ) ? '<label class="' . esc_attr ( $label_class ) .
				 ' ' . esc_attr( $field_slug_css ) . '" for="' . esc_attr( $field_slug ) . '">' . esc_html( $field_label ) . '</label>' : '' ;
		// create emails division opening tag 		
		$control_set .= '<div id = "' . $this->field->field_slug . '-control-set' . '" class = "wic-multivalue-control-set">';

		// create a hidden template row for adding rows in wic-utilities.js through moreFields() 
		// moreFields will replace the string 'row-template' with row-counter index value after creating the new row
		
		$class_name = 'WIC_Entity_' . initial_cap ( $this->field->field_slug ) ; // note, php would forgive the initial_cap missing in the field_slug, but . . . 
		$args = array(
			'instance' => 'row-template'		
			);
		$template = new $class_name( 'initialize', $args );
		$control_set .= $template->update_row ( $save );

		// now proceed to add rows for any existing records from database or previous form
		
		if ( count ( $this->value ) > 0 ) {
			foreach ( $this->value as $value_row ) {
				$control_set .= $value_row->update_row ( $save );
			}
		}		

		$control_set .= '<div class = "hidden-template" id = "' . $this->field->field_slug . '-row-counter">' . count( $this->value ) . '</div>';		
		$control_set .= $this->create_add_button ( $this->field->field_slug, sprintf ( __( 'Add %s ', 'wp-issues-crm' ), $this->field->field_label ) . ' ' . $repeater_group_label_suffix ) ;
		$control_set .= '</div>';

		return ($control_set);	
	}






	
		/* this button will create a new instance of the templated base paragraph (repeater row) and insert it above related counter in the DOM*/
	public function create_add_button ( $base, $button_label ) {
		
		$button =  
			'<button ' . 
			' class = "row-add-button" ' .
			' id = "' . esc_attr( $base ) . '-add-button" ' .
			' type = "button" ' .
			' onclick="moreFields(\'' . esc_attr( $base ) . '\')" ' .
			' >' . esc_html(  $button_label ) . '</button>'; 

		return ($button);
	}
		

}	
