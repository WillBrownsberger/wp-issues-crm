					}
				} else { // non array for serialized field is only from a search -- compress/sanitize, but not validate
					if ( 'phones' == $field['slug'] ) {
						$clean_input[$field['slug']] = preg_replace("/[^0-9]/", '', $_POST[$field['slug']] );
					} else {
						$clean_input[$field['slug']] = stripslashes( sanitize_text_field( $_POST[$field['slug']] ) );
					}
				} // close non-array for serialized fields
			} elseif ( 'multi_select' == $field['type'] ) { 
				$clean_input[$field['slug']] = array();
					if( isset ( $_POST[$field['slug']] ) ) {
						foreach ( $_POST[$field['slug']] as $key => $value ) {
							$clean_input[$field['slug']][] = $key; 					
						}
					}
			}
			

			// add date hi-lo ranges to array and standardize all dates to yyyy-mm-dd 
			$readonly_subtype = isset ( $field['readonly_subtype'] ) ? $field['readonly_subtype'] : '';
			if ( 'date' == $field['type'] || 'date' == $readonly_subtype ) {
				$clean_input[$field['slug'] . '_lo' ] = isset( $_POST[$field['slug'] . '_lo' ] ) ? stripslashes( sanitize_text_field( $_POST[$field['slug'] . '_lo' ] ) ) : '';			
				$clean_input[$field['slug'] . '_hi' ] = isset( $_POST[$field['slug'] . '_hi' ] ) ? stripslashes( sanitize_text_field( $_POST[$field['slug'] . '_hi' ] ) ) : '';
				if ( $clean_input[$field['slug']] > '' ) {
					$clean_input[$field['slug']]  = $this->validate_date( $clean_input[$field['slug']] );
					if ( '' == $clean_input[$field['slug']] ) {
						$clean_input['error_messages'] .= $field['label'] .__( ' had unsupported date format -- yyyy-mm-dd will work. ', 'wp-issues-crm' );					
					}
				} 
				if ( $clean_input[$field['slug'] . '_lo' ]  > '' ) {
					$clean_input[$field['slug'] . '_lo' ]  = $this->validate_date( $clean_input[$field['slug'] . '_lo' ] );
					if ( '' == $clean_input[$field['slug'] . '_lo' ] ) {
						$clean_input['search_notices'] .= $field['label'] . ' (low) ' . __( ' had unsupported date format -- yyyy-mm-dd will work. ', 'wp-issues-crm' );					
					}
				}				
				if ( $clean_input[$field['slug'] . '_hi' ]  > '' ) {
					$clean_input[$field['slug']  . '_hi' ]  = $this->validate_date( $clean_input[$field['slug'] . '_hi' ] );
					if ( '' == $clean_input[$field['slug'] . '_hi' ] ) {
						$clean_input['search_notices'] .= $field['label'] . ' (high) ' . __( ' had unsupported date format -- yyyy-mm-dd will work. ', 'wp-issues-crm' );					
					}
				}							
			}		
			
			// do test for group required (including first among any repeater fields)
			if ( 'group' == $field['required'] ) {
				$group_required_test .=	is_array ( $clean_input[$field['slug']] ) ? $clean_input[$field['slug']][0][1] : $clean_input[$field['slug']] ;
				$group_required_label .= ( '' == $group_required_label ) ? '' : ', ';	
				$group_required_label .= $field['label'];	
			}

			// do individual field required tests and for non-blank to email validation
			if ( ! $clean_input[$field['slug']] > ''  ) { // note array always > '' and we do not store blank arrays, so this suffices for the array fields 
				if( 'individual' == $field['required'] ) {
					$clean_input['error_messages'] .= ' ' . sprintf( __( ' %s is a required field. ' , 'wp-issues-crm' ), $field['label'] );				
				}   		
   		}
   	}
		
		// outside the loop -- test group requires after all fields passed 
		if ( '' == $group_required_test && $group_required_label > '' ) {
			$clean_input['error_messages'] .= sprintf ( __( ' At least one among %s is required. ', 'wp-issues-crm' ), $group_required_label );
   	}

		$clean_input['wic_post_content'] = isset ( $_POST['wic_post_content'] ) ? stripslashes ( ( $_POST['wic_post_content'] ) ) : '' ;
		$clean_input['old_wic_post_content'] = isset ( $_POST['old_wic_post_content'] ) ? stripslashes ( $_POST['old_wic_post_content'] ) : '' ;
   	$clean_input['wic_post_id'] = absint ( $_POST['wic_post_id'] ); // always included in form; 0 if unknown;
		$clean_input['strict_match']	=	isset( $_POST['strict_match'] ) ? true : false; // only updated on the form; only affects search_wic_posts
		$clean_input['initial_form_state'] = 'wic-form-open';	
		
   } 
	/*
	* date sanitization function
	*
	*/   
	public function validate_date ( $possible_date ) {
		try {
			$test = new DateTime( $possible_date );
		}	catch ( Exception $e ) {
			return ( '' );
		}	   			
 		return ( date_format( $test, 'Y-m-d' ) );
	}
   

	
	/*
	*
	*	The following group of functions create generic controls -- no field-specific logic
	*		 -- checked, text, text_area, selected
	*/	
	
	
	public function create_check_control ( $control_args ) {
		
		/* control args = array (
			'field_name_id' 		=> name/id
			'field_label'			=>	label for field
			'label_class'			=> for css
			'value'					=> from database or blank
			'read_only_flag'		=>	whether should be a read only -- true false
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)								
		);	
		*/			

		$read_only_flag 		= false; 				
		$field_label_suffix 	= '';
		$label_class = 'wic-label';
		$input_class = 'wic_input_checked';

		
		extract ( $control_args, EXTR_OVERWRITE ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ?  '<label class="' . $label_class . '" for="' . 
				esc_attr( $field_name_id ) . '">' . esc_html( $field_label ) . ' ' . '</label>' : '';
		$control .= '<input class="' . $input_class . '"  id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) . 
			'" type="checkbox"  value="1"' . checked( $value, 1, false) . $readonly  .'/>' . 
			$field_label_suffix_span  ;	

		return ( $control );

	}
	
	
	public function create_text_area_control ( $control_args ) {
		
		/* control args = array (
			'field_name_id' 		=> name/id
			'field_label'			=>	label for field
			'label_class'			=> for css
			'input_class'			=>	for css
			'placeholder'			=> placeholder in input field
			'value'					=> from database or blank
			'read_only_flag'		=>	whether should be a read only -- true false
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)								
		);	
		*/			

		$read_only_flag 		= false; 				
		$field_label_suffix 	= '';
		$label_class = 'wic-label';
		$input_class = 'wic-input';
		$placeholder = '';

		
		extract ( $control_args, EXTR_OVERWRITE ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . esc_attr( $field_label ) . '</label>' : '' ;
		$control .= '<textarea class="' . $input_class . '" id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) . '" type="text" placeholder = "' . 
			esc_attr( $placeholder ) . '" ' . $readonly  . '/>' . esc_textarea( $value ) . '</textarea>' . $field_label_suffix_span;
			
		return ( $control );

	}	
	
	
	public function create_select_control ( $control_args ) {
		
		/* $control_args = array (
			'field_name_id' => name/id
			'field_label'	=>	label for field
			'placeholder' => label that will appear in drop down for empty string
			'value'		=> initial value 
			'label_class'			=> for css
			'field_input_class'			=> for css
			'select_array'	=>	the options for the selected -- key value array with keys 'value' and 'label' 
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)
		*/								

		$label_suffix = '';
		$value = '';
		$label_class = 'wic-label';
		$field_input_class = 'wic-input';
		$placeholder = '';
	
		$value = esc_html ( $value ); 

		extract ( $control_args, EXTR_OVERWRITE ); 
		
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';

		$control = '';
				
		$not_selected_option = array (
			'value' 	=> '',
			'label'	=> $placeholder,								
		);  
		$option_array =  $select_array;
		array_push( $option_array, $not_selected_option );
		
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . 
				esc_html( $field_label ) . '</label>' : '';
		$control .= '<select class="' . $field_input_class . '" id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) 
				. '" >' ;
		$p = '';
		$r = '';
		foreach ( $option_array as $option ) {
			$label = $option['label'];
			if ( $value == $option['value'] ) { // Make selected first in list
				$p = '<option selected="selected" value="' . esc_attr( $option['value'] ) . '">' . esc_html ( $label ) . '</option>';
			} else {
				$r .= '<option value="' . esc_attr( $option['value'] ) . '">' . esc_html( $label ) . '</option>';
			}
		}
		$control .=	$p . $r .	'</select>' . $field_label_suffix_span;	
	
		return ( $control );
	
	}	

	public function create_multi_select_control ( $control_args ) {
		
		/* $control_args = array (
			'field_name_id' => name/id
			'field_label'	=>	label for field
			'placeholder' => label that will appear in drop down for empty string
			'value'		=> initial value 
			'label_class'			=> for css
			'field_input_class'			=> for css
			'select_array'	=>	the options for the selected -- key value array with keys 'value' and 'label' 
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)
		*/								

		$label_suffix = '';
		$value = '';
		$label_class = 'wic-multi-select-group-label';
		$field_input_class = 'wic-input';
		$placeholder = '';
		$value = array(); 

		extract ( $control_args, EXTR_OVERWRITE ); 
		
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';

		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . esc_attr( $field_label ) . '</label>' : '' ;

		$control .= '<div class = "wic_multi_select">';
				
		foreach ( $select_array as $option ) {

			$args = array(
				'field_name_id' 		=> $field_name_id . '[' . $option['value'] . ']',
				'field_label'			=>	$option['label'],
				'label_class'			=> 'wic-multi-select-label '  . $option ['class'],
				'input_class'			=> 'wic-multi-select-checkbox ', 
				'value'					=> in_array ( $option['value'], $value, false ),
				'read_only_flag'		=>	false,
				'field_label_suffix'	=> '',						
			);	
			$control .= '<p class = "wic_multi_select_item" >' . $this->create_check_control($args) . '</p>';
			
		}
		$control .= '</div>';
		return ( $control );
	
	}	



	/* little function to format phone numbers for display */	
   function format_phone ($raw_phone) {
   	
		$phone = preg_replace( "/[^0-9]/", '', $raw_phone );
   	
		if ( 7 == strlen($phone) ) {
			return ( substr ( $phone, 0, 3 ) . '-' . substr($phone,3,4) );		
		} elseif ( 10  == strlen($phone) ) {
			return ( '(' . substr ( $phone, 0, 3 ) . ') ' . substr($phone, 3, 3) . '-' . substr($phone,6,4) );	
		} else {
			return ($phone);		
		}
    
    }

	/*
	*	repeater validation function for phones
	*/

	function validate_phones( $phone_number_row ) {
		
		$outcome = array(
			'result' 	=> '',
			'error'		=> '',
			'present' 	=> false
		);

		$outcome['result'] = array(
				preg_replace( "/[^0-9]/", '', $phone_number_row[0] ),
				preg_replace( "/[^0-9]/", '', $phone_number_row[1] ),
				preg_replace( "/[^0-9]/", '', $phone_number_row[2] ), 
			);
			
		$outcome['present'] = $outcome['result'][1] > '' ;
		
		return( $outcome );		
			
	}
	
	
	function validate_individual_email( $email ) { 
		$error = filter_var( $email, FILTER_VALIDATE_EMAIL ) ? '' : __( 'Email address appears to be not valid. ', 'wp-issues-crm' );
		return $error;	
	}	

/*
*	function for address groups
*
*/

	}
	function validate_addresses( $address_row ) {
		
		$outcome = array(
			'result' 	=> '',
			'error'		=> '',
			'present' 	=> false
		);

		$outcome['result'] = array(
				preg_replace( "/[^0-9]/", '', $address_row[0] ),
				stripslashes( sanitize_text_field ( $address_row[1] ) ),
				stripslashes( sanitize_text_field ( $address_row[2] ) ),
			);
			
		$outcome['present'] = $outcome['result'][1] > '' || $outcome['result'][2] > '' ;
		
   	$outcome['error'] =  '';

		return( $outcome );		
			
	}
	
	/*
	* convert string with various possible white spaces and commas into comma separated	
	*/
	public function resanitize_individual_textcsv ( $textcsv ) {
		
		$temp_tags = str_replace ( ',', ' ', $textcsv )	;	
		$temp_tags = explode ( ' ', $textcsv );
		
		$temp_tags2 = array();
		foreach ( $temp_tags as $tag ) {
			if ( trim($tag) > '' ) {
				$temp_tags2[] = trim($tag);
			}			
		}
		$output_textcsv = implode ( ',', $temp_tags); 		
		return ( $output_textcsv );
	}	
	
	
	
	public function format_wic_post_content ( $notes ) {

		$current_user = wp_get_current_user();
				
		$output = '<div class = "wic-notes-entry">' .
						'<div class = "wic-notes-header">' .
							'<div class = "wic-notes-author">' . __( 'Note by ' , 'wp-issues-crm' ) .  $current_user->display_name . '</div>' .
							'<div class = "wic-notes-date">' . '(' . current_time('Y-m-d, h:i:s A' ) . ')' . ':</div>' .
						'</div>' .
						'<div class = "wic-notes-content">' .
							$notes .
						'</div>' .
					'</div>';
					
		return ($output); 
	}	

	public function create_wic_form_button ( $control_array_plus_class ) { 
	
		$form_requested			= '';
		$action_requested			= '';
		$id_requested				= 0 ;
		$referring_parent			= 0 ;
		$new_form 					= 'n'; // go straight to a save
		$button_class				= 'wic-form-button';
		$button_label				= '';
		$omit_label_and_close_tag = false;

		extract ( $control_array_plus_class, EXTR_OVERWRITE );

		$button_value = $form_requested . ',' . $action_requested  . ',' . $id_requested  . ',' . $referring_parent . ',' . $new_form;
		$close = $omit_label_and_close_tag ? '' : __( $button_label, 'wp-issues-crm' ) . '</button>';

		$button =  '<button class = "' . $button_class . '" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . $close;

		return ( $button );
	}

	public function drop_down_issues() {
		
		global $wic_database_utilities;		
				
		$wic_issues_query = $wic_database_utilities->get_open_issues();

		$issues_array = array();
		
		if ( $wic_issues_query->have_posts() ) {		
			while ( $wic_issues_query->have_posts() ) {
				$wic_issues_query->the_post();
				$issues_array[] = array(
					'value'	=> $wic_issues_query->post->ID,
					'label'	=>	$wic_issues_query->post->post_title,
				);
			}
		}
		
		wp_reset_postdata();
		return $issues_array;

	}

	public function wic_get_post_title( $post_id ) {

/*		global $wic_database_utilities;	
		$post_query = $wic_database_utilities->wic_get_post( $post_id );
		$title = $post_query->posts[0]->post_title;
		wp_reset_postdata();
*/
		$title = get_the_title ( $post_id ); 
		return $title;
					
	}

	public function wic_get_user_list ( $role ) {
		/* query users with specified role (s) -- empty string returns all */
		$user_query_args = 	array (
			'role' => $role,
			'fields' => array ( 'ID', 'display_name'),
		);						
		$user_list = new WP_User_query ( $user_query_args );

		$user_select_array = array();
		foreach ( $user_list->results as $user ) {
			$temp_array = array (
				'value' => $user->ID,
				'label'	=> $user->display_name,									
			);
			array_push ( $user_select_array, $temp_array );								
		} 

		return ( $user_select_array );

	}


	public $category_select_array = array();
	private $category_array_depth = 0;

	public function wic_get_category_list ( $parent ) {
		
		$this->category_array_depth++;		
		
		$args = array(
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 0,
			'taxonomy'                 => 'category',
			'pad_counts'               => true, 
			'parent'							=> $parent,
		); 

		$categories = get_categories( $args );
		if ( 0 < count ( $categories ) ) {		
			foreach ( $categories as $category ) {
				$temp_array = array (
					'value' => $category->term_id,
					'label' => $category->name,
					'class' => 'wic-multi-select-depth-' . $this->category_array_depth,
				);			
				$this->category_select_array[] = $temp_array;
				$this->wic_get_category_list ($category->term_id);	
			}
		}
		$this->category_array_depth--;
		return ( $this->category_select_array );
	} 



	public function wic_get_post_categories ( $post_id ) {
		 
		$categories = get_the_category ( $post_id );
		$return_list = '';
		foreach ( $categories as $category ) {
			$return_list .= ( '' == $return_list ) ? $category->cat_name : ', ' . $category->cat_name;		
				}
		return ( $return_list ) ;	
	}

	public function wic_get_post_author_display_name ( $user_id ) {
		
		$display_name = '';		
		if ( isset ( $user_id ) ) { 
			if ( $user_id > 0 ) {
				$user =  get_users( array( 'fields' => array( 'display_name' ), 'include' => array ( $user_id ) ) );
				$display_name = $user[0]->display_name; // best to generate an error here if this is not set on non-zero user_id
			}
		}
		return ( $display_name );
	}

	public function format_select_array ( $select_array, $format, $select_parameter ) {
		
		$select_array = is_array( $select_array ) ? $select_array : $this->$select_array( $select_parameter );
		if ( 'control' == $format ) {
			return ( $select_array );		
		} elseif ( 'lookup' == $format ) {
			$reformatted_select_array = array();
			foreach ( $select_array as $pair ) {
				$reformatted_select_array[$pair['value']] = $pair['label'];
			} 	
			return ( $reformatted_select_array );
		}	
	}
	
}

$wic_form_utilities = new WP_Issues_CRM_Form_Utilities;