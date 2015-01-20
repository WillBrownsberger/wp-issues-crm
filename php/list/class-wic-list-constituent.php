<?php
/*
* File: class-wic-list-parent.php
*
* Description: lists entities (posts) passed as query 
* 
* @package wp-issues-crm
*
*/ 

class WIC_List_Constituent extends WIC_List_Parent {
	/*
	*
	*
	*
	*/
	protected function format_rows( &$wic_query, &$fields ) {
		$output = '';
		
				
		$line_count = 1;
		// convert the array objects from $wic_query into a string
  		$id_list = '(';
		foreach ( $wic_query->result as $result ) {
			$id_list .= $result->ID . ',';		
		} 	
  		$id_list = trim($id_list, ',') . ')';
   	
   	// create a new WIC access object and search for the id's
  		$wic_query2 = WIC_DB_Access_Factory::make_a_db_access_object( $wic_query->entity );
		$wic_query2->list_by_id ( $id_list ); 

		// check current user so can highlight assigned cases
		$current_user_id = get_current_user_id();
		
		// loop through the rows and output a list item for each
		foreach ( $wic_query2->result as $row_array ) {

			$row= '';
			$line_count++;
			
			// get row class alternating color marker
			$row_class = ( 0 == $line_count % 2 ) ? "pl-even" : "pl-odd";

			// add special row class to reflect case assigned status
			if ( $current_user_id == $row_array->case_assigned ) {
				$row_class .= " case-assigned ";
				if ( 1 == $row_array->case_status ) {
					$row_class .= " case-open ";	
					$review_date = new DateTime ( $row_array->case_review_date );
					$today = new DateTime( current_time ( 'Y-m-d') );
					$interval = date_diff ( $review_date, $today );
					if ( 0 == $interval->invert ) {
						$row_class .= " overdue ";				
						if ( 7 < $interval->days ) {
							$row_class .= " overdue long-overdue ";				
						}
					}
				} elseif ( 0 == $row_array->case_status ) {			
					$row_class .= " case-closed ";
				}	
			}			
			
			// $control_array['id_requested'] =  $wic_query->post->ID;
			$row .= '<ul class = "wic-post-list-line">';			
				foreach ( $fields as $field ) {
					// showing fields other than ID with positive listing order ( in left to right listing order )
					if ( 'ID' != $field->field_slug && $field->listing_order > 0 ) {
						// 						
						$class_name = 'WIC_Entity_' . $wic_query->entity;
						$formatter = $field->list_formatter;
						if ( method_exists ( $class_name, $formatter ) ) { 
							// note:  formatter MUST include esc_html on value unless known sanitized field like phone
							$display_value = $class_name::$formatter (  $row_array->{$field->field_slug} );
						} elseif ( function_exists ( $formatter ) ) {
							$display_value = $formatter (  $row_array->{$field->field_slug} );
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
	}

	protected function format_message( &$wic_query ) {
	
		if ( $wic_query->found_count < $wic_query->retrieve_limit ) {
			$header_message = sprintf ( __( 'Found %1$s constituents.', 'wp-issues-crm'), $wic_query->found_count );		
		} elseif ( $wic_query->found_count_real ) {
			$header_message = sprintf ( __( 'Found total of %1$s constituents, showing selected search maximum -- %2$s.', 'wp-issues-crm'),
				 $wic_query->found_count, $wic_query->showing_count ); 		
		} else {
			$header_message = sprintf ( __( 'Showing %1$s records -- changing search options may show more records.', 'wp-issues-crm' ),
				 $wic_query->showing_count );		
		}
		return $header_message;
	}

	protected function get_the_buttons( &$wic_query ) {
		$button = '<div id = "wic-list-button-row">' .
			'<button id = "wic-post-export-button" 
				name = "wic-post-export-button" 
				class = "wic-form-button" 
				type="submit" 
				value = "' . $wic_query->search_id  .'" >' . 
					__( 'Export All', 'wp-issues-crm' ) . 
			'</button>' . 
			'<button 	id = "wic-list-back-button" 
				class= "wic-form-button" 
				type="button" 
				onclick = "history.go(-1);return true;">' .
					 __( 'Go Back', 'wp-issues-crm' ) . 
				'</button>' .
			'</div>';
		return ( $button );
	}
 }	

