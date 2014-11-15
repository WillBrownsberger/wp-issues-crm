<?php
/*
*
*	wic-entity-address.php
*
*/



class WIC_Entity_Address extends WIC_Entity_Multivalue {

	protected function set_entity_parms( $args ) {
		extract ( $args );
		$this->entity = 'address';
		$this->entity_instance = $instance;
	} 

}