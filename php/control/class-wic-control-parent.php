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
	*	The control create functions COULD be promiscuous in that they get their control arguments from multiple places.
	*		Instead, limit input to rules from database on initialization and 
	*		-- rules specified in the named control function (search_control, update_control) 
	*		In child controls, may allow direct passage of arguments -- see checked and multivalue.
	*		Note that have potential to get css specified to them based on their field slug
	*/


	public function initialize_default_values ( $entity, $field_slug, $instance ) {
		$this->field = WIC_DB_Dictionary::get_field_rules( $entity, $field_slug );
		$this->default_control_args =  array_merge( $this->default_control_args, get_object_vars ( $this->field ) );
		$this->default_control_args['field_slug_css'] = str_replace( '_', '-', $field_slug );
		$this->default_control_args['field_slug'] = ( '' == $instance ) ? // ternary
				// if no instance supplied, this is just a field in a main form, and use field slug for field name and field id
				$field_slug :
				// if an instance is supplied prepare to output the field as an array element, i.e., a row in a multivalue field 
				// note that the entity name for a row object in a multivalue field is the same as the field_slug for the multivalue field
				// this is a trap for the unwary in setting up the dictionary table 
				$entity . '[' . $instance . ']['. $field_slug . ']';
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
	
	public function get_display_value () {
		if ( '' < $this->field->format_call_back  ) {
			return $this->field->format_call_back( $this->value );
		} else {
			return ( $this->value ) ;		
		}	
	}
	
	public function reset_value() {
		$this->value = '';	
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
		$final_control_args['readonly'] = false;
		$final_control_args['field_label_suffix'] = $final_control_args['like_search_enabled'] ? '(%)' : '';
		$final_control_args['value'] = $this->value;
		$control =  $this->create_control( $final_control_args ) ;
		return ( $control ) ;
	}
	
	public function save_control () {
		$final_control_args = $this->default_control_args;
		if( ! $final_control_args['readonly'] ) {
			$final_control_args['field_label_suffix'] = $this->set_required_values_marker ( $final_control_args['required'] );
			$final_control_args['value'] = $this->value;
			return  ( $this->create_control( $final_control_args ) );	
		}
	}
	
	public function update_control () { 
		$final_control_args = $this->default_control_args;
		$final_control_args['field_label_suffix'] = $this->set_required_values_marker ( $final_control_args['required'] );
		$final_control_args['value'] = $this->value;
		return ( $this->create_control( $final_control_args )  );	
	}

	protected function create_control ( $control_args ) { // basic create text control, accessed through control methodsabove
    
		extract ( $control_args, EXTR_OVERWRITE );
		$readonly = $readonly ? 'readonly' : '';
		$type = ( 1 == $hidden ) ? 'hidden' : 'text';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' . $field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' && ! ( 1 == $hidden ) ) ? '<label class="' . esc_attr ( $label_class ) .
				 ' ' . esc_attr( $field_slug_css ) . '" for="' . esc_attr( $field_slug ) . '">' . esc_html( $field_label ) . '</label>' : '' ;
		$control .= '<input class="' . esc_attr( $input_class ) . ' ' .  esc_attr( $field_slug_css ) . '" id="' . esc_attr( $field_slug )  . 
			'" name="' . esc_attr( $field_slug ) . '" type="' . $type . '" placeholder = "' .
			 esc_attr( $placeholder ) . '" value="' . esc_attr ( $value ) . '" ' . $readonly  . '/>' . $field_label_suffix_span; 
			
		return ( $control );

	}

	protected function set_required_values_marker ( $required ) {
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
	* control validate -- will handle all including multiple values -- generic case is string
	*
	*********************************************************************************/

	public function validate() {
		$validation_error = '';
		$validator = $this->field->validate_call_back;
		if ( $validator > '' ) {
			$validation_error = $validator ( $this->value );
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
		if ( "individual" == $this->field->required && ! is_present() ) {
			return ( sprintf ( __( ' %s is a required field. ', 'wp-issues-crm' ), $this->field->field_label ) ) ;		
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

	public function create_search_clause ( $dup_check ) {
		if ( '' == $this->value || 1 == $this->field->transient ) {
			return ('');		
		}		
		$compare = $this->field->like_search_enabled ? 'like' : '=';
		$compare = ( $this->get_strict_match_setting() || $dup_check  ) ? '=' : $compare;
		$query_clause =  array ( // double layer array to standardize a return that allows multivalue fields
				array (
					'table'	=> $this->field->entity_slug,
					'key' 	=> $this->field->field_slug,
					'value'	=> $this->value,
					'compare'=> $compare,
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
			// exclude transient and readonly fields, but since want to be able to treat ID as readonly
			// and allow direct search by it for constituents, add that ID will be passed through -- see special handling 
			// for ID, which is a where condition on an update in WIC_DB_Access_WIC::db_update
			$update_clause = array (
					'key' 	=> $this->field->field_slug,
					'value'	=> $this->value,
			);
			return ( $update_clause );
		}
	}
	
	protected function get_strict_match_setting() {
		$strict_match = isset ( $_POST['strict_match'] )  ? true : false;
		return ( $strict_match );
	}
	

}
