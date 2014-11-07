<?php
/*
* File: class-wic-entity-list.php
*
* Description: lists entities (posts) passed as query 
* 
* @package wp-issues-crm
*
*/ 

class WIC_Entity_List {
	/*
	*
	* field definitions for ready reference array 
	*
	*/
		
  public function format_entity_list( &$wic_query, $show_top_buttons ) {
  	
  		$fields =  WIC_Data_Dictionary::get_list_fields_for_entity( $wic_query->entity );
  	
  		$list_button_args = array(
			'entity_requested'		=> $wic_query->entity,
			'action_requested'		=> 'id_search',
		);	
  	
		$output = '<div id="wid-post-list"><form method="POST">' . 
			'<div class = "wic-post-field-group wic-group-odd">';
		
		/* check if have any select fields to decode and assemble array of their arrays */
		
	
			
		if ( $show_top_buttons ) {	
			$output .=	'<h2> Found ' . $wic_query->outcome . ' records, showing ' . $wic_query->outcome . '</h2>' . 
				'<button id = "form-toggle-button-on-list" type="button" onclick = "togglePostForm()">' . __( 'Show Search', 'wp-issues-crm' ) . '</button>' .
				'<button id = "post-export-button" class = "wic-form-button" type="button" >' . __( 'Export (not built yet)', 'wp-issues-crm' ) . '</button>' .
				'</div>';
		}

		$line_count = 1;	
		$output .= '<ul class = "wic-post-list">' .  // out ul for the list
			'<li class = "pl-odd">' .							// header is a list item with a ul within it
				'<ul class = "wic-post-list-headers">';				
					foreach ( $this->list_fields as $field ) {
						$output .= '<li class = "wic-post-list-header pl-' . $wic_query->entity . '-' . $field->field_slug . '">' . $field->field_label . '</li>';			
					}
			$output .= '</ul></li>';

			foreach ( $wic_query->results as $entity ) {
				
				$line_count++;
				$row_class = ( 0 == $line_count % 2 ) ? "pl-even" : "pl-odd";
				// $control_array['id_requested'] =  $wic_query->post->ID;
				$list_button_args['button_class'] = 'wic-post-list-button ' . $row_class;
				$list_button_args['id_requested'] = $post->ID;
				$output .= '<li>' . $wic_form_utilities->create_wic_form_button($list_button_args);		
			
			$output .= '<ul class = "wic-post-list-line">';			
					foreach ( $fields as $field ) {
						$output .= '<li class = "wic-post-list-field pl-' . $entity_type . '-' . $field['slug'] . ' "> ';
						if ( isset ( $post->$key ) ) {
								$output .= esc_html ( $post->$key );
						}							
						$output .= '</li>';			
					}
			$output .='</ul>
			
			</li>';
		}		
		$output .= '</ul>';
		$output .= 	wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ) .
		'</form></div>'; 
		
		return $output;
   } // close function
}	

