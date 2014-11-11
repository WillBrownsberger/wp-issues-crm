<?php
/*
* wic-control-multivalue.php
*
*/
class WIC_Control_Multivalue extends WIC_Control_Parent {
	
	public function initialize_default_values ( $entity, $field_slug, $instance ) {
		// here just initializing the this multivalue control itself
		parent::initialize_default_values( $entity, $field_slug, $instance );
		// here initializing the multi-value array
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
	* 	 based on the array of post values -- set value is called from populate_data_object_array_from_submitted_form()
	*   in WIC_Parent_Entity.  The multivalue control has been created, but not initialized with a value.
	* Even if it had a value, this would be the appropriate response -- overlay the value with a new array of objects.  
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
			$this->value[$instance_counter] = new $class_name( 'populate', $args );
			$instance_counter++;
		}
	}

	public function sanitize() {
		foreach ( $this->value as $row_object ) {
			$row_object->sanitize_values();		
		}	
	}


	public function search_control ( $control_args ) {
/*		$final_control_args = array_merge ( $this->default_control_args, $control_args );
		$final_control_args['readonly'] = false;
		$final_control_args['field_label_suffix'] = $final_control_args['like_search_enabled'] ? '(%)' : '';
		$final_control_args['value'] = $this->value;
		$control =  $this->create_control( $final_control_args ) ;*/
		$control = $this->value[0]->search_row();
	 	echo $control;
	}
	
	

}	
