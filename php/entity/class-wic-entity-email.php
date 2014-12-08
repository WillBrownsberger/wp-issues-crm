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

	public function update_row() {
		$upload_location = wp_upload_dir(); 
		if ( file_exists ( $upload_location['basedir'] . '/email-icon.png' ) ) {
			$link_location =  '<img id = "wic-email-icon" src="' . $upload_location['baseurl'] . '/email-icon.png">';  		
		} else {
			$link_location = 'send';		
		}

		$message = '<a id = "wic-email-icon-link" title = "' . __( 'Send email to email address.', 'wp-issues-crm' ) . 
			 '" href="mailto:'. $this->get_email_address() .'"> ' .
				$link_location .			
				 '</a>';
		$new_update_row_object = new WIC_Form_Email_Update ( $this->entity, $this->entity_instance );
		$new_update_row = $new_update_row_object->layout_form( $this->data_object_array, $message, null );
		return $new_update_row;
	}

	private static $email_type_options = array(	
		array(
			'value'	=> '0',
			'label'	=>	'Personal' ),
		array(
			'value'	=> '1',
			'label'	=>	'Work' ),
		array(
			'value'	=> '2',
			'label'	=>	'Other' ),
		);

		
	public static function get_email_type_options () {
		return self::$email_type_options; 
	}

	public static function get_email_type_label( $lookup ) {
		foreach ( self::$email_type_options as $select_item_array ) {
			if ( $lookup == $select_item_array['value'] ) {
				return ( $select_item_array['label'] );			
			} 
		}
	}

	public static function email_address_validator ( $email ) { 
		$error = '';
		if ( $email > '' ) {	
			$error = filter_var( $email, FILTER_VALIDATE_EMAIL ) ? '' : __( 'Email address appears to be not valid. ', 'wp-issues-crm' );
		}
		return $error;	
	}	

	public function get_email_address() {
		return ( $this->data_object_array['email_address']->get_value() );	
	}
}