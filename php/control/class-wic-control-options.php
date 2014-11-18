<?php
/*
*
* class-wic-control-options.php
*
* every select field will have a method in this class to retrieve options for it
*  	the name of that function will be get_{field_slug}_options()
*
* the alternatives to this approach that were considered are:
*		put static options in the WP option array -- this may make sense later, but probably still will retain the class methods here
*			other options are from diverse functions and I will have to create those functions and put them someplace anyway
*		put these in the select control -- bad to be touching that often
*		create my own options table structure -- bad more database calls for slim returns
*		
*
*/
Class WIC_Control_Options {


	private static $activity_type_options = array ( 
		array(
			'value'	=> '0',
			'label'	=>	'eMail' ),
		array(
			'value'	=> '1',
			'label'	=>	'Call' ),
		array(
			'value'	=> '2',
			'label'	=>	'Petition' ),
		array(
			'value'	=> '3',
			'label'	=>	'Meeting' ),
		array(
			'value'	=> '4',
			'label'	=>	'Letter' ),
		);
 
	private static $address_type_options	= array(	
		array(
			'value'	=> '0',
			'label'	=>	'Home Address' ),
		array(
			'value'	=> '1',
			'label'	=>	'Work Address' ),
		array(
			'value'	=> '2',
			'label'	=>	'Mail Address' ),
		array(
			'value'	=> '3',
			'label'	=>	'Other Address' ),
		);
		
  	private static $case_status_options = array ( 
		array(
			'value'	=> '0',
			'label'	=>	'Closed' ),
		array(
			'value'	=> '1',
			'label'	=>	'Open' ),
		);
		
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
			
	private static $phone_type_options = array(	
		array(
			'value'	=> '0',
			'label'	=>	'Home Landline' ),
		array(
			'value'	=> '1',
			'label'	=>	'Personal Mobile' ),
		array(
			'value'	=> '2',
			'label'	=>	'Work Landline' ),
		array(
			'value'	=> '3',
			'label'	=>	'Work Mobile' ),
		array(
			'value'	=> '4',
			'label'	=>	'Home Fax' ),					
		array(
			'value'	=> '5',
			'label'	=>	'Work Fax' ),
		array(
			'value'	=> '6',
			'label'	=>	'Other Phone' ),
		);

	private static $pro_con_options = array ( 
		array(
			'value'	=> '0',
			'label'	=>	'Pro' ),
		array(
			'value'	=> '1',
			'label'	=>	'Con' ),
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

	private static $state_options = array (
		array(
			'value'	=> 'MA',
			'label'	=>	'MA'),
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
	public static function get_activity_type_options() {
		return self::$activity_type_options; 
	}
	 
	public static function get_address_type_options() {
		return self::$address_type_options; 
	}
		
  	public static function get_case_status_options() {
		return self::$case_status_options; 
	} 
		
	public static function get_email_type_options () {
		return self::$email_type_options; 
	}
	
	public static function get_gender_options() {
		return self::$gender_options; 
	}
	
	public static function get_issue_options() {
	/*	
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
	*/
		return self::$gender_options; 
	}
	
	public static function get_party_options() {
		return self::$party_options; 
	}
	
	public static function get_party_label( $lookup ) {
		foreach ( self::$party_options as $select_item_array ) {
			if ( $lookup == $select_item_array['value'] ) {
				return ( $select_item_array['label'] );			
			} 
		}
	}
			
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

	public static function get_pro_con_options() {
		return self::$pro_con_options; 
	}

	public static function get_retrieve_limit_options() {
		return self::$retrieve_limit_options; 
	}

	public static function get_state_options() {
		return self::$activity_type_options; 
	}

	public static function get_voter_status_options() {
		return self::$voter_status_options; 
	}
	
	public static function get_voter_status_label( $lookup ) {
		foreach ( self::$voter_status_options as $select_item_array ) {
			if ( $lookup == $select_item_array['value'] ) {
				return ( $select_item_array['label'] );			
			} 
		}
	}

 }

