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
			'form_requested'			=> 'Constituent',
			'action_requested'		=> 'search',
			'button_label'				=> __('Search Constituent', 'wp-issues-crm')
		);	
		$this->create_wic_form_button ( $button_args_main );
	}
	
	protected function get_the_header () {
		return ( __('Search Constituents', 'wp-issues-crm') );
	}

	
	protected function the_controls ( $fields, &$data_array ) {
	
		foreach ( $fields as $field ) {
			$control_args = array(
				'field_name_id' 			=> $field->field_slug,
				'field_label' 				=> $field->field_label,
				'like_search_enabled'	=> $field->like_search_enabled,
				'value' 						=> $data_array[$field->field_slug],
			);
			$class_name = 'WIC_' . initial_cap ( $field->field_type ) . '_Control';
			echo '<p>' . $class_name::search_control ( $control_args ) . '</p>';
		}	

	}

	protected function get_the_legends() {
		$elements = WIC_Data_Dictionary::get_field_suffix_elements( $this->get_the_entity() );
		if ( $elements[0]->like ) {
			$control_args = array ( 
				'field_name_id'		=> 'strict_match',
				'field_label'			=>	'(%) ' . __( 'Full-text search conditionally enabled for these fields -- require strict match instead this time? ' , 'wp-issues-crm' ),
				'value'					=> 0,
			);
			return ( '<p class = "wic-form-legend">' . $wic_form_utilities->create_check_control ( $control_args ) . '</p>' );
		}
	}
}