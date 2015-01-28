<?php
/*
*
*  class-wic-form-constituent-search.php
*
*/

class WIC_Form_Data_Dictionary_Save extends WIC_Form_Parent  {
	
	// associate form with entity in data dictionary
	protected function get_the_entity() {
		return ( 'data_dictionary' );	
	}

	// define the top row of buttons (return a row of wic_form_button s)
	protected function get_the_buttons () {

		$buttons = '';
		
		$button_args_main = array(
			'entity_requested'			=> 'data_dictionary',
			'action_requested'			=> 'form_save',
			'button_class'					=> 'button button-primary wic-form-button',
			'button_label'					=> __('Save', 'wp-issues-crm'),
		);	
		$buttons .= $this->create_wic_form_button ( $button_args_main );
		
		$buttons .= '<a href="/wp-admin/admin.php?page=wp-issues-crm-fields">' . __( 'Back to Fields List', 'wp-issues-crm' ) . '</a>';

		return $buttons;
		
	}
	
	// define the form message (return a message)
	protected function format_message ( &$data_array, $message ) {
		return ( __('Save Custom Field. ', 'wp-issues-crm') . $message );
	}

	// chose search controls
	protected function get_the_formatted_control ( $control ) {
		return ( $control->save_control() ); 
	}

	// screen in all groups (only one)
	protected function group_screen ( $group ) {
		return true;	
	}

	// hooks not implemented
	protected function supplemental_attributes() {}
	protected function get_the_legends( $sql = '' ) {}	
	protected function group_special( $group ) {}
	protected function pre_button_messaging ( &$data_array ){}
	protected function post_form_hook ( &$data_array ) {} 

}