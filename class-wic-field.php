<?php
/*
* wic-field.php
*
* This file contains WIC_Field base class and child classes with names of the form WIC_{Type}_Field. 
* This is the list of valid Types for WIC Fields:
*		-- Checked
*		--	Date
*		-- Email
*		-- Text
*		-- Textarea
* 
*
*
*/

/************************************************************************************
*
*  WIC Field
*
************************************************************************************/
abstract class WIC_Field {
		
	/* name and type are required in field definitions from wic_table class */
	$name			= ''				// no spaces machine use name
	$type			= ''				// Initial Caps, since field type will be embedded in class name by WIC_Table __construct 
	
	/* all other properties can default and not all properties are implemented for all field types */
	$dedup 					= false; 				// whether field should be included in definition of unique record for deduping purposes 
	$default					= ''						// default value for field in save forms	
	$group 					= ''; 					// group in form layout
	$label 					= '';						// front facing label
	$like	 					= false;					// whether field may be searched in free text mode -- right wild card only, needle% 	
	$list	 					= '0';					// the css width value for the field in forms and listings, expressed in px
	$list_call_back	= ''							// function to call to decode (transform from select function )
	$online 					= true;					// whether the field should be displayed in the online system at all
	$order					= 0;						// order in form layout (ordered across all groups, but group order is higher sort)
	$readonly				= false;					// allow search, but not save or update
	$required 				= '';						// can be '' (not required), 'individual' (required) or  'group' (at least one of group must be supplied)
	$select_array 			= '';						// hard coded array for short select lists;
	$select_function 		= '';						// function for creating more complex select lists;
	$select_parameter 	= '';						// parameter to pass to select function 
	$wp_query_parameter 	= '';						// used for fields that go back to post records
	
	// value of the field, from form or possibly, after search, from database 
	$value				= ''; 
	$formatted_value	= '';

	// possible validation errors field	
	$validation_errors = '';
	$search_notices = '';	
	
	// parameters for text control creation -- the text control is used by multiple extensions of the class
	$control_args = array {
		// first four normally equal field settings (grab_control_args_from_field_settings), but may vary by save/update/search context
		'field_name_id' 		=> '',
		'field_label' 			=> '', 
		'read_only_flag' 		=> false,
		'value' 					=> '',
		// these are not derived directly from field settings  -- either they default or are specified by context
		'input_class' 			=> 'wic-input',
		'placeholder' 			=> '',
		'label_class' 			=> 'wic-label',
		'field_label_suffix' => '',
		'type'					=> 'text' 
	}

