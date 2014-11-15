<?php
/*
*
*	wic-entity-phone.php
*
*/



class WIC_Entity_Phone extends WIC_Entity_Multivalue {

	protected function set_entity_parms( $args ) {
		extract ( $args );
		$this->entity = 'phone';
		$this->entity_instance = $instance;
	} 

}