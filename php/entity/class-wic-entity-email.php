<?php
/*
*
*	wic-entity-email.php
*
*/



class WIC_Entity_Email extends WIC_Entity_Multivalue {

	protected function set_entity_parms( $args ) {
		extract ( $args );
		$this->entity = 'email';
		$this->entity_instance = $instance;
	} 

}