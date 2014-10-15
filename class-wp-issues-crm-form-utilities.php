<?php
/*
* form utilities
*
*/

class WP_Issues_CRM_Form_Utilities {

	public function __construct() {

	}
	
	/* 
	*	This function sets up main form variable
	*
	*/	
	public function initialize_blank_form( &$next_form_output, &$fields_array, $post_id )	{ 

		/* these values may later go into database */			
		foreach ( $fields_array as $field ) { // note 1 below 
			$next_form_output[$field['slug']] =	'';
			if ( 'date' == $field['type'] ) {
				$next_form_output[$field['slug'] . '_lo'] = '';				
				$next_form_output[$field['slug'] . '_hi'] = '';
			}
		}
		$next_form_output['wic_post_content']	=	''; 					// note 2 below 
		$next_form_output['old_wic_post_content']	=	''; 					// note 2 below 			
		$next_form_output['wic_post_id']		=	$post_id;	// note 3 below

		/*	these values are only for form setup */	
		$next_form_output['guidance']				=  'Enter just a little information and do a full text search for constituents.'; // note 4a below
		$next_form_output['error_messages']		=	'';					// note 4b below
		$next_form_output['search_notices']		=	'';					// note 4c below
		$next_form_output['next_action'] 		=	'search';			// note 5 below
		$next_form_output['strict_match']		=	false;				// note 6 below
		$next_form_output['initial_form_state']= 	'wic-form-open';  // note 7 below 
		$next_form_output['initial_sections_open'] = array();			// note 8 below
		
	}

	/*
	*
	*	sanitize_validate_input: form sanitization and validation function:
	*  takes blank array and populates it from $_POST while sanitizing it
	*   
	* 		1. handles all meta fields defined as displayable for wic_posts
	*		2. wic_post_content
	*		3. wic_post_id
	*		4. form_notices with validation errors
	*
	*	The following sanitization/validation is done:
	*
	*	(1) All fields have stripslashes applied
	*  (2) All fields except wic_post_content have sanitize_text_field applied -- "Checks for invalid UTF-8, 
	*			Convert single < characters to entity, strip all tags, remove line breaks, tabs and extra white space, strip octets."
	*	(3) Post content is not sanitized or validated other than by stripping slashes (trust Wordpress on search/save)
	*	(4) All content validation rules are applied 
	*			- required fields
	*			- email formatting 
	*			- date formatting
	*			- phones compressed to numeric only
	*			- note select fields are compressed to numeric or slash/textsanitized
	*	  
	*	uses php converts DateTime object to recognize date formats and convert to yyyy-mm-dd
	*  
	*/   
   
