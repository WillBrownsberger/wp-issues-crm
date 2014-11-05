<?php
/*
*
*  class-wic-search-form.php
*
*/

class WIC_Constituent_Search_Form extends WIC_Form  {
	
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
	
	protected function get_the_header () {
		return ( __('Search Constituents', 'wp-issues-crm') );
	}

	protected function get_the_control ( $control_args ) {
		extract ( $control_args );
		$class_name = 'WIC_' . initial_cap ( $field_type ) . '_Control';
		return ( $class_name::search_control ( $control_args ) ) ; 
	}

	protected function get_the_legends() {
		$elements = WIC_Data_Dictionary::get_field_suffix_elements( $this->get_the_entity() );
		if ( $elements[1]->like_search_enabled ) {
			$control_args = array ( 
				'field_slug'		=> 'strict_match',
				'field_label'			=>	'(%) ' . __( 'Full-text search conditionally enabled for these fields -- require strict match instead this time? ' , 'wp-issues-crm' ),
				'value'					=> 0,
			);
		return ( '<p class = "wic-form-legend">' . WIC_Checked_Control::search_control ( $control_args ) . '</p>' );
		}
	}
}