<?php
/*
*
*	wic-constituent.php
*
*/

class WIC_Constituent extends WIC_Entity {

	protected function set_entity_parms() {
		$this->entity = 'constituent';
	} 

	// handle a request for a new search form
	protected function new_form() {
		$this->fields = WIC_Data_Dictionary::get_fields( $this->entity );
		$this->data_array = $this->initialize_data_array();
		$new_constituent_search = new WIC_Constituent_Search_Form;
		$new_constituent_search->layout_form( $this->data_array, 
			__( 'Enter constituent data and search. If record not found, you will be able to save.', 'wp-issues-crm'),
			 'wic_form_no_errors' );
	}

	// handle a search request coming from a form
	protected function form_search () { 
		$this->fields = WIC_Data_Dictionary::get_fields( $this->entity );
		$this->initialize_data_array_from_submitted_form();
		$wic_db_access = WIC_DB_Access_Factory::make_a_db_access_ojbect( $this->entity );
		$wic_results = $wic_db_access->search ( $this->entity, $this->data_array );



		$wic_query = $wpdb->get_results( prepare_search_sql ('new') );
		if ( 0 == $wpdb->num_rows ) {
			$this->guidance	=	__( 'No matching record found. Try a save? ', 'wp-issues-crm' );
			$this->next_action 	=	'save';
		} elseif ( 1 == $wpdb->num_rows ) {
			// overwrite form with that unique record's  values
			$this->populate_fields ( $wic_query );
			$this->guidance	=	__( 'One matching record found. Try an update?', 'wp-issues-crm' );
			$this->next_action 	=	'update';
		} else {
			$this->guidance	=	__( 'Multiple records found (results below). ', 'wp-issues-crm' );
			$this->next_action 	=	'search';
			$show_list = true;
		}						
	

	}	

}