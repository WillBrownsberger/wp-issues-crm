<?php
/*
* class-wic-control-range.php
*
*/ 

class WIC_Control_Range extends WIC_Control_Parent {
	
	private $value_lo = '';
	private $value_hi = '';	
	
	public function search_control () {
		$final_control_args = $this->default_control_args;
		$final_control_args['readonly'] = false;
		$final_control_args['field_label_suffix'] = $final_control_args['like_search_enabled'] ? '(%)' : '';
		$final_control_args['value'] = $this->value;
		$field_slug_base = $final_control_args['field_slug'];
		$final_control_args['field_slug'] = $field_slug_base . '[lo]';	
		$final_control_args['field_label'] = $final_control_args['field_label'] . ' >= ';
		$control = $this->create_control( $final_control_args ) ;
		$final_control_args['field_label'] = ' <= ';		
		$final_control_args['field_slug'] = $field_slug_base . '[hi]'; 
		$control .= $this->create_control( $final_control_args ) ;
		return ( $control );
	}
		
	public function set_value( $value ) {
		if ( is_array ( $value ) ) {
			extract ( $value );
			$this->value_lo = $lo;
			$this->value_hi = $hi;		
		} else {
			$this->value = $value;		
		}
	}

	public function create_search_clause ( $dup_check ) {
		
		if ( $dup_check ) {
			$query_clause = parent::create_search_clause ( $dup_check );
			return ( $query_clause );		
		}
		 	
		if ( ( '' == $this->value && '' == $this->value_lo && '' == $this->value_hi ) || 1 == $this->field->transient ) {
			return ('');
		} elseif ( $this->value_lo > '' && $this->value_hi > '' ) {
			$query_clause = array ( 
				array (
					'table'	=> $this->field->entity_slug,
					'key' => $this->field->field_slug,
					'value'	=> array (
						$this->value_lo,
						$this->value_hi,
						),
					'compare'=> 'BETWEEN',
					)
				);
		} elseif ( $this->value_lo > '' ) {
			$query_clause = array ( 
				array (
					'table'	=> $this->field->entity_slug,
					'key' => $this->field->field_slug,
					'value'	=> $this->value_lo,
					'compare'=> '>',
					)
				);
		} elseif ( $this->value_hi > '' ) {
			$query_clause = array ( 
				array (
					'table'	=> $this->field->entity_slug,
					'key' => $this->field->field_slug,
					'value'	=> $this->value_hi,
					'compare'=> '<',
					)
				);
		}

		return ( $query_clause );

	}	

}

