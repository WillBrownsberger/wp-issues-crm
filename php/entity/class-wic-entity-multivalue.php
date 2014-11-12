<?php
/*
*
*	wic-entity-multivalue.php
*
*/



abstract class WIC_Entity_Multivalue extends WIC_Entity_Parent {

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
			if ( isset ( $form_row_array[$field->field_slug] ) ) {
				$this->data_object_array[$field->field_slug]->set_value( $form_row_array[$field->field_slug] );
			}
		}
	}

	protected function populate_from_object( $args ) {
		extract( $args );
		$this->initialize();
		foreach ( $this->fields as $field ) {
			if ( ! $this->data_object_array[$field->field_slug]->is_transient() ) {
				$this->data_object_array[$field->field_slug]->set_value( $form_row_object->{$field->field_slug} );
			}
		}
	}

	public function search_row() {
		$new_search_row_object = new WIC_Form_Multivalue_Search ( $this->entity );
		$new_search_row = $new_search_row_object->layout_form ( $this->data_object_array, null, null);
		return $new_search_row;
	}

	public function update_row() {
		$new_update_row_object = new WIC_Form_Multivalue_Update ( $this->entity, $this->entity_instance );
		$new_update_row = $new_update_row_object->layout_form( $this->data_object_array, null, null );
		return $new_update_row;
	}



	// empty functions required by parent class, but not implemented
	protected function new_form() {}
	protected function form_search () {}
	protected function id_search ( $args ) {}
	protected function form_update ( $args ) {}
	protected function form_save ( $args ) {}
}