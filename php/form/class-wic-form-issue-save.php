<?php
/*
*
*  class-wic-form-issue-save.php
*
*/

class WIC_Form_Issue_Save extends WIC_Form_Issue_Update  {
	
	protected function get_the_buttons () {
		$button_args_main = array(
			'entity_requested'			=> 'issue',
			'action_requested'			=> 'form_save',
			'button_label'					=> __('Save', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) ) ;
	}
	
	protected function get_the_header ( &$data_array ) {
		return ( __('Save New Issue' , 'wp-issues-crm') );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->save_control() ); 
	}

	
}