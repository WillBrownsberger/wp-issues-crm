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

	private static $address_type_options	= array(	
		array(
			'value'	=> '0',
			'label'	=>	'Home' ),
		array(
			'value'	=> '1',
			'label'	=>	'Work' ),
		array(
			'value'	=> '2',
			'label'	=>	'Mail' ),
		array(
			'value'	=> '3',
			'label'	=>	'Other' ),
		);

	public static function get_address_type_options() {
		return self::$address_type_options; 
	}

	private static $state_options = array (
		array(
			'value'	=> 'MA',
			'label'	=>	'MA'),
		);		

	public static function get_state_options() {
		return self::$state_options; 
	}


}