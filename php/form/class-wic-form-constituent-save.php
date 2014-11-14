<?php
/*
*
*  class-wic-form-constituent-update.php
*
*/

class WIC_Form_Constituent_Save extends WIC_Form_Constituent_Update  {
	
	protected function get_the_entity() {
		return ( 'constituent' );	
	}

	protected function get_the_buttons () {
		$button_args_main = array(
			'entity_requested'			=> 'constituent',
			'action_requested'			=> 'form_save',
			'button_label'					=> __('Save Constituent', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) ) ;
	}
	
	protected function get_the_header ( &$data_array ) {
		return ( __('Save New Constituent' , 'wp-issues-crm') );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->save_control() ); 
	}

	
}