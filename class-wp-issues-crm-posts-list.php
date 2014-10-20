<?php
/*
* File: class-wp-issues-crm-posts-lisst.php
*
* Description: lists entities (posts) passed as query 
* 
* @package wp-issues-crm
*
*/ 

class WP_Issues_CRM_Posts_List {
	/*
	*
	* field definitions for ready reference array 
	*
	*/
		
	private $list_fields = array();
	public $post_list;

	public function __construct( &$wic_query, $fields_array, $entity_type, $referring_parent, $show_top_buttons ) {
		
		foreach ( $fields_array as $field )
			if ( $field['list'] > 0 ) { 		
 				 array_push( $this->list_fields, $field );
 			}
		$this->post_list = $this->format_post_list( $wic_query, $entity_type, $referring_parent, $show_top_buttons );
	}


  public function format_post_list( &$wic_query, $entity_type, $referring_parent, $show_top_buttons ) {
  	
  	
  		$list_button_args = array(
			'form_requested'			=> $entity_type,
			'action_requested'		=> 'search',
			'id_requested'				=> '',
			'referring_parent'		=> $referring_parent,
			'omit_label_and_close_tag' => true,
		);	
  	
  		global $wic_base_definitions;
 		global $wic_form_utilities;
		$output = '<div id="wid-post-list"><form method="POST">' . 
			'<div class = "wic-post-field-group wic-group-odd">';
		
		/* check if have any select fields to decode and assemble array of their arrays */
		
		$array_of_select_arrays = array();
		foreach ( $this->list_fields as $field ) { // doing this just for economy -- running list, don't want to recreate array for each row
			if ( 'select' == $field['type'] && ! isset ( $field['list_call_back_id'] ) && ! isset( $field['list_call_back_key'] ) ) {
				$select_array = $wic_form_utilities->format_select_array( $field['select_array'] , 'lookup', '' );
				$array_of_select_arrays[$field['slug']] = $select_array;
			}
		} 		
			
		if ( $show_top_buttons ) {	
			$output .=	'<h2> Found ' . $wic_query->found_posts . ' records, showing ' . $wic_query->post_count . '</h2>' . 
				'<button id = "form-toggle-button-on-list" type="button" onclick = "togglePostForm()">' . __( 'Show Search', 'wp-issues-crm' ) . '</button>' .
				'<button id = "post-export-button" class = "wic-form-button" type="button" >' . __( 'Export (not built yet)', 'wp-issues-crm' ) . '</button>' .
				'</div>';
		}

		$line_count = 1;	
		$output .= '<ul class = "wic-post-list">' .  // out ul for the list
			'<li class = "pl-odd">' .							// header is a list item with a ul within it
				'<ul class = "wic-post-list-headers">';				
					foreach ( $this->list_fields as $field ) {
						$output .= '<li class = "wic-post-list-header pl-' . $entity_type . '-' . $field['slug'] . '">' . $field['label'] . '</li>';			
					}
			$output .= '</ul></li>';

			while ( $wic_query->have_posts() ) {
				$wic_query->next_post();
				
				$line_count++;
				$row_class = ( 0 == $line_count % 2 ) ? "pl-even" : "pl-odd";
				// $control_array['id_requested'] =  $wic_query->post->ID;
				$list_button_args['button_class'] = 'wic-post-list-button ' . $row_class;
				$list_button_args['id_requested'] = $wic_query->post->ID;
				$output .= '<li>' . $wic_form_utilities->create_wic_form_button($list_button_args);		
				$output .= '<ul class = "wic-post-list-line">';			
					foreach ( $this->list_fields as $field ) {
						$wp_query_parameter = isset ( $field['wp_query_parameter'] ) ? $field['wp_query_parameter'] : '';							
						if ( '' == $wp_query_parameter ) {
							$key = $wic_base_definitions->wic_metakey . $field['slug'];
						} else {
							$key = $wic_base_definitions->wp_query_parameters[$field['wp_query_parameter']]['update_post_parameter'];						
						} 
						$output .= '<li class = "wic-post-list-field pl-' . $entity_type . '-' . $field['slug'] . ' "> ';
						if ( isset ( $wic_query->post->$key ) ) {
							if ( ! is_array ( $wic_query->post->$key ) ) { 
								if ( 'select' == $field['type'] && ! isset( $field['list_call_back_key'] ) && ! isset( $field['list_call_back_id'] ) ) {
									$output .= esc_html ( $array_of_select_arrays[$field['slug']][$wic_query->post->$key] );
								} elseif ( isset( $field['list_call_back_key'] ) ) {
									$output .= esc_html ( $wic_form_utilities->$field['list_call_back_key']( $wic_query->post->$key ) );
								} elseif ( isset( $field['list_call_back_id'] ) ) { 
									$output .= esc_html ( $wic_form_utilities->$field['list_call_back_id']( $wic_query->post->ID ) );
								} else {
									$output .= esc_html ( $wic_query->post->$key );
								}
							} else {
								// NB $wic_query->post->$key)[0][1] gets evaluated to whole ->post array b/c $key[0][1] evaluates to empty; 
								// cannot fix with parens, so two step this
								$row_array = $wic_query->post->$key;
								if ( 'phones' == $field['type'] ) { 
									$output .= $wic_form_utilities->format_phone($row_array[0][1]);
								} else {
									$output .= esc_html ( $row_array[0][1] );								
								}							
  							}
						}							
						$output .= '</li>';			
					}
			$output .='</ul></button></li>';
		}		
		$output .= '</ul>';
		$output .= 	wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ) .
		'</form></div>'; 
		
		return $output;
   } // close function
}	

