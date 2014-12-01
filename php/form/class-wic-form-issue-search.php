<?php
/*
*
*  class-wic-form-issue-search.php
*
*/

class WIC_Form_issue_Search extends WIC_Form_Parent  {
	
	protected function get_the_entity() {
		return ( 'issue' );	
	}

	protected function get_the_buttons () {
		$button_args_main = array(
			'entity_requested'			=> 'issue',
			'action_requested'			=> 'form_search',
			'button_label'					=> __('Search', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) ) ;
	}
	
	protected function format_message ( &$data_array, $message ) {
		return ( __('Search issues. ', 'wp-issues-crm') . $message );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->search_control() ); 
	}

	protected function get_the_legends( $sql = '' ) {
		$legend = '<p class = "wic-form-legend">' .  __( 'Issue content (body and title) is searched using Word Press full text scan.' , 'wp-issues-crm' ) . '</p>';
		return ( $legend );
	}
}