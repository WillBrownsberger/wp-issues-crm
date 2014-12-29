<?php
/*
* File: class-wic-list-trend.php
*
* Description: lists entities (posts) passed as query 
* 
* @package wp-issues-crm
*
*/ 

class WIC_List_Trend extends WIC_List_Parent {
	/*
	* Need to two step list and also to format header for this hybrid entity, so can't use much of parent functions
	*
	*/
	
	public function format_entity_list( &$wic_query, $show_top_buttons ) {

  		// set up form
		$output = '<div id="wic-post-list"><form method="POST">' . 
			'<div class = "wic-post-field-group wic-group-odd">';


		// prepare the custom/mixed list fields for header set up and list formatting
  		$fields = array (
			array ( __( 'Issue', 'wp-issues-crm' ), 'id' ),
			array ( __( 'Constituents: Total', 'wp-issues-crm' ), 'total' ),   		
			array ( __( 'Pro', 'wp-issues-crm' ), 'pro' ),
			array ( __( 'Con', 'wp-issues-crm' ), 'con' ),
			array ( __( 'Categories', 'wp-issues-crm' ), 'cats' ),
  		)
	
		$output .= '<ul class = "wic-post-list">' .  // open ul for the whole list
			'<li class = "pl-odd">' .							// header is a list item with a ul within it
				'<ul class = "wic-post-list-headers">';				
					foreach ( $fields as $field ) {
							$output .= '<li class = "wic-post-list-header pl-' . $wic_query->entity . '-' . $field[1] . '">' . $field[0] . '</li>';
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
	
	
	
next: rewrite this to track field list and output actions based on slug (if cats, else)
protected function format_rows( &$wic_query, &$fields ) {

		$output = '';
		$line_count = 1;

		// check current user so can highlight assigned cases
		$current_user_id = get_current_user_id();


		foreach ( $wic_query->result as $row_array ) {
			var_dump ($row_array);
			$row= '';
			$line_count++;
			$row_class = ( 0 == $line_count % 2 ) ? "pl-even" : "pl-odd";
			
			// add special row class to reflect case assigned status
			if ( $current_user_id == $row_array->issue_staff ) {
				$row_class .= " case-assigned ";
				if ( 'open' == $row_array->follow_up_status ) {
					$row_class .= " case-open ";
					if ( '' == $row_array->review_date ) {	
						$review_date = new DateTime ( '1900-01-01' );
					} else {
						$review_date = new DateTime ( $row_array->review_date );					
					}
					$today = new DateTime( current_time ( 'Y-m-d') );
					$interval = date_diff ( $review_date, $today );
					if ( 0 == $interval->invert ) {
						$row_class .= " overdue ";				
						if ( 7 < $interval->days ) {
							$row_class .= " overdue long-overdue ";				
						}
					}
				} elseif ( 0 == $row_array->follow_up_status ) {			
					$row_class .= " case-closed ";
				}	
			}		

			$row .= '<ul class = "wic-post-list-line">';			
				foreach ( $fields as $field ) { 
					if ( 'ID' != $field->field_slug && 0 < $field->listing_order ) {
						$class_name = 'WIC_Entity_' . $wic_query->entity;
						$formatter = $field->field_slug . '_formatter';
						if ( method_exists ( $class_name, $formatter ) ) { 
							// note:  formatter MUST include esc_html on value unless known sanitized field like phone
							$display_value = $class_name::$formatter (  $row_array->{$field->field_slug} );
						} elseif ( 'post_category' == $field->field_slug ) {
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

	protected function format_message( &$wic_query ) {}
	protected function get_the_buttons( &$wic_query ) {}

 }	

