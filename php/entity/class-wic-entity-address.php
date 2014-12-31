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

	public static function get_address_type_label( $lookup ) {
		foreach ( self::$address_type_options as $select_item_array ) {
			if ( $lookup == $select_item_array['value'] ) {
				return ( $select_item_array['label'] );			
			} 
		}
	}

	private static $state_options = array (
		array(
			'value'	=> 'MA',
			'label'	=>	'MA'),
		);		

	public static function get_state_options() {
		return self::$state_options; 
	}

	private function get_zip_from_usps () {
		
		$uspsRequest = new WIC_Entity_Address_USPS(); //class instantiation
		$uspsRequest->address2 = $this->data_object_array['address_line']->get_value();   
		$uspsRequest->address1 = '';
		$uspsRequest->city = $this->data_object_array['city']->get_value();
		$uspsRequest->state = $this->data_object_array['state']->get_value();
		$uspsRequest->zip = '';
 
		if ( $uspsRequest->address2 > '' && $uspsRequest->city > '' && $uspsRequest->state > '' ) {	
		
			$result = $uspsRequest->submit_request();
 		
			if ( !empty( $result ) ) {
				$xml = new SimpleXMLElement( $result );
				if( ! isset($xml->Address[0]->Error)) {
					$this->data_object_array['address_line']->set_value( (string) $xml->Address[0]->Address2 );
					$this->data_object_array['city']->set_value( (string) $xml->Address[0]->City );	
					$this->data_object_array['zip']->set_value( (string) $xml->Address[0]->Zip5 ); 		 		 
				}
			} else {
				echo '<h4>' . __( 'Check settings -- non-fatal error: Empty return from USPS ZipCode Validator.', 'wp-issues-crm' )  . '</h4>';
			}
		}
	}

	public function validate_values () { 
		$this->get_zip_from_usps();
		return ( parent::validate_values() );
	} 

}