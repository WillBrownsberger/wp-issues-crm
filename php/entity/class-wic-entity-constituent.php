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

	//handle a search request coming search log
	protected function redo_search_from_query ( $meta_query_array ) {
		$this->redo_search_from_meta_query ( $meta_query_array, 'WIC_Form_Constituent_Save', 'WIC_Form_Constituent_Update' );
		return;
	}		
	
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
			'value'	=> '10',
			'label'	=>	'Up to 10' ),
		array(
			'value'	=> '50',
			'label'	=>	'Up to 50' ),
		array(
			'value'	=> '100',
			'label'	=>	'Up to 100' ),
		);

	private static $match_level_options = array ( 
		array(
			'value'	=> '2',
			'label'	=>	'Soundex' ),
		array(
			'value'	=> '1',
			'label'	=>	'Right wild card' ),
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

	/*	public function drop_down_issues() {
		
		global $wic_database_utilities;		
				
		$wic_issues_query = $wic_database_utilities->get_open_issues();

		$issues_array = array();
		
		if ( $wic_issues_query->have_posts() ) {		
			while ( $wic_issues_query->have_posts() ) {
				$wic_issues_query->the_post();
				$issues_array[] = array(
					'value'	=> $wic_issues_query->post->ID,
					'label'	=>	$wic_issues_query->post->post_title,
				);
			}
		}
		
		wp_reset_postdata();
		return $issues_array;

	}
	
		public function get_open_issues() {
			
		$meta_query_args = array(
			'relation' => 'AND',
			array(
				'meta_key'     => 'wic_live_issues',
				'value'   => 'open',
				'compare' => '=',
			)
		);
		
		$list_query_args = array (
			'ignore_sticky_posts'	=> true,
			'post_type'		=>	'post',
 			'posts_per_page' => 100,
 			'meta_query' 	=> $meta_query_args, 
 			'order'			=> 'DESC',
	 		);	
	 		
	 	$open_posts = new WP_Query ( $list_query_args );
	 	
	 	return ( $open_posts );	
		
	}

*/
	
	
}