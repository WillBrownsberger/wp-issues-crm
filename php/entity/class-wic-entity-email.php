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

	private static $email_type_options = array(	
		array(
			'value'	=> '0',
			'label'	=>	'Personal Email' ),
		array(
			'value'	=> '1',
			'label'	=>	'Work Email' ),
		array(
			'value'	=> '2',
			'label'	=>	'Shared Email' ),
		array(
			'value'	=> '3',
			'label'	=>	'Other Email' ),
		);

		
	public static function get_email_type_options () {
		return self::$email_type_options; 
	}

	public static function email_address_validator ( $email ) { 
		$error = '';
		if ( $email > '' ) {	
			$error = filter_var( $email, FILTER_VALIDATE_EMAIL ) ? '' : __( 'Email address appears to be not valid. ', 'wp-issues-crm' );
		}
		return $error;	
	}	

}