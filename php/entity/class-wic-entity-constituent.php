<?php
/*
*
*	wic-constituent.php
*
*/

class WIC_Entity_Constituent extends WIC_Entity_Parent {

	protected function set_entity_parms() {
		$this->entity = 'constituent';
	} 

	// handle a request for a new standard form
	protected function new_form() {
		$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
		$this->initialize_data_object_array();
		$new_constituent_search = new WIC_Form_Constituent_Search;
		$new_constituent_search->layout_form( $this->data_object_array, 
			__( 'Enter constituent data and search. If record not found, you will be able to save.', 'wp-issues-crm'),
			 'guidance' );
	}

	// handle a search request coming from a standard form
	protected function form_search () { 
		$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
		$this->initialize_data_object_array_from_submitted_form();
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_query->search ( $this->data_object_array );
		if ( 0 == $wic_query->found_count ) {
			$message = __( 'No matching record found -- try a save?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$save_form = new WIC_Form_Constituent_Save;
			$save_form->layout_form ( $this->data_object_array, $message, $message_level );			
		} elseif ( 1 == $wic_query->found_count) {
			$message = __( 'One matching record found. Try an update?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$this->populate_data_object_array_from_found_record ( $wic_query );			
			$save_form = new WIC_Form_Constituent_Update;
			$save_form->layout_form (	$this->data_object_array, $message, $message_level );			
		} else {
			$lister = new WIC_List_Parent;
			$list = $lister->format_entity_list( $wic_query,true );
			echo $list;	
		}						
	}
	
	// handle a search request for an ID coming from anywhere
	protected function id_search ( $args ) {
		// initialize data array with only the ID and do search
		$this->data_object_array['ID'] = WIC_Control_Factory::make_a_control( 'text' );
		$this->data_object_array['ID']->initialize_default_values(  $this->entity, 'ID' );	
		$this->data_object_array['ID']->set_value( $args['id_requested'] );
		$wic_query = 	WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_query->search ( $this->data_object_array ); 
		// retrieve record if found, otherwise error
		if ( 1==$wic_query->found_count ) {
			$message = __( 'Record Retrieved. Try an update?', 'wp-issues-crm' );
			$message_level =  'guidance';
			$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
			$this->initialize_data_object_array();	
			$this->populate_data_object_array_from_found_record ( $wic_query );			
			$update_form = new WIC_Form_Constituent_Update;
			$update_form->layout_form ( $this->data_object_array, $message, $message_level );	
		} else {
			die ( sprintf ( __( 'Data base corrupted for record ID: %1$s', 'wp-issues-crm' ) , $args['id_requested'] ) );		
		} 
	}

	//handle an update request coming from a form
	protected function form_update ( $args ) {
		$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
		$this->initialize_data_object_array_from_submitted_form();
		$wic_access_object = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_access_object->update ( $this->data_object_array ); 
		if ( false === $wic_access_object->outcome ) {
			$message = __( 'Please correct form errors: ', 'wp-issues-crm' ) . $wic_access_object->explanation;
			$message_level = 'error';
			$update_form = new WIC_Form_Constituent_Update;
			$update_form->layout_form ( $wic_access_object->sanitized_array, $message, $message_level );
			if ( $wic_access_object->found_count > 1 || ( ( 1 == $wic_access_object->found_count ) && ( $wic_access_object->result[0]->ID != $this->data_object_array['ID']->get_value() ) ) ) {	
				$lister = new WIC_List_Parent;
				$list = $lister->format_entity_list( $wic_access_object, false );
				echo $list;
			}	
		} else {
			$message = __( 'Update successful.  You can update again. ', 'wp-issues-crm' ) . $wic_access_object->explanation;
			$message_level = 'good_news';
			$update_form = new WIC_Form_Constituent_Update;
			$update_form->layout_form ( $wic_access_object->sanitized_array, $message, $message_level );					
		}
	}
	
	protected function form_save ( $args ) {
		$this->fields = WIC_DB_Dictionary::get_form_fields( $this->entity );
		$this->initialize_data_object_array_from_submitted_form();
		$wic_access_object = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_access_object->save ( $this->data_object_array ); 
		if ( false === $wic_access_object->outcome ) {
			$message = __( 'Please correct form errors: ', 'wp-issues-crm' ) . $wic_access_object->explanation;
			$message_level = 'error';
			$save_form = new WIC_Form_Constituent_Save;
			$save_form->layout_form ( $wic_access_object->sanitized_array, $message, $message_level );
			if ( $wic_access_object->found_count > 0 ) {	
				$lister = new WIC_List_Parent;
				$list = $lister->format_entity_list( $wic_access_object, false );
				echo $list;
			}	
		} else {
			$message = __( 'New record saved.  You can update it further. ', 'wp-issues-crm' ) . $wic_access_object->explanation;
			$message_level = 'good_news';
			$update_form = new WIC_Form_Constituent_Update;
			$update_form->layout_form ( $this->data_object_array, $message, $message_level );					
		}
	}
}