	// sanitizes, validates, all basic properties
	protected function __construct( $args )	{

		// initialize field definition properties from WIC table field definitions
		foreach ( $args as $key => $value ) {
			$this->$key = $value;
		} 
		
		// die if name not among properties
		if ( '' == $this-> name ) { // note that need not check for type, since will die on class not found
			die ("Field configuration error -- name omitted in field definitions for a field in the requested record type." );		
		}
		
		// initialize field value 
		$this->value = isset ( $_POST[$args['name'] ) ? $_POST[$args['name'] : '' ;
			
		// sanitize if non-blank
		if ( $this->value > '' ) { 
			$this->value = $this->sanitize( $this->value );
		}

		// validate if still non-blank
	
		if ( $this->value > '' ) { 
			$this->validate();
		}
		// format for screen output if still non-blank {
		if ( $this->value > '' ) { 
			$this->formatted_value = $this->format( $this->value );
		}

		// args may be overridden, but good starting point for most instances 		
		$this->grab_control_args_from_class_properties();	
		
	}

	// basic sanitization, stripslashes because of magic quotes; sanitize_text_field also applied -- "Checks for invalid UTF-8,
	// Convert single < characters to entity, strip all tags, remove line breaks, tabs and extra white space, strip octets."
	protected function sanitize ( $dirty ) {
		$clean = ( stripslashes( sanitize_text_field ( $dirty ) ) ;
		return( $clean );
	}	

	// validation template -- may be empty for field type, extended class not required to implement
	protected function validate() {
	}

	// validation template -- may be empty for field type, extended class not required to implement
	protected function format() {
		$return( $this->value );
	}

	// in extended classes, the following two database access functions will vary

	protected function search_clauses () {
		
		$search_clauses = array(
			'where' 		= '',
			'join' 		= '',
			'values' 	= array(),
		);
		
		if ( '' < $this->value ) {
			$search_clauses['where']  			=  " AND $name = %s "
			$this->search_values['values'][]	= $this->value;
		}

		return ( $search_clauses );

	}

	protected function data_array () {
		$data_array = array();
		$data_array[$this->name] = $this->value;		
		return ( $data_array );
	}

	public function new_control () {
		$this->control_args['value'] = '';
		$this->search_control();
	}

	public function search_control () {
		$this->control_args['read_only_flag'] = false;
		$this->control_args['field_label_suffix'] = ( $this->contains ) ? '*' : '';
		echo  = '<p>' . $this->create_control( $this->control_args ) . '<p>';
	}
	
	public function save_control () {
		if( ! $this->readonly ) {
			
			echo  = '<p>' . $this->create_control	( $this->control_args ) . '</p>';	
		}
	}
	
	public function update_control () {
		echo  = '<p>' . $this->create_control	( $this->control_args ) . '</p>';	
	}

	public function create_control ( $control_args ) { // basic create text control
		
		extract ( $control_args, EXTR_SKIP ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' && ! $hidden_flag ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . esc_html( $field_label ) . '</label>' : '' ;
		$control .= '<input class="' . $input_class . '" id="' . esc_attr( $field_name_id )  . 
			'" name="' . esc_attr( $field_name_id ) . '" type="' . $type . '" placeholder = "' .
			 esc_attr( $placeholder ) . '" value="' . esc_attr ( $value ) . '" ' . $readonly  . '/>' . $field_label_suffix_span; 
			
		return ( $control );

	}
	
	// these may be overriden in context, but are usual correct, so do this always as a starting point
	public function grab_control_args_from_field_settings () {
		$this->control_args['field_name_id']	=> $this->name;
		$this->control_args['field_label'] 		=> $this->label; 
		$this->control_args['read_only_flag'] 	=> $this->readonly;
		$this->control_args['value'] 				=> $this->formatted_value;
	}

	public function set_required_values_legend () {
		$required_individual = ( 'individual' == $this->required ) ? '*' : '';
		$required_group = ( 'group' == $this->required ) ? '(+)' : '';
		$this->control_args['field_label_suffix'] = $required_individual . $required_group;  
	}

}

/************************************************************************************
*
*  WIC Checked Field
*
************************************************************************************/
class WIC_Checked_Field extends WIC_Field {
	
	public function create_control ( $control_args ) {
		
		$input_class = 'wic_input_checked';

		extract ( $control_args, EXTR_SKIP ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ?  '<label class="' . $label_class . '" for="' . 
				esc_attr( $field_name_id ) . '">' . esc_html( $field_label ) . ' ' . '</label>' : '';
		$control .= '<input class="' . $input_class . '"  id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) . 
			'" type="checkbox"  value="1"' . checked( $value, 1, false) . $readonly  .'/>' . 
			$field_label_suffix_span  ;	

		return ( $control );

	}	

}	

/************************************************************************************
*
*  WIC Date Field
*
************************************************************************************/
class WIC_Date_Field extends WIC_Field {
	
	$date_values = array(); // 0 is base value, 1, low range, 2 high range
	
	public function __construct() { // has to sanitize/validate possible range as well as base value
		
		// initialize field definition properties
		foreach ( $args as $key => $value ) {
			$this->$key = $value;
		} 

		if ( '' == $this-> name ) { // note that need not check for type, since will die on class not found
			die ("Field configuration error -- name omitted in field definitions for a field in the requested record type." );		
		}
		
		// initialize field values 
		$this->date_values[] = array (   '', isset( $_POST[$args['name']           ) ? $_POST[$args['name']              : '' );	
		$this->date_values[] = array ( 'lo', isset( $_POST[$args['name'] . '_lo' ] ) ? $_POST[$args['name'] . '_lo' ] )  : '' );		 	
		$this->date_values[] = array ( 'hi', isset( $_POST[$args['name'] . '_hi' ] ) ? $_POST[$args['name'] . '_hi' ] )  : '' );	
		
		// sanitize each date if non-blank
		foreach ( $date_values as $date ) {
			if ( $date[1] > '' ) {
				date[1] = sanitize( $date[1] );			
			}
		}

		// validate each date if still non-blank
		foreach ( $date_values as $date ) {
			if ( $date[1] > '' ) {
				$date[1] = validate( $date[1] );	
				if ( '' == $date[1] ) {
					$date_identifier = ( '' == $date[0] ) ? '' : ' (' . $date[0] . ') ';
					$this->validation_errors .= $args['label'] . $date_identifier . __( ' had unsupported date format -- yyyy-mm-dd will work. ', 'wp-issues-crm' );		
				}		
			}
		}

		// args may be overridden, but good starting point for most instances 		
		$this->grab_control_args_from_class_properties();	
		
	}
	
	public function validate ( $possible_date ) {
		try {
			$test = new DateTime( $possible_date );
		}	catch ( Exception $e ) {
			$this
			return ( '' );
		}	   			
 		return ( date_format( $test, 'Y-m-d' ) );
	}

	protected function search_clauses () {
		
		$search_clauses = array(
			'where' 		= '',
			'join' 		= '',
			'values' 	= array(),
		);
		
		if ( '' < $this->date_array[1][1]] ) { // low date
			$search_clauses['where']  			=  " AND $name >= %s "
			$this->search_values['values'][]	= date_array[1][1]];
		}

		if ( '' < $this->date_array[2][1]] ) { // high date
			$search_clauses['where']  			=  " AND $name <= %s "
			$this->search_values['values'][]	= $this->date_array[2][1]];
		}

		return ( $search_clauses );

	}
	
	public function search_control () {
		$this->create_date_range_control ( $this->date_array[1][1], $this->date_array[2][1] );	
	}

	public function create_control ( $low_date, $high_date ) {

		$date_range_control = '';

		$args = array (
			'field_name_id'		=> $this->name . '_lo',
			'field_label'			=>	$this->label . ' >= ' ,
			'value'					=> $low_date
		);
		$date_range_control .=  parent::create_control ( $args ); 
	
		$args = array (
			'field_name_id'		=> $this->name . '_hi',
			'field_label'			=>	__( 'and <=', 'wp_issues_crm' ),
			'label_class'			=> 'wic-label-2',
			'value'					=> $high_date
						
		);
		$date_range_control .=  parent::create_control ( $args ); 

		return ( $date_range_control );	
	
	}	
	
}	
	
/************************************************************************************
*
*  WIC Email Field
*
************************************************************************************/
class WIC_Email_Field extends WIC_Text_Field {
	
