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

		// convert the array objects from $wic_query into a string
  		$id_list = '(';
		foreach ( $wic_query->result as $result ) {
			$id_list .= $result->ID . ',';		
		} 	
  		$id_list = trim($id_list, ',') . ')';
   	
   	// create a new WIC access object and search for the id's
  		$wic_query2 = WIC_DB_Access_Factory::make_a_db_access_object( $wic_query->entity );
		$wic_query2->list_by_id ( $id_list ); 

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
				'<button id = "form-toggle-button-on-list" type="button" onclick = "togglePostForm()">' . __( 'Show Search', 'wp-issues-crm' ) . '</button>' .
				'<button id = "post-export-button" class = "wic-form-button" type="button" >' . __( 'Export (not built yet)', 'wp-issues-crm' ) . '</button>' .
				'</div>';
		}
		// prepare the list fields for header set up and list formatting
  		$fields =  WIC_DB_Dictionary::get_list_fields_for_entity( $wic_query->entity );
  	
		$line_count = 1;	
		$output .= '<ul class = "wic-post-list">' .  // out ul for the list
			'<li class = "pl-odd">' .							// header is a list item with a ul within it
				'<ul class = "wic-post-list-headers">';				
					foreach ( $fields as $field ) {
						if ( $field->field_slug != 'ID' ) {
							$output .= '<li class = "wic-post-list-header pl-' . $wic_query->entity . '-' . $field->field_slug . '">' . $field->field_label . '</li>';
						}			
					}
			$output .= '</ul></li>';

			foreach ( $wic_query2->result as $row_array ) {

				$row= '';
				$line_count++;
				$row_class = ( 0 == $line_count % 2 ) ? "pl-even" : "pl-odd";
				// $control_array['id_requested'] =  $wic_query->post->ID;
				$row .= '<ul class = "wic-post-list-line">';			
					foreach ( $fields as $field ) {
						if ( 'ID' != $field->field_slug ) {
							$row .= '<li class = "wic-post-list-field pl-' . $wic_query->entity . '-' . $field->field_slug . ' "> ';
								$row .= esc_html ( $row_array->{$field->field_slug} );
							$row .= '</li>';			
						}	
					}
				$row .='</ul>';				
				
				$list_button_args = array(
						'entity_requested'	=> $wic_query->entity,
						'action_requested'	=> 'id_search',
						'button_class' 		=> 'wic-post-list-button ' . $row_class,
						'id_requested'			=> $row_array->ID,
						'button_label' 		=> $row,				
				);			
				$output .= '<li>' . WIC_Form_Parent::create_wic_form_button( $list_button_args ) . '</li>';		
			}		
		$output .= '</ul>';
		$output .= 	wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ) .
		'</form></div>'; 
		
		$output .= 	'<p class = "wic-list-legend">' . __('Search SQL was:', 'wp-issues-crm' )	 .  $wic_query->sql . '</p>';	

		return $output;
   } // close function
}	

