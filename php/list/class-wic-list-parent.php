<?php
/*
* File: class-wic-list-parent.php
*
* Description: lists entities (posts) passed as query 
* 
* @package wp-issues-crm
*
*/ 

abstract class WIC_List_Parent {
	/*
	*
	* field definitions for ready reference array 
	*
	*/
	
	protected abstract function get_the_buttons( &$wic_query );	
	protected abstract function format_message( &$wic_query );
		
	public function format_entity_list( &$wic_query, $show_top_buttons ) {

		global $wic_db_dictionary;

  		// set up form
		$output = '<div id="wic-post-list"><form method="POST">' . 
			'<div class = "wic-post-field-group wic-group-odd">';

		$message = $this->format_message ( $wic_query ); 
		$output .= '<div id="post-form-message-box" class = "wic-form-routine-guidance" >' . esc_html( $message ) . '</div>';
		$output .=  $this->get_the_buttons( $wic_query );	

		// set up args for use in row buttons -- each row is a button
  		$list_button_args = array(
			'entity_requested'		=> $wic_query->entity,
			'action_requested'		=> 'id_search',
		);	

		// prepare the list fields for header set up and list formatting
  		$fields =  $wic_db_dictionary->get_list_fields_for_entity( $wic_query->entity );
	
		$output .= '<ul class = "wic-post-list">' .  // open ul for the whole list
			'<li class = "pl-odd">' .							// header is a list item with a ul within it
				'<ul class = "wic-post-list-headers">';				
					foreach ( $fields as $field ) {
						if ( $field->field_slug != 'ID' && $field->listing_order > 0 ) {
							$output .= '<li class = "wic-post-list-header pl-' . $wic_query->entity . '-' . $field->field_slug . '">' . $field->field_label . '</li>';
						}			
					}
			$output .= '</ul></li>'; // header complete
		$output .= $this->format_rows( $wic_query, $fields ); // format list item rows from child class	
		$output .= '</ul>'; // close ul for the whole list
		$output .= 	wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ) .
		'</form></div>'; 
		
		$output .= 	'<p class = "wic-list-legend">' . __('Search SQL was:', 'wp-issues-crm' )	 .  $wic_query->sql . '</p>';	

		return $output;
   } // close function
}	

