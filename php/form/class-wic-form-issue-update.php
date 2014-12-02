<?php
/*
*
*  class-wic-search-form.php
*
*/

class WIC_Form_Issue_Update extends WIC_Form_Parent  {
	
	protected function get_the_entity() {
		return ( 'issue' );	
	}

	protected function get_the_buttons () {
		$button_args_main = array(
			'entity_requested'			=> 'issue',
			'action_requested'			=> 'form_update',
			'button_label'					=> __('Update issue', 'wp-issues-crm')
		);	
		return ( $this->create_wic_form_button ( $button_args_main ) . parent::backbutton (' second-position' ) ) ;
	}
	
	protected function format_message ( &$data_array, $message ) {
		$title = $this->format_name_for_title ( $data_array );
		return ( sprintf ( __('Update %1$s. ' , 'wp-issues-crm'), $title ) . $message );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->update_control() ); 
	}

	protected function get_the_legends( $sql = '' ) {

		$legend = '';
	
		$individual_required_string = WIC_DB_Dictionary::get_required_string( "issue", "individual" );
		if ( '' < $individual_required_string ) {
			$legend =   __('Required for save/update: ', 'wp-issues-crm' ) . $individual_required_string . '. ';
		}
		
		$group_required_string = WIC_DB_Dictionary::get_required_string( "issue", "group" );
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
		
		$title = $data_array['post_title']->get_value();
		
		return  ( $title );
	}

	protected function pre_button_messaging( &$data_array ) {
		edit_post_link( __( 'Edit this Post in Wordpress editor.', 'wp-issues-crm' ), '<div id = "wic-issue-post-edit-link">', '</div>', $data_array['ID']->get_value() ) ;
	}	
	
	
	protected function group_screen( $group ) {
		return ( ! ( 1 == $group->search_only ) );	
	}
	
}