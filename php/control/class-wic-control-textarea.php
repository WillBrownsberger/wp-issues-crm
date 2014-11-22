<?php
/*
* class-wic-control-textarea.php
*
*
*/

class WIC_Control_Textarea extends WIC_Control_Parent {

	// when searching, offer a straight text control
	public function search_control () {
		$final_control_args = $this->default_control_args;
		$final_control_args['readonly'] = false;
		$final_control_args['field_label_suffix'] = $final_control_args['like_search_enabled'] ? '(%)' : '';
		$final_control_args['value'] = $this->value;
		$final_control_args['placeholder'] = '';
		$control =  parent::create_control( $final_control_args ) ;
		return ( $control ) ;
	}


	
	protected function create_control ( $control_args ) {
		
		extract ( $control_args, EXTR_SKIP ); 
	
		$readonly = $readonly ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_slug ) . '">' . esc_attr( $field_label ) . '</label>' : '' ;
		$control .= '<textarea class="' . $input_class . ' ' .  esc_attr( $field_slug_css ) . '" id="' . esc_attr( $field_slug ) . '" name="' . esc_attr( $field_slug ) . '" type="text" placeholder = "' . 
			esc_attr( $placeholder ) . '" ' . $readonly  . '/>' . esc_textarea( $value ) . '</textarea>' . $field_label_suffix_span;
			
		return ( $control );

	}	
	
	// when searching, submit compare type = scan which is interpreted as double wildcards, front and back
	public function create_search_clause ( $search_clause_args ) {

		extract ( $search_clause_args );
		 
		if ( '' == $this->value || 1 == $this->field->transient ) {
			return ('');		
		}
		// do two way wildcard for text areas;		
		$compare = 'scan';
		$compare = ( ( 0 == $match_level )|| $dup_check  ) ? '=' : $compare;
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
		
	
}

