<?php
/*
*
*	wic-constituent.php
*
*  This class is instantiated and takes control from the parent class in the parent constructor
*  It takes action on user requests which are the named functions.
*  It receives the $args passed from the button ( via WP_Issues_CRM and the parent )  
*		BUT only $arg actually used is in the ID requested function.
*	It is able to use generic functions from the parent.
*
*/

class WIC_Entity_Constituent extends WIC_Entity_Parent {

	protected function set_entity_parms( $args ) { // 
		// accepts args to comply with abstract function definition, but as a parent does not process them -- no instance
		$this->entity = 'constituent';
	} 

	// handle a request for a new standard form
	protected function new_form() { 
		$this->new_form_generic( 'WIC_Form_Constituent_Search' );
		return;
	}

	// handle a search request coming from a standard form
	protected function form_search () { 
		$this->form_search_generic ( 'WIC_Form_Constituent_Save', 'WIC_Form_Constituent_Update');
		return;				
	}
	
	// handle a search request for an ID coming from anywhere
	protected function id_search ( $args ) {
		$id = $args['id_requested']; 
		$this->id_search_generic ( $id, 'WIC_Form_Constituent_Update' );
		return;		
	}

	//handle an update request coming from a standard form
	protected function form_update () {
		$this->form_save_update_generic ( false, 'WIC_Form_Constituent_Update', 'WIC_Form_Constituent_Update' );
		return;
	}
	
	//handle a save request coming from a standard form
	protected function form_save () {
		$this->form_save_update_generic ( true, 'WIC_Form_Constituent_Save', 'WIC_Form_Constituent_Update' );
		return;
	}
	
	/***************************************************************************
	*
	* Constituent properties, setters and getters
	*
	****************************************************************************/ 	

  	private static $case_status_options = array ( 
		array(
			'value'	=> '0',
			'label'	=>	'Closed' ),
		array(
			'value'	=> '1',
			'label'	=>	'Open' ),
		);
		

	private static $gender_options = array ( 
		array(
			'value'	=> 'm',
			'label'	=>	'Male' ),
		array(
			'value'	=> 'f',
			'label'	=>	'Female' ),
		);

	private static $party_options = array ( 
		array(
			'value'	=> 'd',
			'label'	=>	'Democrat' ),
		array(
			'value'	=> 'r',
			'label'	=>	'Republican' ),
		array(
			'value'	=> 'u',
			'label'	=>	'Unenrolled' ),
		array(
			'value'	=> 'l',
			'label'	=>	'Libertarian' ),
		array(
			'value'	=> 'j',
			'label'	=>	'Green-Rainbow' ),
		array(
			'value'	=> 'g',
			'label'	=>	'Green Party USA' ),	
		array(
			'value'	=> 's',
			'label'	=>	'Socialist' ),	
		array(
			'value'	=> 'o',
					'label'	=>	'Other' ),						
		);	


	private static $retrieve_limit_options = array ( 
		array(
			'value'	=> '10',
			'label'	=>	'Up to 10 Records' ),
		array(
			'value'	=> '50',
			'label'	=>	'Up to 50 Records' ),
		array(
			'value'	=> '100',
			'label'	=>	'Up to 100 Records' ),
		array(
			'value'	=> '500',
			'label'	=>	'Up to 500 Records' ),
		array(
			'value'	=> '1000',
			'label'	=>	'Up to 1000 Records' ),
		);

	private static $match_level_options = array ( 
		array(
			'value'	=> '2',
			'label'	=>	'Soundex matching permitted for names.' ),
		array(
			'value'	=> '1',
			'label'	=>	'Right wild card for names.' ),
		array(
			'value'	=> '0',
			'label'	=>	'Strict match for all fields. ' ),
		);

	private static $voter_status_options = array ( 
		array(
			'value'	=> 'a',
			'label'	=>	'Active' ),
		array(
			'value'	=> 'i',
			'label'	=>	'Inactive' ),
		array(
			'value'	=> 'x',
			'label'	=>	'Not Registered' ),
		);	
	
	/*
	*	option array get functions
	*/

	public static function get_case_assigned_options() {
		return ( wic_get_administrator_array() );
	}

		
  	public static function get_case_status_options() {
		return self::$case_status_options; 
	} 

	public static function get_gender_options() {
		return self::$gender_options; 
	}

	public static function get_match_level_options() {
		return self::$match_level_options; 
	}
	
	public static function get_party_options() {
		return self::$party_options; 
	}
	
	public static function get_party_label( $lookup ) {
		return wic_value_label_lookup ( $lookup,  self::$party_options );
	}
	// note: since phone is multivalue, and formatter is not invoked in the 
	// WIC_Control_Multivalue class (rather at the child entity level), 
	// this function is only invoked in the list context
	public static function phone_formatter ( $phone_list ) {
		$phone_array = explode ( ',', $phone_list );
		$formatted_phone_array = array();
		foreach ( $phone_array as $phone ) {
			$formatted_phone_array[] = WIC_Entity_Phone::phone_formatter ( $phone );		
		}
		return ( implode ( '<br />', $formatted_phone_array ) );
	}
	
	public static function email_formatter ( $email_list ) {
		$email_array = explode ( ',', $email_list );
		$clean_email_array = array();
		foreach ( $email_array as $email ) {
			$clean_email_array[] = esc_html ( $email );		
		}
		return ( implode ( '<br />', $clean_email_array ) );
	}		

	public static function address_formatter ( $address_list ) {
		return self::email_formatter ( $address_list );	
	}	

	public static function mark_deleted_validator ( $value ) {
		if ( $value > '' && trim( strtolower( $value ) ) != 'deleted' ) {
			return __( 'To hide this record from future searches, type the full word "DELETED" into Mark Deleted and then Update.', 'wp-issues-crm');		
		}
	}	

	public static function get_retrieve_limit_options() {
		return self::$retrieve_limit_options; 
	}


	public static function get_voter_status_options() {
		return self::$voter_status_options; 
	}
	
	public static function get_voter_status_label( $lookup ) {
		return wic_value_label_lookup ( $lookup,  self::$voter_status_options );
	}

	
	
	
}