<?php
/*
*
*	wic-constituent.php
*
*/

class WIC_Constituent extends WIC_Entity {

	protected function set_entity_parms() {
		$this->entity = 'constituent';
		$this->entity_type = 'WIC';	
	}

	protected function new_form() {
		$new_constituent_search = new WIC_Constituent_Search_Form;
		$new_constituent_search->layout_form( null, '', 'wic_form_no_errors' );
	}	

}