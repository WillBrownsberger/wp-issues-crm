<?php
/*
*
*  class-wic-search-form.php
*
*/

class WIC_Form_Constituent_Search extends WIC_Form_Parent  {
	
	protected function get_the_entity() {
		return ( 'constituent' );	
	}

	protected function get_the_buttons () {
		$button_args_main = array(
			'entity_requested'			=> 'constituent',
			'action_requested'			=> 'form_search',
			'button_label'					=> __('Search', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) ) ;
	}
	
	protected function get_the_header ( &$data_array ) {
		return ( __('Search Constituents', 'wp-issues-crm') );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->search_control() ); 
	}

	protected function get_the_legends( $sql = '' ) {
		$elements = WIC_DB_Dictionary::get_field_suffix_elements( $this->get_the_entity() );
		$legend = '';
		if ( $elements[1]->like_search_enabled ) {
			$legend = '<p class = "wic-form-legend">' . '(%) ' .  __( 'Full-text search conditionally enabled for these fields -- you can require strict match under search options. ' , 'wp-issues-crm' ) . '</p>';
		}	
		if ( $sql > '' ) {
			$legend .= 	'<p class = "wic-form-legend">' . __('Search SQL was:', 'wp-issues-crm' )	 .  $sql . '</p>';	
		}		
		return ( $legend );
	}
}