<?php
/*
* class-wic-control-parent.php
*
* This file contains WIC_Control base class and child classes with names of the form WIC_Control_{Type} 
* This is the list of valid Types for WIC Fields:
*		-- Checked
*		--	Date
*		-- Select
*		-- Text
* 
* It also includes validation and sanitization and db query element functions that depend on control structure 
*   
*
*
*
*/

/************************************************************************************
*
*  WIC Control Parent
*
************************************************************************************/
abstract class WIC_Control_Parent {
	protected $field;
	protected $default_control_args = array();
	protected $value = '';	


	/***
	*
	*	The control create functions are a little promiscuous in that they gather their control arguments from multiple places.
	*		Field rules from database on initialization  
	*		Rules specified in the named control function (search_control, update_control) 
	*		In child controls, may allow direct passage of arguments -- see checked and multivalue.
	*		Note that have potential to get css specified to them based on their field slug
	*		Any special validation, sanitization, formatting and defaults values ( as opposed to default rule values ) are supplied from the relevant object
	*/


	public function initialize_default_values ( $entity, $field_slug, $instance ) {
	// initialize the default values of field RULES  
		$this->field = WIC_DB_Dictionary::get_field_rules( $entity, $field_slug );
		$this->default_control_args =  array_merge( $this->default_control_args, get_object_vars ( $this->field ) );
		$this->default_control_args['field_slug_css'] = str_replace( '_', '-', $field_slug );
		$this->default_control_args['field_slug_stable'] = $field_slug; 
		// retain this value arg so don't need to parse out instance in static create control function where don't have $this->field->field_slug to refer to
		$this->default_control_args['field_slug'] = ( '' == $instance ) ? // ternary
				// if no instance supplied, this is just a field in a main form, and use field slug for field name and field id
				$field_slug :
				// if an instance is supplied prepare to output the field as an array element, i.e., a row in a multivalue field 
				// note that the entity name for a row object in a multivalue field is the same as the field_slug for the multivalue field
				// this is a trap for the unwary in setting up the dictionary table 
				$entity . '[' . $instance . ']['. $field_slug . ']';
		// initialize the value of the control
		$this->reset_value();		
	}

	/*********************************************************************************
	*
	* methods for control creation for different types of forms -- new, search, save, update
	*
	***********************************************************************************/
	
	public function set_value ( $value ) {
		$this->value = $value;	
	}
	
	public function get_value () {
		return $this->value;	
	}
	
/*	public function get_display_value () {
		if ( '' < $this->field->format_call_back  ) {
			return $this->field->format_call_back( $this->value );
		} else {
			return ( $this->value ) ;		
		}	
	} */
	
	public function reset_value() {
		$this->value = '';	
	}

	public function get_wp_query_parameter() {
		return ( $this->field->wp_query_parameter );	
	}

	/*********************************************************************************
	*
	* methods for control creation for different types of forms -- new, search, save, update
	*
	***********************************************************************************/
	
	public static function new_control () {
		$this->search_control();
	}

	public function search_control () {
		$final_control_args = $this->default_control_args;
		if ( ! $final_control_args['suppress_on_search'] ) {
			$final_control_args['readonly'] = false;
			$final_control_args['value'] = $this->value;
			$control =  static::create_control( $final_control_args ) ;
			return ( $control ) ;
		}
	}
	
	public function save_control () {
		$final_control_args = $this->default_control_args;
		if( ! $final_control_args['readonly'] ) {
	    	$class_name = 'WIC_Entity_' . $this->field->entity_slug;
			$set_default = $this->field->field_slug . '_set_default';
			if ( method_exists ( $class_name, $set_default ) ) { 
				$final_control_args['value'] = $class_name::$set_default ( $this->value );
			} else {
				$final_control_args['value'] = $this->value;
			}
			return  ( static::create_control( $final_control_args ) );	
		}
	}
	
