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
		return ( $this->create_wic_form_button ( $button_args_main ) ) ;
	}
	
	protected function get_the_header ( &$data_array ) {
		$title = $this->format_name_for_title ( $data_array );
		return ( sprintf ( __('Update %1$s' , 'wp-issues-crm'), $title ) );
	}

	protected function get_the_formatted_control ( $control ) {
		return ( $control->update_control() ); 
	}

	protected function get_the_legends( $sql = '' ) {

		$elements = WIC_DB_Dictionary::get_field_suffix_elements( $this->get_the_entity() );
		$legend = '';
		if ( reset( $elements )->required_individual ) {
			$legend =  '<p class = "wic-form-legend">' . '* ' . __('Required field.', 'wp-issues-crm' )	 . '</p>';
		}
		if ( reset( $elements )->required_group ) {
			$legend .=  '<p class = "wic-form-legend">' . '(+) ' . __('At least one among these fields must be supplied.', 'wp-issues-crm' )	 . '</p>';
		}
		
		if ( $sql > '' ) {
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
	
	protected function post_form_hook ( &$data_array ) {
		
		// extract $post_id	
		$post_id = $data_array['ID']->get_value();
		
		// retrieve ID's of constituents referencing this issue in activities or comments
		$args = array ( $post_id ); 
		$wic_comment_query = new WIC_Entity_Comment ( 'get_constituents_by_issue_id', $args ) ;
		
		// append the list to the form
		if ( 0 < $wic_comment_query->found_count ) {
			$lister = new WIC_List_Constituent;
			$list = $lister->format_entity_list( $wic_comment_query, false );
			echo $list;			 
		}	else {
			echo '<div id="no-activities-found-message">' . __( 'No comments or activities found for issue.', 'wp-issue-crm' ) . '</div>';
		} 
	}	
	
	
}