   public function sanitize_validate_input( &$clean_input, &$fields_array ) {
   	// takes initialized blank working array and populates it.
   	
   	global $wic_base_definitions; 
   	$group_required_test = '';
   	$group_required_label = '';
		$possible_validator = '';   	
    	
   	foreach ( $fields_array as $field ) { 
   		
   		if ( in_array( $field['type'], $wic_base_definitions->serialized_field_types ) && isset( $_POST[$field['slug']] ) ) {
 	 			// if array, load array, sanitizing all fields and cleaning/validating (using array validation function)	  		
 				if ( is_array( $_POST[$field['slug']] ) ) { 
		  			$validation_function = 'validate_' . $field['type'];
					$repeater_count = 0;
					foreach( $_POST[$field['slug']] as $key => $value ) {	
						if ( 'row-template' !== $key ) { // skip template row -- NB:  true: 0 == 'alphastring' false: 0 != 'alphastring true 0 !== 'alphastring'
							$test_repeater = $this->$validation_function($value);
							if ( $test_repeater['present'] ) { // skip rows that validate to absent
								$clean_input[$field['slug']][$repeater_count] = $test_repeater['result'];
								$repeater_count++;
								if ( $test_repeater['error'] > '' ) {
									$clean_input['error_messages'] .= ' ' . $test_repeater['error'] . ' ' . $field['label'] . ' ' . $repeater_count . '. '; 
								}
							}	 						
						}
					}
				} else { // non array for serialized field is only from a search -- compress/sanitize, but not validate
					if ( 'phones' == $field['type'] ) {
						$clean_input[$field['slug']] = preg_replace("/[^0-9]/", '', $_POST[$field['slug']] );
					} else {
						$clean_input[$field['slug']] = stripslashes( sanitize_text_field( $_POST[$field['slug']] ) );
					}
				} // close non-array for serialized fields
			} else { // not a serialized field and/or not set	-- do clean and also individual field validators
 				$clean_input[$field['slug']] = isset( $_POST[$field['slug']] ) ? stripslashes( sanitize_text_field( $_POST[$field['slug']] ) ) : '';
 				$possible_validator =  'validate_individual_' . $field['type'];
 				if ( $clean_input[$field['slug']] > '' && method_exists ( $this, $possible_validator )  ) {
 					 $clean_input['error_messages'] .= $this->$possible_validator( $clean_input[$field['slug']] );				
 				} 
			}

			// add date hi-lo ranges to array and standardize all dates to yyyy-mm-dd 
			if ( 'date' == $field['type'] ) {
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

		
		extract ( $control_args, EXTR_OVERWRITE ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ?  '<label class="' . $label_class . '" for="' . 
				esc_attr( $field_name_id ) . '">' . esc_html( $field_label ) . ' ' . '</label>' : '';
		$control .= '<input class="wic-input-checked"  id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) . 
			'" type="checkbox"  value="1"' . checked( $value, 1, false) . $readonly  .'/>' . 
			$field_label_suffix_span  ;	

		return ( $control );

	}
	
	public function create_text_control ( $control_args ) {
		
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
		 
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . esc_html( $field_label ) . '</label>' : '' ;
		$control .= '<input class="' . $input_class . '" id="' . esc_attr( $field_name_id )  . 
			'" name="' . esc_attr( $field_name_id ) . '" type="text" placeholder = "' .
			 esc_attr( $placeholder ) . '" value="' . esc_attr ( $value ) . '" ' . $readonly  . '/>' . $field_label_suffix_span; 
			
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

/*
*
*	The functions below are used to create the repeating special groups -- phones, emails, addresses
*    -- they build in logic about these types of data.
*
*  In every instance, the second position of the group array is the main datum.  
* 	It is extracted from the first instance in search functions requiring strings.
*
*  To add a new repeater group x, ( x like phones, emails, addresses ) 
*		+ add x to $serialized_field_types above, 
*		+ add create_x_group function here to set up the display, 
*     + add a validate_x function here to handle form input for each row
*
*/

	
	/* this button will destroy the form element (e.g., paragraph for repeater row) containing it */
	public function create_destroy_button () {

		$button = '<button ' .  
			' class	="destroy-button"' . 
			' onclick = {this.parentNode.parentNode.removeChild(this.parentNode);}' .
			' type 	= "button" ' .
			' name	= "destroy-button" ' .
			' title  = ' . __( 'Remove Row', 'wp-issues-crm' ) .
			' >x</button>';	

		return ($button);
	}
	
	
	/* this button will create a new instance of the templated base paragraph (repeater row) and insert it above related counter in the DOM*/
	public function create_add_button ( $base, $button_label ) {
		
		$button ='<div class = "add-button-spacer"></div>' .  
			'<button ' . 
			' class = "row-add-button" ' .
			' id = "' . esc_attr( $base ) . '-add-button" ' .
			' type = "button" ' .
			' onclick="moreFields(\'' . esc_attr( $base ) . '\')" ' .
			' >' . esc_html(  $button_label ) . '</button>'; 

		return ($button);
	}

	/*
	*
	*	output show-hide-button
	*  calls toggleConstituentFormSection in wic-utilities.js
	*
	*/
	public function output_show_hide_toggle_button( $args ) {

		$class 			= 'field-group-show-hide-button';		
		$name_base 		= 'wic-inner-field-group-'  ;
		$name_variable = ''; // group['name']
		$label = ''; // $group['label']
		$show_initial = true;
		
		extract( $args, EXTR_OVERWRITE );

		$show_legend = $show_initial ? __( 'Hide', 'wp-issues-crm' ) : __( 'Show', 'wp-issues-crm' );

		
		$button =  '<button ' . 
		' class = "' . $class . '" ' .
		' id = "' . $name_base . esc_attr( $name_variable ) . '-toggle-button" ' .
		' type = "button" ' .
		' onclick="toggleConstituentFormSection(\'' . $name_base . esc_attr( $name_variable ) . '\')" ' .
		' >' . esc_html ( $label ) . '<span class="show-hide-legend" id="' . $name_base . esc_attr( $name_variable ) .
		'-show-hide-legend">' . $show_legend . '</span>' . '</button>';

		return ($button);
}



		
	public function create_phones_group ( $repeater_group_args ) {
		/*
      *		'repeater_group_id'		=> $field['slug'],
		*		'repeater_group_label'		=> $field['label'],
		*		'repeater_group_data_array'	=>	$next_form_output[$field['slug']],
		*		'repeater_group_label_suffix'	=> $required_individual . $required_group . $contains,		
		*/
		
		global $wic_constituent_definitions;		
		
		extract ( $repeater_group_args, EXTR_OVERWRITE );
		
				
		$repeater_group_id = esc_attr( $repeater_group_id );
		// create phones division opening tag 		
		$phone_group_control_set = '<div id = "' . $repeater_group_id . '-control-set' . '">';

		// create a hidden template row for adding phone fields in wic-utilities.js through moreFields() 
		// moreFields will replace the string 'row-template' with row-counter index value after creating the new row
		// this will change row id and the field indexes - the array will be $repeater_group_id[x][y] (x = row, y = field)
		$row = '<p class = "hidden-template" id = "' . $repeater_group_id . '-row-template' . '">'; // template opening line	
	
		$phone_type_array = array ( 
				'field_name_id' 	=> $repeater_group_id . '[row-template][0]',
				'field_label'		=>	'',
				'placeholder' => __( 'Phone type?', 'wp-issues-crm' ),
				'select_array'		=>	$wic_constituent_definitions->phone_type_options,
				'value'			=> '',
				'field_input_class' 	=> 'wic-input wic-phone-type-dropdown',
				'field_label_suffix'	=> '',
			);	
		$row .= $this->create_select_control ( $phone_type_array );

		$phone_number_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][1]',
			'field_label'			=>	'',
 			'value'					=> '', 
 			'placeholder'			=> __( 'Phone number?', 'wic-issues-crm' ),
			'input_class' 	=> 'wic-input wic-phone-number',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_text_control( $phone_number_array );

		$phone_extension_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][2]',
			'field_label'			=>	'',
 			'value'					=> '',
			'placeholder'			=> __( 'Extension?', 'wic-issues-crm' ),
			'input_class' 	=> 'wic-input wic-phone-extension',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_text_control( $phone_extension_array );

		$row .= $this->create_destroy_button ();

		$row .= '</p>';
	
		// put completed template row into phones division			
		$phone_group_control_set .= $row;


		// now proceed to add rows for any existing phones from database or previous form
		$i = '0'; // array index
		
		if ( is_array( $repeater_group_data_array ) ) {

			foreach ( $repeater_group_data_array as $phone_number ) {
				
				// note, in this loop, need only instantiate the changing arguments in the arrays			
				
				$row = '<p class = "phone-number-row" id = "' . $repeater_group_id . '-' . $i . '">';
							
				$phone_type_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][0]';
				$phone_type_array['value']			= $repeater_group_data_array[$i][0];
				$row .= $this->create_select_control ( $phone_type_array );
	
			
				$phone_number_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][1]';
	 			$phone_number_array['value']				= $this->format_phone ( $repeater_group_data_array[$i][1] );
				$row .= $this->create_text_control( $phone_number_array );
	

				$phone_extension_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][2]';
	 			$phone_extension_array['value']				= $repeater_group_data_array[$i][2];
				$row .= $this->create_text_control( $phone_extension_array );
				
				$row .= $this->create_destroy_button ();
				
				$row .= '</p>';
				
				$phone_group_control_set .= $row;
				
				$i++;
	
			}
		}		
		
