<?php
/*
* wic-control-multivalue.php
*
*/
class WIC_Control_Multivalue extends WIC_Control_Parent {
	
	
	public function initialize_default_values ( $entity, $field_slug ) {
		$this->field = WIC_DB_Dictionary::get_field_rules( $entity, $field_slug );
		$this->default_control_args = array_merge( $this->default_control_args, get_object_vars ( $this->field ) );
		$class_name = 'WIC_Entity_' . $field_slug;
		$this->value = new $class_name;
		$this->value->initialize_data_object_array();
	}

	public function search_control ( $control_args ) {
		$final_control_args = array_merge ( $this->default_control_args, $control_args );
		$final_control_args['readonly'] = false;
		$final_control_args['field_label_suffix'] = $final_control_args['like_search_enabled'] ? '(%)' : '';
		$final_control_args['value'] = $this->value;
		$control =  $this->create_control( $final_control_args ) ;
		return ( $control ) ;
	}
	
	
	public function create_control ( $control_args ) {
		
		$input_class = 'wic_input_checked';

		extract ( $control_args, EXTR_SKIP ); 
	
		$readonly = $readonly ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ?  '<label class="' . $label_class . '" for="' . 
				esc_attr( $field_slug ) . '">' . esc_html( $field_label ) . ' ' . '</label>' : '';
		$control .= '<input class="' . $input_class . '"  id="' . esc_attr( $field_slug ) . '" name="' . esc_attr( $field_slug ) . 
			'" type="checkbox"  value="1"' . checked( $value, 1, false) . $readonly  .'/>' . 
			$field_label_suffix_span  ;	

		return ( $control );

	}	

}	
