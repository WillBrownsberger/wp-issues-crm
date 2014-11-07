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
		$this->initialize_data_array();
		$new_constituent_search = new WIC_Form_Constituent_Search;
		$new_constituent_search->layout_form( $this->data_array, 
			__( 'Enter constituent data and search. If record not found, you will be able to save.', 'wp-issues-crm'),
			 'guidance' );
	}

	// handle a search request coming from a form
	protected function form_search () { 
		$this->fields = WIC_Data_Dictionary::get_fields( $this->entity );
		$this->initialize_data_array_from_submitted_form();
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_query->search ( $this->data_array );
		if ( 0 == $wic_query->outcome ) {
			$message = __( 'No matching record found -- try a save?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$save_form = new WIC_Form_Constituent_Save;
			$save_form->layout_form ( $this->data_array, $message, $message_level );			
		} elseif ( 1 == $wic_query->outcome) {
			$message = __( 'One matching record found. Try an update?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$this->initialize_data_array_from_found_record ( $wic_query );			
			$save_form = new WIC_Form_Constituent_Update;
			$save_form->layout_form (	$this->data_array, $message, $message_level );			
		} else {
			$list = WIC_List::format_entity_list ( $wic_query,true );
			echo $list;	
		}						
	}
	
	// handle a search request for an ID coming from anywhere
	protected function id_search ( $args ) {
		$wic_query = 	WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$data_array = 	array (
				'ID' => $args['id_requested'],
			);
		$wic_query->search ( $data_array ); 
		if ( 1==$wic_query->outcome ) {
			$message = __( 'Record Retrieved. Try an update?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$this->initialize_data_array_from_found_record ( $wic_query );			
			$update_form = new WIC_Form_Constituent_Update;
			$update_form->layout_form ( $this->data_array, $message, $message_level );	
		} else {
			die ( sprintf ( __( 'Data base corrupted for record ID: $1%s', 'wp-issues-crm' ) , $args['id_requested'] ) );		
		} 
	}





}