		$phone_group_control_set .= '<div class = "hidden-template" id = "' . $repeater_group_id . '-row-counter">' . $i . '</div>';
		$phone_group_control_set .= $this->create_add_button ( $repeater_group_id, __( 'Add Phone', 'wp-issues-crm' ) . ' ' . $repeater_group_label_suffix ) ;
		$phone_group_control_set .= '</div>';
		
		
		
		return ($phone_group_control_set);	
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
	
	public function create_emails_group ( $email_group_args ) {
		/*
      *		'repeater_group_id'		=> $field['slug'],
		*		'repeater_group_label'		=> $field['label'],
		*		'repeater_group_data_array'	=>	$next_form_output[$field['slug']],
		*		'repeater_group_label_suffix'	=> $required_individual . $required_group . $contains,		
		*/
		
		global $wic_constituent_definitions;
		
		extract ( $email_group_args, EXTR_OVERWRITE );
		
		$repeater_group_id = esc_attr( $repeater_group_id );
		// create emails division opening tag 		
		$email_group_control_set = '<div id = "' . $repeater_group_id . '-control-set' . '">';

		// create a hidden template row for adding email fields in wic-utilities.js through moreFields() 
		// moreFields will replace the string 'row-template' with row-counter index value after creating the new row
		// this will change row id and the field indexes - the array will be $repeater_group_id[x][y] (x = row, y = field)
		$row = '<p class = "hidden-template" id = "' . $repeater_group_id . '-row-template' . '">'; // template opening line	
	
		$email_type_array = array ( 
				'field_name_id' 	=> $repeater_group_id . '[row-template][0]',
				'field_label'		=>	'',
				'placeholder' => __( 'eMail type?', 'wp-issues-crm' ),
				'select_array'		=>	$wic_constituent_definitions->email_type_options,
				'value'			=> '',
				'field_input_class' 	=> 'wic-input wic-email-type-dropdown',
				'field_label_suffix'	=> '',
			);	
		$row .= $this->create_select_control ( $email_type_array );

		$email_address_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][1]',
			'field_label'			=>	'',
 			'value'					=> '', 
 			'placeholder'			=> __( 'eMail address?', 'wic-issues-crm' ),
			'input_class' 		=> 'wic-input wic-email-address',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_text_control( $email_address_array );

