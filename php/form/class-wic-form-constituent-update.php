<?php
/*
*
*  class-wic-search-form.php
*
*/

class WIC_Form_Constituent_Update extends WIC_Form_Parent  {
	
	protected function get_the_entity() {
		return ( 'constituent' );	
	}

	protected function get_the_buttons () {
		$button_args_main = array(
			'entity_requested'			=> 'constituent',
			'action_requested'			=> 'form_update',
			'button_label'					=> __('Update Constituent', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) ) ;
	}
	
	protected function get_the_header ( &$data_array ) {
		$title = $this->format_name_for_title ( $data_array );
		return ( sprintf ( __('Update %1$s' , 'wp-issues-crm'), $title ) );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->update_control() ); 
	}

	protected function get_the_legends() {
		$elements = WIC_DB_Dictionary::get_field_suffix_elements( $this->get_the_entity() );
		$legend = '';
		if ( $elements[1]->required_individual ) {
			$legend =  '<p class = "wic-form-legend">' . '* ' . __('Required field.', 'wp-issues-crm' )	 . '</p>';
		}
		if ( $elements[1]->required_group ) {
			$legend .=  '<p class = "wic-form-legend">' . '(+) ' . __('At least one among these fields must be supplied.', 'wp-issues-crm' )	 . '</p>';
		}
		return  $legend;
	}
	
	protected function format_name_for_title ( &$data_array ) {
		
		// for title, use group email if have it, otherwise use individual email 
	/*	$email_for_title = '';
		if ( isset( $data_array['email_group'] ) ) {
			$email_for_title = isset( $data_array['email_group'][0][1] ) ? $data_array['email_group'][0][1]  : '';
		} 
		if ( '' == $email_for_title ) {
			$email_for_title = isset( $data_array['email'] ) ? $data_array['email_group']  : ''; 
		} */
		
   	// title is ln OR ln,fn OR fn OR email -- one of these is required in validation to be non-blank.	
		$title = 	$data_array['last_name']->get_value();
		$title .= 	$data_array['first_name']->get_value() > '' ? ( $title > '' ? ', ' : '' ) . $data_array['first_name']->get_value() : '';
	//	$title =		( '' == $title ) ? $email_for_title : $title;
		$title =		( '' == $title ) ? 'Constituent ' : $title;
		
		return  ( $title );
	}
	
}