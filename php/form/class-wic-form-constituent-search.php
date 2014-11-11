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
			'button_label'					=> __('Search Constituent', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) ) ;
	}
	
	protected function get_the_header ( &$data_array ) {
		return ( __('Search Constituents', 'wp-issues-crm') );
	}

	protected function get_the_formatted_control ( $control ) {
		$args = array();
		return ( $control->search_control( $args ) ); 
	}

	protected function get_the_legends() {
		$elements = WIC_DB_Dictionary::get_field_suffix_elements( $this->get_the_entity() );
		if ( $elements[1]->like_search_enabled ) {
			$control_args = array ( 
				'field_slug'		=> 'strict_match',
				'field_label'			=>	'(%) ' . __( 'Full-text search conditionally enabled for these fields -- require strict match instead this time? ' , 'wp-issues-crm' ),
				'value'					=> 0,
			);
			$check_box = new WIC_Control_Checked;
		return ( '<p class = "wic-form-legend">' . $check_box->search_control ( $control_args ) . '</p>' );
		}
	}
}