	public function update_control () { 
		$final_control_args = $this->default_control_args;
		$final_control_args['value'] = $this->value;
		return ( static::create_control( $final_control_args )  );	
	}

	protected static function create_control ( $control_args ) { // basic create text control, accessed through control methodsabove

		extract ( $control_args, EXTR_OVERWRITE );  
		
		$value = ( '0000-00-00' == $value ) ? '' : $value; // don't show date fields with non values; 
		
     	$class_name = 'WIC_Entity_' . $entity_slug; 
		$formatter = $field_slug_stable . '_formatter'; // ( field slug has instance args in it )
		if ( method_exists ( $class_name, $formatter ) ) { 
			$value = $class_name::$formatter ( $value );
		}

		$readonly = $readonly ? 'readonly' : '';
		$type = ( 1 == $hidden ) ? 'hidden' : 'text';
		 
		$control = ( $field_label > '' && ! ( 1 == $hidden ) ) ? '<label class="' . esc_attr ( $label_class ) .
				 ' ' . esc_attr( $field_slug_css ) . '" for="' . esc_attr( $field_slug ) . '">' . esc_html( $field_label ) . '</label>' : '' ;
		$control .= '<input class="' . esc_attr( $input_class ) . ' ' .  esc_attr( $field_slug_css ) . '" id="' . esc_attr( $field_slug )  . 
			'" name="' . esc_attr( $field_slug ) . '" type="' . $type . '" placeholder = "' .
			 esc_attr( $placeholder ) . '" value="' . esc_attr ( $value ) . '" ' . $readonly  . '/>'; 
			
		return ( $control );

	}


	/*********************************************************************************
	*
	* control sanitize -- will handle all including multiple values -- generic case is string
	*
	*********************************************************************************/

	public function sanitize() {  
		$class_name = 'WIC_Entity_' . $this->field->entity_slug;
		$sanitizor = $this->field->field_slug . '_sanitizor';
		if ( method_exists ( $class_name, $sanitizor ) ) { 
			$this->value = $class_name::$sanitizor ( $this->value );
		} else { 
			$this->value = sanitize_text_field ( stripslashes ( $this->value ) );		
		} 
		if ( $this->field->is_date && $this->value > '' ) { 				
			$this->value = $this->sanitize_date ( $this->value );	
		}
	}

	/*
	* date sanitization function ( no error message for bad date, but will fail a required test )
	*
	*/   
	protected function sanitize_date ( $possible_date ) {
		try {
			$test = new DateTime( $possible_date );
		}	catch ( Exception $e ) {
			return ( '' );
		}	   			
 		return ( date_format( $test, 'Y-m-d' ) );
	}
   

	/*********************************************************************************
	*
	* control validate -- will handle all including multiple values -- generic case is string
	*
	*********************************************************************************/

	public function validate() { 
		$validation_error = '';
		$class_name = 'WIC_Entity_' . $this->field->entity_slug;
		$validator = $this->field->field_slug . '_validator';
		if ( method_exists ( $class_name, $validator ) ) { 
			$validation_error = $class_name::$validator ( $this->value );
		}
		return $validation_error;
	}

	/*********************************************************************************
	*
	* report whether field should be included in deduping.
	*
	*********************************************************************************/


	public function dup_check() {
		return $this->field->dedup;	
	}
	/*********************************************************************************
	*
	* report whether field is transient
	*
	*********************************************************************************/


	public function is_transient() {
		return ( $this->field->transient );	
	}

	
	
	
	/*********************************************************************************
	*
	* report whether field is multivalue
	*
	*********************************************************************************/


	public function is_multivalue() {
		return ( $this->field->field_type == 'multivalue' );	
	}

	/*********************************************************************************
	*
	* report whether field fails individual requirement
	*
	*********************************************************************************/
	public function required_check() { 
		if ( "individual" == $this->field->required && ! $this->is_present() ) {
			return ( sprintf ( __( ' %s is required. ', 'wp-issues-crm' ), $this->field->field_label ) ) ;		
		} else {
			return '';		
		}	
	}

