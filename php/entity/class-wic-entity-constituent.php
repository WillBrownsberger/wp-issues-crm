<?php
/*
*
*	wic-constituent.php
*
*  This class is instantiated and takes control from the parent class in the parent constructor
*  It takes action on user requests which are the named functions.
*  It receives the $args passed from the button ( via the dashboard )  
*	It is mostly able to use generic functions from the parent.
*
*/

class WIC_Entity_Constituent extends WIC_Entity_Parent {
	
	/*
	*
	* Request handlers
	*
	*/

	protected function set_entity_parms( $args ) { // 
		// accepts args to comply with abstract function definition, but as a parent does not process them -- no instance
		$this->entity = 'constituent';
	} 

	// handle a request for a new search form
	protected function new_form() { 
		$this->new_form_generic( 'WIC_Form_Constituent_Search',  __( 'If constituent not found, you will be able to save.', 'wp-issues-crm') );
		return;
	}

	// show a constituent save form using values from a completed search form (search again)
	protected function save_from_search() { 
		parent::save_from_search ( 'WIC_Form_Constituent_Save',  $message = '', $message_level = 'good_news', $sql = '' );	
	}

	// handle a request for a blank new constituent form
	protected function new_constituent() {
		$this->new_form_generic ( 'WIC_Form_Constituent_Save' );	
	}

	// handle a search request coming from a completed search ( or search again ) form
	protected function form_search () { // show new search if not found, otherwise show update (or list)
		$this->form_search_generic ( 'WIC_Form_Constituent_Search_Again', 'WIC_Form_Constituent_Update');
		return;				
	}
	
	// handle a search request for an ID coming from anywhere
	protected function id_search ( $args ) {
		$id = $args['id_requested']; 
		$this->id_search_generic ( $id, 'WIC_Form_Constituent_Update' );
		return;		
	}

	//handle an update request coming from an update form
	protected function form_update () {
		$this->form_save_update_generic ( false, 'WIC_Form_Constituent_Update', 'WIC_Form_Constituent_Update' );
		return;
	}
	
	//handle a save request coming from a save form
	protected function form_save () {
		$this->form_save_update_generic ( true, 'WIC_Form_Constituent_Save', 'WIC_Form_Constituent_Update' );
		return;
	}

	//handle a search request coming search log
	protected function redo_search_from_query ( $meta_query_array ) {
		$this->redo_search_from_meta_query ( $meta_query_array, 'WIC_Form_Constituent_Save', 'WIC_Form_Constituent_Update' );
		return;
	}		
	
	// set values from update process to be visible on form after save or update
	protected function special_entity_value_hook ( &$wic_access_object ) {
		$this->data_object_array['last_updated_time']->set_value( $wic_access_object->last_updated_time );
		$this->data_object_array['last_updated_by']->set_value( $wic_access_object->last_updated_by );		
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
			'value'	=> '50',
			'label'	=>	'Up to 50' ),
		array(
			'value'	=> '100',
			'label'	=>	'Up to 100' ),
		array(
			'value'	=> '500',
			'label'	=>	'Up to 500' ),
		);

	private static $match_level_options = array ( 
		array(
			'value'	=> '1',
			'label'	=>	'Right wild card' ),
		array(
			'value'	=> '2',
			'label'	=>	'Soundex' ),
		array(
			'value'	=> '0',
			'label'	=>	'Strict' ),
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
		return ( WIC_Function_Utilities::get_administrator_array() );
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
		return WIC_Function_Utilities::value_label_lookup ( $lookup,  self::$party_options );
	}
	// note: since phone is multivalue, and formatter is not invoked in the 
	// WIC_Control_Multivalue class (rather at the child entity level), 
	// this function is only invoked in the list context
	public static function phone_formatter ( $phone_list ) {
		$phone_array = explode ( ',', $phone_list );
		$formatted_phone_array = array();
		foreach ( $phone_array as $phone ) {
			$formatted_phone_array[] = WIC_Entity_Phone::phone_number_formatter ( $phone );		
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

	public static function get_last_updated_by_options() {
		return ( WIC_Function_Utilities::get_administrator_array() );
	}

	public static function get_last_updated_by_label( $user_id ) {
		if ( '' < $user_id && 0 < $user_id ) {
			$user = get_user_by ( 'id', $user_id );
			return ( $user->display_name );
		}
		else return ( '' );
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
		return WIC_Function_Utilities::value_label_lookup ( $lookup,  self::$voter_status_options );
	}
	
}