<?php
/*
* wic-control-select.php
*
*/
class WIC_Control_Multiselect extends WIC_Control_Select {
	
	public function reset_value() {
		$this->value = array();	
	}
	
	public static function create_control ( $control_args ) {

		extract ( $control_args, EXTR_OVERWRITE ); 
		
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';

		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_slug ) . '">' . esc_attr( $field_label ) . '</label>' : '' ;

		$control .= '<div class = "wic_multi_select">';
		$unselected = '';
		var_dump ( $value );
		foreach ( $option_array as $option ) {
			if ( ! '' == $option['value'] ) { // discard the blank option embedded for the select control 
				$args = array(
					'field_slug' 			=> $field_slug . '[' . $option['value'] . ']',
					'field_label'			=>	$option['label'],
					'label_class'			=> 'wic-multi-select-label '  . $option ['class'],
					'input_class'			=> 'wic-multi-select-checkbox ', 
					'value'					=> in_array ( $option['value'], $value, false ),
					'readonly'				=>	false,
					'field_label_suffix'	=> '',						
				);	
				if ( in_array ( $option['value'], $value, false ) ) {
					$control .= '<p class = "wic_multi_select_item" >' . WIC_Control_Checked::create_control($args) . '</p>';
				} else {
					$unselected .= '<p class = "wic_multi_select_item" >' . WIC_Control_Checked::create_control($args) . '</p>';				
				}
			}
		}
		$control .= $unselected . '</div>';
		return ( $control );
	
	}	
	
	public function sanitize () {
		foreach ( $this->value as $key => $value ) {
			if ( $value != absint( $value ) ) {
				die ( sprintf ( __( 'Invalid value for multiselect field %s', 'wp-issues-crm' ), $this->field->field_slug ) );			
			}	
		}			
	}
	
	public function create_update_clause () {
		if  ( ( ! $this->field->transient ) && ( ! $this->field->readonly ) )  {
			// exclude transient and readonly fields.   

			$selected_array = array();
			foreach ( $this->value as $key => $selected ) {
				if ( $selected ) {
					$selected_array[] = $key;
				} 			
			}

			$update_clause = array (
					'key' 	=> $this->field->field_slug,
					'value'	=> $selected_array,
					'wp_query_parameter' => $this->field->wp_query_parameter,
					'soundex_enabled' => ( 2 == $this->field->like_search_enabled ),
			);
			return ( $update_clause );
		}
	}	
	
	
}


