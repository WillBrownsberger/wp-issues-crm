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
	
	protected function format_message ( &$data_array, $message ) {
		return ( __('Search constituents. ', 'wp-issues-crm') . $message );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->search_control() ); 
	}

	protected function get_the_legends( $sql = '' ) {

		$legend = '';
	
		$soundex_string = WIC_DB_Dictionary::get_match_type_string( "constituent", "2" );
		if ( '' < $soundex_string ) {
			$legend =  sprintf ( __( '%s will be searched using Soundex matching. ', 'wp-issues-crm' ), $soundex_string );
		}
		
		$like_string = WIC_DB_Dictionary::get_match_type_string( "constituent", "1" );
		if ( '' < $like_string ) {
			$legend .=  sprintf ( __( '%s will be searched using right wild card matching. ', 'wp-issues-crm' ), $like_string );
		}

		if ( '' < $legend ) {
			$legend = '<p class = "wic-form-legend">' . $legend . 
					__( 'These settings may be overridden -- set search options on this screen. 
						Text area fields ( like activity notes ) are	always searched using a full text scan.', 
						'wp-issues-crm') 
			. '</p>';		
		}

		return  $legend;
	}
	
	protected function group_screen( $group ) {
		return ( ! ( 1 == $group->save_update_only ) );	
	}	
	
	
}