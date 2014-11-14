<?php
/*
* wic-control-checked.php
*
*/
class WIC_Control_Checked extends WIC_Control_Parent {
	
	/* public to allow direct call for occasional non database use
	 -- must set up args:
		$control_args = array ( 
			'field_slug'		=> 'non blank',
			'field_label'			=>	'probably non blank ' , 'wp-issues-crm' ),
			'value'					=> 0,
			'readonly' => 0,
			'field_label_suffix' => '',
			'label_class' => '',				
		);	*/
	
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
