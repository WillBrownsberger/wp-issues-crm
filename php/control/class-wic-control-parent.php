<?php
/*
* wic-field.php
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
*  WIC Field
*
************************************************************************************/
abstract class WIC_Control_Parent {
	protected $field;
	protected $default_control_args = array(
		'field_label' 				=> '',
		'value'						=> '',
		'like_search_enabled' 	=> false,
		'readonly' 					=> false,
		'required'					=> '',
		'hidden'						=> false,
		// elements below not derived directly from field settings, but from combination of field type and settings
		'field_label_suffix' 	=> '',
		'input_class' 				=> 'wic-input',
		'label_class' 				=> 'wic-label',
		'placeholder' 				=> '',
		);

	protected $value = '';	
	// parameters for text control creation -- the text control is used by multiple extensions of the class


	public function initialize_default_values ( $entity, $field_slug ) {
		$this->field = WIC_DB_Dictionary::get_field_rules( $entity, $field_slug );
		$this->default_control_args =  array_merge( $this->default_control_args, get_object_vars ( $this->field ) );
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
	

	public static function new_control () {
		$this->search_control();
	}

	public function search_control ( $control_args ) {
		$final_control_args = array_merge ( $this->default_control_args, $control_args );
		$final_control_args['readonly'] = false;
		$final_control_args['field_label_suffix'] = $final_control_args['like_search_enabled'] ? '(%)' : '';
		$final_control_args['value'] = $this->value;
		$control = '<p>'. $this->create_control( $final_control_args ) . '</p>';
		return ( $control ) ;
	}
	
	public function save_control ( $control_args ) {
		$final_control_args = array_merge ( $this->default_control_args, $control_args );
		if( ! $final_control_args['readonly'] ) {
			$final_control_args['field_label_suffix'] = $this->set_required_values_marker ( $final_control_args['required'] );
			$final_control_args['value'] = $this->value;
			return  ( '<p>'. $this->create_control( $control_args ) . '</p>' );	
		}
	}
	
	public function update_control ( $control_args ) {
		$final_control_args = array_merge ( $this->default_control_args, $control_args );
		$final_control_args['field_label_suffix'] = $this->set_required_values_marker ( $final_control_args['required'] );
		$final_control_args['value'] = $this->value;
		return ( '<p>'. $this->create_control( $final_control_args ) . '</p>' );	
	}

	protected function create_control ( $control_args ) { // basic create text control
		
		extract ( $control_args, EXTR_SKIP );
		 
		$readonly = $readonly ? 'readonly' : '';
		$type = ( 1 == $hidden ) ? 'hidden' : 'text';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' . $field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' && ! ( 1 == $hidden ) ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_slug ) . '">' . esc_html( $field_label ) . '</label>' : '' ;
		$control .= '<input class="' . $input_class . '" id="' . esc_attr( $field_slug )  . 
			'" name="' . esc_attr( $field_slug ) . '" type="' . $type . '" placeholder = "' .
			 esc_attr( $placeholder ) . '" value="' . esc_attr ( $value ) . '" ' . $readonly  . '/>' . $field_label_suffix_span; 
			
		return ( $control );

	}

	public function set_required_values_marker ( $required ) {
		$required_individual = ( 'individual' == $required ) ? '*' : '';
		$required_group = ( 'group' == $required ) ? '(+)' : '';
		// won't actually be both, but may be one or the other
		return ( $required_individual . $required_group );  
	}


	/*********************************************************************************
	*
	* control sanitize -- will handle all including multiple values -- generic case is string
	*
	*********************************************************************************/

	public function sanitize() {
		$sanitizor = $this->field->sanitize_call_back;
		// apply stripslashes and sanitize text field 
		// note that these sanitization call backs are generic functions or are in wp-issues-crm.php)
		$sanitizor = $sanitizor > '' ? $sanitizor : 'wic_generic_sanitizor';
		$this->value = $sanitizor ( $this->value );
	}

	/*********************************************************************************
	*
	* create where/join clause components for control elements in generic wp form 
	*
	*********************************************************************************/

	public function create_search_clauses () {
		if ( '' == $this->value ) {
			return ('');		
		}		
		$compare = $this->field->like_search_enabled ? 'like' : '=';
		$compare = ( $this->get_strict_match_setting() ) ? '=' : $compare;
		$query_clauses = array (
			'join_clause' 	=> '',
			'where_clause' => array (
				'key' 	=> $this->field->field_slug,
				'value'	=> $this->value,
				'compare'=> $compare,
			)			
		);
		return ( $query_clauses );
	}

	
	protected function get_strict_match_setting() {
		$strict_match = isset ( $_POST['strict_match'] )  ? true : false;
		return ( $strict_match );
	}
	

}
