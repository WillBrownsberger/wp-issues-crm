<?php
/*
* File: class-wic-list-parent.php
*
* Description: lists entities (posts) passed as query 
* 
* @package wp-issues-crm
*
*/ 

class WIC_List_Parent {
	/*
	*
	* field definitions for ready reference array 
	*
	*/
		
  public function format_entity_list( &$wic_query, $show_top_buttons ) {


		// set up args for use in buttons -- each row is a button
  		$list_button_args = array(
			'entity_requested'		=> $wic_query->entity,
			'action_requested'		=> 'id_search',
		);	
  	
  		// set up form
		$output = '<div id="wid-post-list"><form method="POST">' . 
			'<div class = "wic-post-field-group wic-group-odd">';
			
		if ( $wic_query->found_count < $wic_query->retrieve_limit ) {
			$header_message = sprintf ( __( 'Found total of %1$s records', 'wp-issues-crm'), $wic_query->found_count );		
		} elseif ( $wic_query->found_count_real ) {
			$header_message = sprintf ( __( 'Found total of %1$s records, showing search optional maximum -- %2$s.', 'wp-issues-crm'),
				 $wic_query->found_count, $wic_query->showing_count  ); 		
		} else {
			$header_message = sprintf ( __( 'Showing %1$s records -- changing search options may show more records.', 'wp-issues-crm' ),
				 $wic_query->showing_count );		
		}

		if ( $show_top_buttons ) {	
			$output .=	'<h2>' . $header_message  . '</h2>' . 
				'<button id = "form-toggle-button-on-list" type="button" onclick = "history.go(-1);return true;">' . __( 'Revise Search', 'wp-issues-crm' ) . '</button>' .
				'<button id = "post-export-button" class = "wic-form-button" type="button" >' . __( 'Export (not built yet)', 'wp-issues-crm' ) . '</button>' .
				'</div>';
		}
		// prepare the list fields for header set up and list formatting
  		$fields =  WIC_DB_Dictionary::get_list_fields_for_entity( $wic_query->entity );
	
		$output .= '<ul class = "wic-post-list">' .  // open ul for the whole list
			'<li class = "pl-odd">' .							// header is a list item with a ul within it
				'<ul class = "wic-post-list-headers">';				
					foreach ( $fields as $field ) {
						if ( $field->field_slug != 'ID' ) {
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