	public function __construct() {
		parent::__construct();
		$
		$this->control_args['type'] = 'email';
	}

	public function validate {
		$error = filter_var( $email, FILTER_VALIDATE_EMAIL ) ? '' : __( 'Email address appears to be not valid. ', 'wp-issues-crm' );
		$this->validation_errors .= $error	
	}	

}

/************************************************************************************
*
*  WIC Link Field (used for ID or parent; can additionally do ID or parent as readonly,
*  but if do only as readonly, will not carry through from form -- 
*  readonly fields are not in $_POST
*
************************************************************************************/
class WIC_Link_Field extends WIC_Text_Field {
	
	public function __construct() {
		parent::__construct();
		$this->control_args['type'] = 'hidden';
	}

}



/************************************************************************************
*
*  WIC Phone Field
*
************************************************************************************/

class WIC_Phone_Field extends WIC_Text_Field {
	
	public function sanitize ( $value ) {
		$value = preg_replace( "/[^0-9]/", '', $value)
	}	
	
   public function format ( $raw_phone ) {
   	
		$phone = preg_replace( "/[^0-9]/", '', $raw_phone );
   	
		if ( 7 == strlen($phone) ) {
			return ( substr ( $phone, 0, 3 ) . '-' . substr($phone,3,4) );		
		} elseif ( 10  == strlen($phone) ) {
			return ( '(' . substr ( $phone, 0, 3 ) . ') ' . substr($phone, 3, 3) . '-' . substr($phone,6,4) );	
		} else {
			return ($phone);		
		}
    
    }

}

/**********************************************************************************
*
*  WIC Post Content Field
*
**********************************************************************************/

class WIC_Post_Content_Field extends WIC_Text_Field {
	
