<?php
/*
*
*  class-wic-form-constituent-save.php
*
*/

class WIC_Form_Constituent_Save extends WIC_Form_Constituent_Update  {
	
	protected function get_the_buttons () {
		$button_args_main = array(
			'entity_requested'			=> 'constituent',
			'action_requested'			=> 'form_save',
			'button_label'					=> __('Save', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) . parent::backbutton (' second-position'  ) ) ;
	}
	
	protected function format_message ( &$data_array, $message ) {
		$formatted_message =  __('Save new constituent. ' , 'wp-issues-crm') . $message;
		return $formatted_message; 
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->save_control() ); 
	}

	
}