<?php
/*
* File: class-wic-list-parent.php
*
* Description: lists entities (posts) passed as query 
* 
* @package wp-issues-crm
*
*/ 

class WIC_List_Issue extends WIC_List_Parent {
	/*
	* return from wp_query actually has the full post content already, so not two-stepping through lists
	*
	*/
protected function format_rows( &$wic_query, &$fields ) {

		$output = '';
		$line_count = 1;

		foreach ( $wic_query->result as $row_array ) {
			$row= '';
			$line_count++;
			$row_class = ( 0 == $line_count % 2 ) ? "pl-even" : "pl-odd";
			// $control_array['id_requested'] =  $wic_query->post->ID;
			$row .= '<ul class = "wic-post-list-line">';			
				foreach ( $fields as $field ) { 
					if ( 'ID' != $field->field_slug ) {
						$class_name = 'WIC_Entity_' . $wic_query->entity;
						$formatter = $field->field_slug . '_formatter';
						if ( method_exists ( $class_name, $formatter ) ) { 
							// note:  formatter MUST include esc_html on value unless known sanitized field like phone
							$display_value = $class_name::$formatter (  $row_array->{$field->field_slug} );
						} elseif ( 'categories' == $field->field_slug ) {
							$display_value =  esc_html( $class_name::get_post_categories( $row_array->ID ) );		
						} else {
							$display_value =  esc_html( $row_array->{$field->field_slug} );		
						}
						$row .= '<li class = "wic-post-list-field pl-' . $wic_query->entity . '-' . $field->field_slug . ' "> ';
							$row .=  $display_value ;
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
		return ( $output );		
	} // close function 
 }	

