<?php
/*
*
*  class-wic-form-multivalue-search.php
*
*	this form is for an instance of a multivalue field -- a row (or rows, if multiple groups) of controls, not a full form
*	  
*	entity in this context is the entity that the multivalue field may contain several instances of 
*   -- this form generator doesn't need to know the instance value; 
* 	 -- the control objects within each row know which row they are implementing
*
*/

class WIC_Form_Multivalue_Search extends WIC_Form_Parent  {
	
	
	protected $entity = '';
	
	public function __construct ( $entity ) {
		$this->entity = $entity; 
	}
	
	protected function get_the_entity() {
		return ( $this->entity );	
	}
	
	protected function get_the_buttons(){}
	protected function get_the_header ( &$data_array ) {}	
	protected function get_the_formatted_control ( $control ) {
		$args = array();
		return ( $control->search_control( $args ) ); 
	}
	protected function get_the_legends() {}	
	
	public function layout_form ( &$data_array, $message, $message_level ) {
	
		$groups = $this->get_the_groups();
		$search_row = '<div id="wic-multivalue-block">';
			foreach ( $groups as $group ) {
				 $search_row .= '<div class = "wic-multivalue-field-subgroup" id = "wic-field-subgroup-' . esc_attr( $group->group_slug ) . '">';
						$group_fields = WIC_DB_Dictionary::get_fields_for_group ( $this->get_the_entity(), $group->group_slug );
						$search_row .= '<p>' . 
							$this->the_controls ( $group_fields, $data_array )
						   . '</p>' 
					. '</div>';
			} 
		$search_row .= '</div>';
		return $search_row;
	}
	
	protected function the_controls ( $fields, &$data_array ) {
		$controls = '';
		foreach ( $fields as $field ) {
			$controls .= $this->get_the_formatted_control ( $data_array[$field] );
		}
		return $controls;
	}
	
}