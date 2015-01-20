<?php
/*
*
*  class-wic-form-multivalue-update.php
*
*	this form is for an instance of a multivalue field -- a row (or rows, if multiple groups) of controls, not a full form
*	  
*	entity in this context is the entity that the multivalue field may contain several instances of 
*   -- this form generator doesn't need to know the instance value; 
* 	 -- the control objects within each row know which row they are implementing
*
*/

class WIC_Form_Multivalue_Update extends WIC_Form_Multivalue_Search  {
	
	public function __construct ( $entity, $instance ) {
		$this->entity = $entity;
		$this->entity_instance = $instance;
	}

		
	protected function get_the_formatted_control ( $control ) { 
		return ( $control->update_control() ); 
	}
		
	
	public function layout_form ( &$data_array, $message, $message_level, $sql = '' ) { 
	
		global $wic_db_dictionary;

		$groups = $this->get_the_groups();
		$class = ( 'row-template' == $this->entity_instance ) ? 'hidden-template' : 'visible-templated-row';
		$update_row = '<div class = "'. $class . '" id="' . $this->entity . '[' . $this->entity_instance . ']">';
		$update_row .= '<div class="wic-multivalue-block ' . $this->entity . '">';
			foreach ( $groups as $group ) { 
				 $update_row .= '<div class = "wic-multivalue-field-subgroup wic-field-subgroup-' . esc_attr( $group->group_slug ) . '">';
						$group_fields = $wic_db_dictionary->get_fields_for_group ( $this->get_the_entity(), $group->group_slug );
						$update_row .= $this->the_controls ( $group_fields, $data_array );
				$update_row .= '</div>';
			} 
		$update_row .= '</div></div>';
		return $update_row;
	}

}