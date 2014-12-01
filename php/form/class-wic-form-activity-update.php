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

class WIC_Form_Activity_Update extends WIC_Form_Multivalue_Update  {
	
	public function __construct ( $entity, $instance ) {
		$this->entity = $entity;
		$this->entity_instance = $instance;
	}

		
	protected function get_the_formatted_control ( $control ) { 
		return ( $control->update_control() ); 
	}
		
	
	public function layout_form ( &$data_array, $message, $message_level, $sql = '' ) { 
		$groups = $this->get_the_groups();
		$class = ( 'row-template' == $this->entity_instance ) ? 'hidden-template' : 'visible-templated-row';
		$search_row = '<div class = "'. $class . '" id="' . $this->entity . '[' . $this->entity_instance . ']">';
		$search_row .= '<div id="wic-multivalue-block">';
			foreach ( $groups as $group ) { 
				 $search_row .= '<div class = "wic-multivalue-field-subgroup wic-field-subgroup-' . esc_attr( $group->group_slug ) . '">';
						$group_fields = WIC_DB_Dictionary::get_fields_for_group ( $this->get_the_entity(), $group->group_slug );
						$search_row .= $this->the_controls ( $group_fields, $data_array );
						if ( 'activity' == $group->group_slug ) {
							$list_button_args = array(
								'entity_requested'	=> 'issue',
								'action_requested'	=> 'id_search',
								'button_class' 		=> 'wic-form-button wic-activity-issue-link-button',
								'id_requested'			=> $data_array['issue']->get_value(),
								'button_label' 		=> __( 'View Issue', 'wp-issues-crm' )				
							);			
						$search_row .=  WIC_Form_Parent::create_wic_form_button( $list_button_args );	
						}
				$search_row .= '</div>';
			} 
		$search_row .= '</div></div>';
		return $search_row;
	}

}