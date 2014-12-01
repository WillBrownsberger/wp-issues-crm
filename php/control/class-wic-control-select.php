<?php
/*
* wic-control-select.php
*
*/
class WIC_Control_Select extends WIC_Control_Parent {
	
	public function search_control () {
		$final_control_args = $this->default_control_args;
		if ( ! $final_control_args['suppress_on_search'] ) {
			$final_control_args['readonly'] = false;
			$final_control_args['value'] = $this->value;
			$final_control_args['option_array'] =  $this->create_options_array ( $final_control_args );
			$final_control_args['required'] = ''; // fields never required on search; set explicitly here for correct result in create_options_array
			$control = $this->create_control( $final_control_args ) ;
			return ( $control ) ;
		}
	}	
	
	public function update_control () {
		$final_control_args = $this->default_control_args;
		$final_control_args['value'] = $this->value;
		if ( $this->field->readonly ) {	
			$final_control_args['readonly_update'] = 1 ; // lets control know to only show the already set value if readonly
																		// (readonly control will not show at all on save, so need not cover that case)
		} 
		$final_control_args['option_array'] =  $this->create_options_array ( $final_control_args );		
		$control =  $this->create_control( $final_control_args ) ;
		return ( $control );
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
			$final_control_args['option_array'] =  $this->create_options_array ( $final_control_args );
			return  ( static::create_control( $final_control_args ) );	
		}
	}	
	
	protected function create_options_array ( $control_args ) {

		extract ( $control_args, EXTR_SKIP );
		
		$entity_class = 'WIC_Entity_' . $this->field->entity_slug;		
		if ( ! isset ( $readonly_update ) ) { // the usual mode -- show drop down		
			$not_selected_option = array (
				'value' 	=> $this->field->is_int ? 0 : '',
				'label'	=> $placeholder,
			);  
			$getter = 'get_' . $this->field->field_slug . '_options';
			$option_array =  $entity_class::$getter( $value ); // include the value parameter to allow the getter to add the value to the array if needed
			if ( '' == $required && 0 == $blank_prohibited ) { // difference is that required is not a required setting on search, but blank_prohibited is 
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
	
		return ( $option_array );	
	}	
	
	public static function create_control ( $control_args ) { 
		
		extract ( $control_args, EXTR_SKIP ); 

		$control = '';
		
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_slug ) . '">' . 
				esc_html( $field_label ) . '</label>' : '';
		$control .= '<select class="' . esc_attr( $input_class ) . ' ' .  esc_attr( $field_slug_css ) .'" onchange ="' . $onchange . '"id="' . esc_attr( $field_slug ) . '" name="' . esc_attr( $field_slug ) 
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
		$control .=	$p . $r .	'</select>';
		return ( $control );
	
	}
		
}


