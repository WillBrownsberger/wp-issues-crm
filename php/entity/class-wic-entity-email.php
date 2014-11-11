<?php
/*
*
*	wic-entity-email.php
*
*/



class WIC_Entity_Email extends WIC_Entity_Parent {

	protected function set_entity_parms( $args ) {
		extract ( $args );
		$this->entity = 'email';
		$this->entity_instance = $instance;
	} 

	protected function initialize() {
		$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
		$this->initialize_data_object_array();
	/*	echo '<BR/> INITIALIZING';
		var_dump ($this->entity_instance);
		foreach ($this->data_object_array as $control) {
			echo '<br/>----------------------------------------------';
			var_dump($control->default_control_args['field_slug']);
			var_dump($control->default_control_args['value']);
		} */
	}
	
	protected function populate( $args ) {
		extract( $args );
		// expects form_row_array among args; 
		// instance also present, but has already been processed in __construct
		// here, just getting values from form array 
		// note that row numbering may not synch between $_POST and the multivalue array 
		$this->initialize();
		var_dump ($form_row_array);
		foreach ($this->fields as $field ) {
			// var_dump ($field->field_slug); 
			$this->data_object_array[$field->field_slug]->set_value( $form_row_array[$field->field_slug] );
			echo '------------' . $this->data_object_array[$field->field_slug]->get_value();	
		}

	}

	public function search_row() {
		$new_search_row_object = new WIC_Form_Multivalue_Search ( $this->entity );
		$new_search_row = $new_search_row_object->layout_form ( $this->data_object_array, null, null);
		return $new_search_row;
	}

	// empty functions required by parent class, but not implemented
	protected function new_form() {}
	protected function form_search () {}
	protected function id_search ( $args ) {}
	protected function form_update ( $args ) {}
	protected function form_save ( $args ) {}
}