		$row .= $this->create_destroy_button ();

		$row .= '</p>';
	
		// put completed template row into emails division			
		$email_group_control_set .= $row;


		// now proceed to add rows for any existing emails from database or previous form
		$i = '0'; // array index
		
		if ( is_array( $repeater_group_data_array ) ) {

			foreach ( $repeater_group_data_array as $email_address ) {
				
				// note, in this loop, need only instantiate the changing arguments in the arrays			
				
				$row = '<p class = "email-address-row" id = "' . $repeater_group_id . '-' . $i . '">';
							
				$email_type_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][0]';
				$email_type_array['value']			= $repeater_group_data_array[$i][0];
				$row .= $this->create_select_control ( $email_type_array );
	
			
				$email_address_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][1]';
	 			$email_address_array['value']				= $repeater_group_data_array[$i][1];
				$row .= $this->create_text_control( $email_address_array );
				
				$row .= $this->create_destroy_button ();
				
				$row .= '</p>';
				
				$email_group_control_set .= $row;
				
				$i++;
	
			}
		}		
		$email_group_control_set .= '<div class = "hidden-template" id = "' . $repeater_group_id . '-row-counter">' . $i . '</div>';		
		$email_group_control_set .= $this->create_add_button ( $repeater_group_id, __( 'Add eMail', 'wp-issues-crm' ) . ' ' . $repeater_group_label_suffix ) ;
		$email_group_control_set .= '</div>';

		
		
		return ($email_group_control_set);	
	}

	function validate_emails( $email_row ) {
		
		$outcome = array(
			'result' 	=> '',
			'error'		=> '',
			'present' 	=> false
		);

		$outcome['result'] = array(
				preg_replace( "/[^0-9]/", '', $email_row[0] ),
				stripslashes( sanitize_text_field ( $email_row[1] )),
			);
			
		$outcome['present'] = $outcome['result'][1] > '';
		
  		if ( $outcome['present'] ) { 
	   	$outcome['error'] =  $this->validate_individual_email( $outcome['result'][1] );
		}	
		
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

		
	public function create_addresses_group ( $repeater_group_args ) {
		/*
      *		'repeater_group_id'		=> $field['slug'],
		*		'repeater_group_label'		=> $field['label'],
		*		'repeater_group_data_array'	=>	$next_form_output[$field['slug']],
		*		'repeater_group_label_suffix'	=> $required_individual . $required_group . $contains,		
		*/
		global $wic_constituent_definitions;
		
		extract ( $repeater_group_args, EXTR_OVERWRITE );
		
		$repeater_group_id = esc_attr( $repeater_group_id );
		// create addresss division opening tag 		
		$address_group_control_set = '<div id = "' . $repeater_group_id . '-control-set' . '">';

		// create a hidden template row for adding address fields in wic-utilities.js through moreFields() 
		// moreFields will replace the string 'row-template' with row-counter index value after creating the new row
		// this will change row id and the field indexes - the array will be $repeater_group_id[x][y] (x = row, y = field)
		$row = '<p class = "hidden-template" id = "' . $repeater_group_id . '-row-template' . '">'; // template opening line	
	
		$address_type_array = array ( 
				'field_name_id' 	=> $repeater_group_id . '[row-template][0]',
				'field_label'		=>	'',
				'placeholder' => __( 'Address type?', 'wp-issues-crm' ),
				'select_array'		=>	$wic_constituent_definitions->address_type_options,
				'value'			=> '',
				'field_input_class' 	=> 'wic-input wic-address-type-dropdown',
				'field_label_suffix'	=> '',
			);	
		$row .= $this->create_select_control ( $address_type_array );

		$address_street_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][1]',
			'field_label'			=>	'',
 			'value'					=> '', 
 			'placeholder'			=> __( 'Street Address?', 'wic-issues-crm' ),
			'input_class' 	=> 'wic-input wic-address-street',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_text_control( $address_street_array );

		$address_zip_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][2]',
			'field_label'			=>	'',
			'placeholder' => __( 'City/Zip?', 'wp-issues-crm' ),
			'select_array'		=>	$wic_constituent_definitions->address_zip_options,
			'value'			=> '',
			'field_input_class' 	=> 'wic-input wic-address-zip',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_select_control( $address_zip_array );

		$row .= $this->create_destroy_button ();

		$row .= '</p>';
	
		// put completed template row into addresss division			
		$address_group_control_set .= $row;


		// now proceed to add rows for any existing addresss from database or previous form
		$i = '0'; // array index
		
		if ( is_array( $repeater_group_data_array ) ) {

			foreach ( $repeater_group_data_array as $address_number ) {
				
				// note, in this loop, need only instantiate the changing arguments in the arrays			
								
				$row = '<p class = "address-number-row" id = "' . $repeater_group_id . '-' . $i . '">';
							
				$address_type_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][0]';
				$address_type_array['value']			= $repeater_group_data_array[$i][0];
				$row .= $this->create_select_control ( $address_type_array );
	
			
				$address_street_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][1]';
	 			$address_street_array['value']				= $repeater_group_data_array[$i][1];
				$row .= $this->create_text_control( $address_street_array );
	

				$address_zip_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][2]';
	 			$address_zip_array['value']				= $repeater_group_data_array[$i][2];
				$row .= $this->create_select_control( $address_zip_array );
				
				$row .= $this->create_destroy_button ();
				
				$row .= '</p>';
				
				$address_group_control_set .= $row;
				
				$i++;
	
			}
		}		
		$address_group_control_set .= '<div class = "hidden-template" id = "' . $repeater_group_id . '-row-counter">' . $i . '</div>';		
		$address_group_control_set .= $this->create_add_button ( $repeater_group_id, __( 'Add Address', 'wp-issues-crm' ) . ' ' . $repeater_group_label_suffix ) ;
		$address_group_control_set .= '</div>';

		
		
		return ($address_group_control_set);	
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

}

$wic_form_utilities = new WP_Issues_CRM_Form_Utilities;