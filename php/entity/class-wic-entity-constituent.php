<?php
/*
*
*	wic-constituent.php
*
*/

class WIC_Entity_Constituent extends WIC_Entity_Parent {

	protected function set_entity_parms( $args ) {
		extract ( $args );
		$this->entity = 'constituent';
		$this->entity_instance = $instance;
	} 

	// handle a request for a new standard form
	protected function new_form() { 
		$this->new_form_generic( 'WIC_Form_Constituent_Search' );
		return;
	}

	// handle a search request coming from a standard form
	protected function form_search () { 
		$this->form_search_generic ( 'WIC_Form_Constituent_Save', 'WIC_Form_Constituent_Update');
		return;				
	}
	
	// handle a search request for an ID coming from anywhere
	protected function id_search ( $args ) {
		$this->id_search_generic ( $args, 'WIC_Form_Constituent_Update' );
		return;		
	}

	//handle an update request coming from a standard form
	protected function form_update ( $args ) {
		$this->form_save_update_generic ( $args, false, 'WIC_Form_Constituent_Update', 'WIC_Form_Constituent_Update' );
		return;
	}
	
	//handle a save request coming from a standard form
	protected function form_save ( $args ) {
		$this->form_save_update_generic ( $args, true, 'WIC_Form_Constituent_Save', 'WIC_Form_Constituent_Update' );
		return;
	}
}