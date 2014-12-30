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
			array ( __( 'Constituents with Activities by Issue', 'wp-issues-crm' ), 'id' ),
			array ( __( 'Total', 'wp-issues-crm' ), 'total' ),   		
			array ( __( 'Pro', 'wp-issues-crm' ), 'pro' ),
			array ( __( 'Con', 'wp-issues-crm' ), 'con' ),
			array ( __( 'Categories', 'wp-issues-crm' ), 'post_category' ),
  		);
	
		$output .= '<ul class = "wic-post-list">' .  // open ul for the whole list
			'<li class = "pl-odd">' .							// header is a list item with a ul within it
				'<ul class = "wic-post-list-headers">';				
					foreach ( $fields as $field ) {
							$output .= '<li class = "wic-post-list-header pl-' . $wic_query->entity . '-' . $field[1] . '">' . $field[0] . '</li>';
						}			
		$output .= '</ul></li>'; // header complete
		$output .= $this->format_rows( $wic_query, $fields ); // format list item rows from child class	
		$output .= '</ul>'; // close ul for the whole list
		$output .= 	wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ) .
		'</form></div>'; 
		
		$output .= 	'<p class = "wic-list-legend">' . __('Search SQL was:', 'wp-issues-crm' )	 .  $wic_query->sql . '</p>';	

		return $output;
   } // close function 	
	
	
protected function format_rows( &$wic_query, &$fields ) {

		$output = '';
		$line_count = 1;

		// check current user so can highlight assigned cases
		$current_user_id = get_current_user_id();

		foreach ( $wic_query->result as $row_array ) {

			$row= '';
			$line_count++;
			$row_class = ( 0 == $line_count % 2 ) ? "pl-even" : "pl-odd";
			
			// add special row class to reflect case assigned status
			$issue_staff = get_post_meta ( $row_array->id, 'wic_data_issue_staff' );
			$issue_status = get_post_meta ( $row_array->id, 'wic_data_follow_up_status' );
			$issue_review_date = get_post_meta ( $row_array->id, 'wic_data_review_date' );
			
			if ( $current_user_id == $issue_staff[0] ) { 
				$row_class .= " case-assigned ";
				if ( 'open' == $issue_status[0] ) {
					$row_class .= " case-open ";
					if ( '' == $issue_review_date[0] ) {	
						$review_date = new DateTime ( '1900-01-01' );
					} else {
						$review_date = new DateTime ( $issue_review_date[0] );					
					}
					$today = new DateTime( current_time ( 'Y-m-d') );
					$interval = date_diff ( $review_date, $today );
					if ( 0 == $interval->invert ) {
						$row_class .= " overdue ";				
						if ( 7 < $interval->days ) {
							$row_class .= " overdue long-overdue ";				
						}
					}
				} elseif ( 0 == $issue_status[0] ) {			
					$row_class .= " case-closed ";
				}	
			}		

			$row .= '<ul class = "wic-post-list-line">';			
				foreach ( $fields as $field ) { 
					if ( 'id' != $field[1] && 'post_category' != $field[1] ) {
							$display_value = $row_array->$field[1];
						} elseif ( 'post_category' == $field[1] ) {
							$display_value =  esc_html( WIC_Entity_Issue::get_post_categories( $row_array->id ) );		
						} else {
							$display_value =  get_the_title ( $row_array->id );		
						}
						$row .= '<li class = "wic-post-list-field pl-' . $wic_query->entity . '-' . $field[1] . ' "> ';
							$row .=  $display_value ;
						$row .= '</li>';			
					}	

			$row .='</ul>';				
			
			$list_button_args = array(
					'entity_requested'	=> 'Issue',
					'action_requested'	=> 'id_search',
					'button_class' 		=> 'wic-post-list-button ' . $row_class,
					'id_requested'			=> $row_array->id,
					'button_label' 		=> $row,				
			);			
			$output .= '<li>' . WIC_Form_Parent::create_wic_form_button( $list_button_args ) . '</li>';	
		} // close for each row
		return ( $output );		
	} // close function 

	protected function format_message( &$wic_query ) {}
	protected function get_the_buttons( &$wic_query ) {}

 }	

