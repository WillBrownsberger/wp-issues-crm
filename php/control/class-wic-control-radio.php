<?php
/*
* wic-control-radio.php
*
*/
class WIC_Control_Radio extends WIC_Control_Select {
	
	public static function create_control ( $control_args ) { 
		
		extract ( $control_args, EXTR_SKIP ); 

		$control = '';
		
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_slug ) . '">' . 
				esc_html( $field_label ) . '</label>' : '';

		$default_unset = true;
		foreach ( $option_array as $option ) {
		
			if ( $blank_prohibited && $transient && $default_unset ) {
				$selected = ' checked ';	
				$default_unset = false;		
			} else {
				$selected = ( $value == $option['value'] ) ?	$selected = ' checked ' : '';
			}

			$control .= '<p class = "wic-radio-button" ><input ' . 
				'type 	= 	"radio" ' .
				'name		=	"' . esc_attr( $field_slug )  . '" ' .  
				'class	=	" wic-radio-button ' . esc_attr( $input_class ) . ' '  .  esc_attr( $field_slug_css ) .'" ' .
				'onchange=	"' . $onchange  					. '" ' .
				'value	=	"' . $option['value'] 	 		. '" ' .
				$selected .
				'>';
			$control .= esc_html ( $option['label'] );
			$control .= '</p>';
		}

		return ( $control );
	
	}
		
}