	$old_value = ''; 
	public function __construct( $args ) {
		parent::__construct( $args );
		$this->old_value = isset ( $_POST['old_' . $args['name'] ) ? $_POST['old_' . $args['name'] : '' ;
		$this->old_value = sanitize( $this_old_value ); 
	}

	public function sanitize( $dirty ) {
		$clean = strip_slashes( $dirty );	
	}

	public function search_control () {
		$this->control_args['read_only_flag'] = false;
		$this->control_args['field_label_suffix'] = ( $this->contains ) ? '*' : '';
		echo  = '<p>' . parent::create_control( $this->control_args ) . '<p>';
	}
	
	public function save_control () {
		if( ! $this->readonly ) {
			echo  = '<p>' . $this->create_control	( $this->control_args ) . '</p>';	
		}
	}
	
	public function update_control () {
		echo  = '<p>' . $this->create_control	( $this->control_args ) . '</p>';	
	}
	
	public function create_control ( $control_args ) {
		
		$control = '';		
		
		if ( ! $control_args['readonly'] ) { // for use with true wp posts
			$control_args['input_class'] = 'wic-input wic-wic-post-content';		
			control .= '<p>' . WIC_Textarea_Field::create_control ( $control_args ) . '</p>';
		}		
		
		$control_args['field_name_id'] = 'old_' . $args['name'];
		$control_args['read_only_flag']	= true;
		$control_args['input_class'] = 'hidden-template';
		$control_args['label_class'] = 'hidden-template';
		$control_args['value']	= $this->old_value;
		$control .= '<p>' . WIC_Textarea_Field::create_control ( $control_args ) . '</p>';
		
		$control.=  '<div id = "wic-old-wic-post-content">' .  balancetags( wp_kses_post ( $this->old_value ), true ) . '</div>'	
		/**
		* options considered for output sanitization of kses_post -- need to be good here, since new notes are just appended to old
		* with no filtering before this point ( on save/update take display value from prior form values (new appended to old), not database 	
		*		(1) esc_html not an option since shows html characters instead of using them format 
		*		(2) sanitize_text_field strips tags entirely
		*		(3) apply_filters('the_content', -- ) does nothing to address stray quotes or unbalanced tags (and would run shortcodes, etc.)
		*		(4) wp_kses_post leaves tags unbalanced but handles stray quotes
		*		(5) balancetags (with force set to true) still gets hurt by stray quotes
		*		CONCLUSION COMBINE 4 AND 5 -- EXPENSIVE, BUT APPROPRIATE, GIVEN RAW CONTENT BEING SERVED -- 
		*		NOTE: Wordpress does not bother to clean post_content up in this way (even through the admin interface) -- so conclude not necessary on save
		*      	-- only do it here for display; assume properly escaped for storage although not clean display
		*/
	
	}
	
	
	protected function data_array () {
		$data = array();
		if ( '' < $this->value ) {
			$data[$this->name] =  $this->format_note_content ( $this->value ) . $this->old_value;	
		}
		return ( $data );
	}

	 
	public function format_note_content ( $notes ) {

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

/*******************************************************************************
*
* WIC Select Control
*
********************************************************************************/
class WIC_Select_Field extends WIC_Text_Field {
	
	public function __construct ( $args ) {
		parent::construct ( $args )
		if ( isset ( $this->select_array ) ) {
			$control_args->select_array = $this->select_array 
		} elseif ( isset ( $this-select_function ) ) {
			$control_args->select_array = $this->select_function ( $select_parameter )		
		}
	}

	public function reformat_select_array ( $select_array ) {
		$reformatted_select_array = array();
		foreach ( $select_array as $pair ) {
			$reformatted_select_array[$pair['value']] = $pair['label'];
		} 	
		return ( $reformatted_select_array );
	}

	public function update_control () {
		if( ! $this->readonly ) {
			echo  = '<p>' . $this->create_control	( $this->control_args ) . '</p>';
		} else {
			// logic addresses the possibility that the select array may be smaller than the
			// array of possible values in the database -- as for example, if choices are closed off of the list 
			if ( ! isset( $this->list_call_back ) ) {
				if ( isset ( $this->select_array) {
					$lookup_array = reformat_select_array ( $this->select_array );
				} else {
					$lookup_array = reformat_select_array ( $this->select_function ( $this->select_parameter ) );
				}
				$this->control_args['value'] = $lookup_array[$this->value];
			} else  { 
				$this->control_args['value'] = $this->list_call_back ( $this->value );
			} 				
			echo  = '<p>' . parent::create_control	( $this->control_args ) . '</p>';
		}	
	}	
	
	public function create_control ( $control_args ) {

		$value = esc_html ( $value ); 

		extract ( $control_args, EXTR_SKIP ); 
		
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
}

/*******************************************************************************
*
*  WIC Text Field
*
*******************************************************************************/
class WIC_Text_Field extends WIC_Field {
	// named just for consistency
}

/*******************************************************************************
*
*  WIC Text Area Field
*
*******************************************************************************/
Class WIC_Textarea_Field extends WIC_field {

	public function create_control ( $control_args ) {
		
		extract ( $control_args, EXTR_SKIP ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . esc_attr( $field_label ) . '</label>' : '' ;
		$control .= '<textarea class="' . $input_class . '" id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) . '" type="text" placeholder = "' . 
			esc_attr( $placeholder ) . '" ' . $readonly  . '/>' . esc_textarea( $value ) . '</textarea>' . $field_label_suffix_span;
			
		return ( $control );

	}	

}


/*******************************************************************************
*
*  WIC Text CSV Field
*
*******************************************************************************/

class WIC_TextCSV_Field extends WIC_Field {

	/*
	* convert string with various possible white spaces and commas into comma separated	
	*/
	public function sanitize ( $textcsv ) {
		
		$textcsv = strip_tags( sanitize_text_field ( $textcsv ) );
		
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

}
 
  
   

    	

   	$clean_input['wic_post_id'] = absint ( $_POST['wic_post_id'] ); // always included in form; 0 if unknown;
		$clean_input['strict_match']	=	isset( $_POST['strict_match'] ) ? true : false; // only updated on the form; only affects search_wic_posts
		$clean_input['initial_form_state'] = 'wic-form-open';	
		
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



/*
*
*	The functions below are used to create the repeating special groups -- phones, emails, addresses
*    -- they build in logic about these types of data.
*
*  In every instance, the second position of the group array is the main datum.  
* 	It is extracted from the first instance in search functions requiring strings.
*
*  To add a new repeater group x, ( x like phones, emails, addresses ) 
*		+ add x to $multivalue_field_types above, 
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
	*  calls togglePostFormSection in wic-utilities.js
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
		' onclick="togglePostFormSection(\'' . $name_base . esc_attr( $name_variable ) . '\')" ' .
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


	
}

$wic_form_utilities = new WP_Issues_CRM_Form_Utilities;