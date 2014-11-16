<?php
/*
* wic-control-select.php
*
*/
class WIC_Control_Select extends WIC_Control_Parent {
	


	public function update_control () {
		$final_control_args = $this->default_control_args;
		$final_control_args['field_label_suffix'] = $this->set_required_values_marker ( $final_control_args['required'] );
		$final_control_args['value'] = $this->value;
		if ( $this->field->readonly ) {	
			$final_control_args['readonly_update'] = 1 ; // lets control know to only show one the already set value if readonly
		} 
			return ( $this->create_control ( $final_control_args ) );
	}	
	
	protected function create_control ( $control_args ) { 

		extract ( $control_args, EXTR_SKIP ); 
		
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';

		$control = '';
				
		if ( ! isset ( $readonly_update ) ) {		
			$not_selected_option = array (
				'value' 	=> '',
				'label'	=> $placeholder,								
			);  
			$getter = 'get_' . $this->field->field_slug . '_options';
			$option_array =  WIC_Control_Options::$getter(); 
			array_push( $option_array, $not_selected_option );
		} else { 
			$getter = 'get_' . $this->field->field_slug . '_label';
			$option_array = array (
				array (
					'value' => $value,
					'label' => WIC_Control_Options::$getter( $value )
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


