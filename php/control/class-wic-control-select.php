<?php
/*
* wic-control-select.php
*
*/
class WIC_Control_Select extends WIC_Control_Parent {
	
	
	public function search_control () {
		$final_control_args = $this->default_control_args;
		$final_control_args['readonly'] = false;
		$final_control_args['field_label_suffix'] = $final_control_args['like_search_enabled'] ? '(%)' : '';
		$final_control_args['value'] = $this->value;
		$final_control_args['required'] = false; // no field is required on search, except for search parms may be blank prohibited 
		$control =  $this->create_control( $final_control_args ) ;
		return ( $control ) ;
	}
	


	public function update_control () {
		$final_control_args = $this->default_control_args;
		$final_control_args['field_label_suffix'] = $this->set_required_values_marker ( $final_control_args['required'] );
		$final_control_args['value'] = $this->value;
		if ( $this->field->readonly ) {	
			$final_control_args['readonly_update'] = 1 ; // lets control know to only show the already set value if readonly
																		// (readonly control will not show at all on save, so need not cover that case)
		} 
			return ( $this->create_control ( $final_control_args ) );
	}	
	
	protected function create_control ( $control_args ) { 

		extract ( $control_args, EXTR_SKIP ); 
		
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';

		$control = '';
		
		$entity_class = 'WIC_Entity_' . $this->field->entity_slug;		
		if ( ! isset ( $readonly_update ) ) { // the usual mode -- show drop down		
			$not_selected_option = array (
				'value' 	=> '',
				'label'	=> $placeholder,								
			);  
			$getter = 'get_' . $this->field->field_slug . '_options';
			$option_array =  $entity_class::$getter();
			if ( 0 == $required && 0 == $blank_prohibited ) { // difference is that required is not a required setting on search, but blank_prohibited is 
				array_push( $option_array, $not_selected_option );
			}
		} else { // show just the already set option if a readonly field, but in update mode 
					// (if were to show as a readonly text, would lose the variable for later use)
			$getter = 'get_' . $this->field->field_slug . '_label';
			$option_array = array (
				array (
					'value' => $value,
					'label' => $entity_class::$getter( $value )
				)
			);			
		} 
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_slug ) . '">' . 
				esc_html( $field_label ) . '</label>' : '';
		$control .= '<select class="' . esc_attr( $input_class ) . ' ' .  esc_attr( $field_slug_css ) .'" id="' . esc_attr( $field_slug ) . '" name="' . esc_attr( $field_slug ) 
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