	/*********************************************************************************
	*
	* report whether field is present as possibly required -- note that is not trivial for multivalued
	*
	*********************************************************************************/
	public function is_present() {
		$present = ( '' < $this->value ); 
		return $present;		
	}
	
	/*********************************************************************************
	*
	* report whether field is required in a group 
	*
	*********************************************************************************/
	public function is_group_required() {
		$group_required = ( 'group' ==  $this->field->required ); 
		return $group_required;		
	}


	/*********************************************************************************
	*
	* create where/join clause components for control elements in generic wp form 
	*
	*********************************************************************************/

	public function create_search_clause ( $search_clause_args ) {
		
		// expecting $match_level and $dup_check, but want errors if not supplied, so no defaults
		// match level is 0 for strict, 1 for like, 2 for soundex like
		// dedup is true or false	
		// added $category_search_mode == '' if not set on screen, but see WIC_DB_Access_WP for allowed values	
		
		extract ( $search_clause_args, EXTR_OVERWRITE );
		
		if ( ! isset( $match_level ) || ! isset ( $dup_check ) ) {
			die ( sprintf ( __( 'Missing parameters for WIC_Control_Parent::create_search_clause() for %1$s.', 'wp-issues-crm' ),
				 $this->field->field_slug  ) );
		}
		
		if ( '' == $this->value || 1 == $this->field->transient || ( 0 == $this->value && $this->field->zero_is_null ) ) {
			return ('');		
		}
		if ( 0 == $match_level || $dup_check || 0 == $this->field->like_search_enabled )  {
			$compare = '=';
			$key = $this->field->field_slug;							
		} elseif ( 1 == $match_level || ( 1 == $this->field->like_search_enabled )) {
			$compare = 'like';
			$key 	= $this->field->field_slug;	
		} elseif ( 2 == $match_level && 2 == $this->field->like_search_enabled ) {
			$compare = 'sound';
			$key 	= $this->field->field_slug . '_soundex';	
		} else {
			die ( sprintf( __( 'Incorrect match_level settings for field %1$s reported by WIC_Control_Parent::create_search_clause.', 'WP_Issues_CRM' ),
				 $this->field->field_slug ) ); 		
		}	
		
		if ( 'cat' == $this->field->wp_query_parameter && '' < $category_search_mode ) {
			$compare = $category_search_mode; // this will actually be parsed in as a $query argument with = as compare 		
		} 			
 
		if ( '' < $this->field->secondary_alpha_search ) { // exists to support address_line search by street name without full text scanning 
 			if ( ! is_numeric ( $this->value[0] ) ) {
				$key = $this->field->secondary_alpha_search; 			
 			}
		} 
 
		$query_clause =  array ( // double layer array to standardize a return that allows multivalue fields
				array (
					'table'	=> $this->field->entity_slug,
					'key' 	=> $key,
					'value'	=> $this->value,
					'compare'=> $compare,
					'wp_query_parameter' => $this->field->wp_query_parameter,
				)
			);
		return ( $query_clause );
	}
	
	/*********************************************************************************
	*
	* create set array or sql statements for saves/updates 
	*
	*********************************************************************************/
	public function create_update_clause () {
		if ( ( ( ! $this->field->transient ) && ( ! $this->field->readonly ) ) || 'ID' == $this->field->field_slug ) {
			// exclude transient and readonly fields.   ID as readonly ( to allow search by ID), but need to pass it anyway.
			// ID is a where condition on an update in WIC_DB_Access_WIC::db_update
			$update_clause = array (
					'key' 	=> $this->field->field_slug,
					'value'	=> $this->value,
					'wp_query_parameter' => $this->field->wp_query_parameter,
					'soundex_enabled' => ( 2 == $this->field->like_search_enabled ),
					'secondary_alpha_search' => $this->field->secondary_alpha_search,
			);
			return ( $update_clause );
		}
	}

}
