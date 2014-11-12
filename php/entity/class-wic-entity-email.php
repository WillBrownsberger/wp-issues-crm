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
	}
	
	protected function populate_from_form( $args ) {
		extract( $args );
		// expects form_row_array among args; 
		// instance also present, but has already been processed in __construct
		// here, just getting values from form array 
		// note that row numbering may not synch between $_POST and the multivalue array 
		$this->initialize();
		foreach ($this->fields as $field ) {
			$this->data_object_array[$field->field_slug]->set_value( $form_row_array[$field->field_slug] );
		}
	}

	protected function populate_from_object( $args ) {
		extract( $args );
		$this->initialize();
		foreach ($this->fields as $field ) {
			$this->data_object_array[$field->field_slug]->set_value( $form_row_object->{$field->field_slug} );
		}
	}


	public function search_row() {
		$new_search_row_object = new WIC_Form_Multivalue_Search ( $this->entity );
		$new_search_row = $new_search_row_object->layout_form ( $this->data_object_array, null, null);
		return $new_search_row;
	}

	public function update_row() {
		$new_search_row_object = new WIC_Form_Multivalue_Update ( $this->entity );
		$class = ( 'row-template' == $this->entity_instance ) ? 'hidden-template' : 'visible-templated-row';
		$new_search_row = '<div class = "'. $class . '" id="' . $this->entity . '-' . $this->entity_instance . '">';
			$new_search_row .= $new_search_row_object->layout_form( $this->data_object_array, null, null );
			$new_search_row .= $this->create_destroy_button ( $this->entity . '-' . $this->entity_instance );
		$new_search_row .= '</div>';
		return $new_search_row;
	}

/*	protected function create_destroy_button () {

		$button = '<button ' .  
			' class	="destroy-button"' . 
			' onclick = {this.parentNode.parentNode.removeChild(this.parentNode);}' .
			' type 	= "button" ' .
			' name	= "destroy-button" ' .
			' title  = ' . __( 'Remove Row', 'wp-issues-crm' ) .
			' >x</button>';	

		return ($button);
	}
*/
	protected function create_destroy_button ( $base ) {
		
		$button ='<button ' . 
			' class = "destroy-button" ' .
			' name	= "destroy-button" ' .
			' title  = ' . __( 'Remove Row', 'wp-issues-crm' ) .
			' type = "button" ' .
			' onclick="hideSelf(\'' . esc_attr( $base ) . '\')" ' .
			' >x</button>'; 

		return ($button);
	}
		

	// empty functions required by parent class, but not implemented
	protected function new_form() {}
	protected function form_search () {}
	protected function id_search ( $args ) {}
	protected function form_update ( $args ) {}
	protected function form_save ( $args ) {}
}