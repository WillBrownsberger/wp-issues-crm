<?php
/*
*
*  class-wic-search-form.php
*
*/

class WIC_Form_Constituent_Update extends WIC_Form_Parent  {
	
	protected function get_the_entity() {
		return ( 'constituent' );	
	}

	protected function get_the_buttons () {
		$button_args_main = array(
			'entity_requested'			=> 'constituent',
			'action_requested'			=> 'form_update',
			'button_label'					=> __('Update', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) ) ;
	}
	
	protected function format_message ( &$data_array, $message ) {
		$title = $this->format_name_for_title ( $data_array );
		$formatted_message = sprintf ( __('Update %1$s. ' , 'wp-issues-crm'), $title )  . $message;
		return ( $formatted_message );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->update_control() ); 
	}

	protected function get_the_legends( $sql = '' ) {

		$legend = '';
	
		$individual_required_string = WIC_DB_Dictionary::get_required_string( "constituent", "individual" );
		if ( '' < $individual_required_string ) {
			$legend =   __('Required for save/update: ', 'wp-issues-crm' ) . $individual_required_string . '. ';
		}
		
		$group_required_string = WIC_DB_Dictionary::get_required_string( "constituent", "group" );
		if ( '' < $group_required_string ) {
			$legend .=   __('At least one among these fields must be supplied: ', 'wp-issues-crm' ) . $group_required_string . '. ';
		}

		if ( '' < $legend ) {
			$legend = '<p class = "wic-form-legend">' . $legend . '</p>';		
		}
		
		if ( '' < $sql ) {
			$legend .= 	'<p class = "wic-form-legend">' . __('Search SQL was:', 'wp-issues-crm' )	 .  $sql . '</p>';	
		}
		return  $legend;
	}
	
	protected function format_name_for_title ( &$data_array ) {
		
		// construct title starting with first name
		$title = 	$data_array['first_name']->get_value(); 
		// if present, add last name, with a space if also have first name		
		$title .= 	( '' == $data_array['last_name']->get_value() ) ? '' : ( ( $title > '' ? ' ' : '' ) . $data_array['last_name']->get_value() );
		// if still empty and email may be available, add email 	
		if ( '' == $title && isset( $data_array['email']->get_value()[0] ) ) {
			$title = $data_array['email']->get_value()[0]->get_email_address();
		} 
		// if still empty, insert word constitent
		$title =		( '' == $title ) ? __( 'Constituent', 'wp-issues-crm' ) : $title;
		
		return  ( $title );
	}
	
	protected function group_screen( $group ) {
		return ( ! ( 1 == $group->search_only ) );	
	}
	
	protected function group_special ( $group ) {
		return ( 'comment' == $group );	
	}
	
	protected function group_special_comment ( &$doa ) {
		return ( WIC_Entity_Comment::create_comment_list ( $doa ) ); 					
	}
}