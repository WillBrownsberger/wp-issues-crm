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

	public static function phone_sanitizor ( $raw_phone ) {
		return ( preg_replace("/[^0-9]/", '', $raw_phone) ) ;
	}
	
	public static function phone_formatter ( $raw_phone ) {
		   	
		$phone = preg_replace( "/[^0-9]/", '', $raw_phone );
   	
		if ( 7 == strlen($phone) ) {
			return ( substr ( $phone, 0, 3 ) . '-' . substr($phone,3,4) );		
		} elseif ( 10  == strlen($phone) ) {
			return ( '(' . substr ( $phone, 0, 3 ) . ') ' . substr($phone, 3, 3) . '-' . substr($phone,6,4) );	
		} else {
			return ($phone);		
		}

	}

	private static $phone_type_options = array(	
		array(
			'value'	=> '0',
			'label'	=>	'Home' ),
		array(
			'value'	=> '1',
			'label'	=>	'Mobile' ),
		array(
			'value'	=> '2',
			'label'	=>	'Work' ),
		array(
			'value'	=> '3',
			'label'	=>	'Fax' ),					
		array(
			'value'	=> '4',
			'label'	=>	'Other' ),
		);

	public static function get_phone_type_options() {
		return self::$phone_type_options; 
	}

	public static function get_phone_type_label( $lookup ) {
		foreach ( self::$phone_type_options as $select_item_array ) {
			if ( $lookup == $select_item_array['value'] ) {
				return ( $select_item_array['label'] );			
			} 
		}
	}
}