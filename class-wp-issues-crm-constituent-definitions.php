<?php
/*
*
* class-wp-issues-crm-constituent-definitions.php
*
* post-type and field definitions for wp_issues_crm constituents
*
*
* contents: 
*   group and field definitions  
*   custom post type
*   option arrays for fields
* 
*/

class WP_Issues_CRM_Constituent_Definitions {
	
	public function __construct() {
		$this->wic_post_field_groups = multi_array_key_sort ( $this->wic_post_field_groups, 'order' );
		$this->wic_post_fields		  = multi_array_key_sort ( $this->wic_post_fields, 'order' );
	}


	public $wic_post_type_labels = array (
		'singular' => 'Constituent',
		'plural'	  => 'Constituents'	
	);

	public $wic_post_type_sort_order = array (
		'orderby' => 'title',
		'order'	  => 'ASC'	
	);	

	public $wic_post_type_dups_ok = false;

	public $wic_post_field_groups = array (
		array (
			'name'		=> 'contact',
			'label'		=>	'Contact',
			'legend'		=>	'',
			'order'		=>	10,
			'initial-open'	=> true,
		),
		array (
			'name'		=> 'address',
			'label'		=>	'Address',
			'legend'		=>	'',
			'order'		=>	20,
			'initial-open'	=> false,
		),
		array (
			'name'		=> 'case_management',
			'label'		=>	'Case Management',
			'legend'		=>	'',
			'order'		=>	25,
			'initial-open'	=> false,
		),
		array (
			'name'		=> 'personal',
			'label'		=>	'Personal Information',
			'legend'		=>	'',
			'order'		=> 30,
			'initial-open'	=> false,
		),
		
		array (
			'name'		=> 'registration',
			'label'		=>	'Voter Information',
			'legend'		=>	'Voter data can be searched, but cannot be updated online.',
			'order'		=> 40,
			'initial-open'	=> false,
		),
		array (
			'name'		=> 'links',
			'label'		=>	'Legacy Codes',
			'legend'		=>	'These codes can be searched, but cannot be updated online.',
			'order'		=> 50,
			'initial-open'	=> false,

		),	
		array (
			'name'		=> 'audit',
			'label'		=>	'Update Logging',
			'legend'		=>	'This information can be searched, but cannot be updated online.',
			'order'		=> 50,
			'initial-open'	=> false,

		),	
	);

	public $wic_post_fields = array( 
	  	/* fields control -- all definitions of fields are in this array (except for native post fields -- content and title)
		*	-- dedup indicates whether field should be on list of fields tested for deduping	  	
	  	*	-- group is just for form layout purposes
	  	* 	-- label is the front facing name
	  	*	-- like indicates whether full text searching is enabled for the field	  	
	  	*  -- list include in standard constituent lists -- if > 0 value is % width for display -- should sum to 100 to keep on one line.
	  	*	--	online is whether field should appear at all in online access (may or may not be updateable -- that is determined by type)
	  	*	-- order is just for form layout purposes
	  	*  -- required may be false, group or individual.  If group, at least one in group must be provided.
		*  -- slug is the no-spaces name of the field
	  	*	-- type determines what control is displayed for the field ( may be readonly ) and also validation
	  	*  **** the only field slug specific logic in the whole class is expectation that one of first_name, last_name and email not be blank for title def
	  	*  **** so, fields with group => required must be all or a subset of first_name, last_name, email, or email_group
	  	*
	  	*	NOTE: Order values must be unique or second list will overlay first
	  	*/
		array( // 1
			'dedup'	=>	true,	
			'group'	=>	'contact',
			'label'	=>	'First Name',
			'like'	=>	true,	
			'list'	=> '14',
			'online'	=>	true,	
			'order'	=>	10,
			'required'	=> 'group', // see note above
			'slug'	=> 'first_name', 	
			'type'	=>	'text', 	
			),		
		array( // 2
	 		'dedup'	=>	true,
	 		'group'	=>	'contact',
	 		'label'	=>	'Middle Name',
	 		'like'	=>	true,
			'list'	=> '0',
	 		'online'	=>	true,
	 		'order'	=>	16,	
			'required'	=> false,
			'slug'	=> 'middle_name',
	 		'type'	=>	'text',
		),		
		array( // 3
			'dedup'	=>	true,
			'group'	=>	'contact',
			'label'	=>	'Last Name',
			'like'	=>	true,
			'list'	=> '14',
			'online'	=>	true,
			'order'	=>	20,
			'required'	=> 'group', // see note above
			'slug'	=> 'last_name',
			'type'	=>	'text',
			),	
		array( // 4
			'dedup'	=>	true,
			'group'	=>	'contact',
			'label'	=>	'eMail',
			'like'	=>	true,
			'list'	=> '28',
			'online'	=>	true,
			'order'	=>	40,
			'required'	=> 'group', // see note above -- do not have to include in group required, if want to force to have a fn or ln
			'slug'	=> 'emails',
			'type'	=>	'multivalue',
			),				
		array(  // 6
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'Phone',
			'like'	=>	true,
			'list'	=> '15',
			'online'	=>	true,
			'order'	=>	35,
			'required'	=> '',
			'slug'	=> 'phones',
			'type'	=>	'multivalue',
			),
/*	array( // 7A
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'Address',
			'like'	=>	true,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	360,
			'required'	=> '',
			'slug'	=> 'street_addresses',
			'type'	=>	'addresses',
			), */	
	array( // 7A
			'dedup'	=>	false,
			'group'	=>	'address',
			'label'	=>	'Street Number',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	300,
			'required'	=> '',
			'slug'	=> 'street_number',
			'type'	=>	'text',
			),	
		array( // 7A
			'dedup'	=>	false,
			'group'	=>	'address',
			'label'	=>	'Street Number Suffix',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	310,
			'required'	=> '',
			'slug'	=> 'street_suffix',
			'type'	=>	'text',
			),				
		array( // 7A
			'dedup'	=>	false,
			'group'	=>	'address',
			'label'	=>	'Street Name',
			'like'	=>	true,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	320,
			'required'	=> '',
			'slug'	=> 'street_name',
			'type'	=>	'text',
			),	
		array( // 7A
			'dedup'	=>	false,
			'group'	=>	'address',
			'label'	=>	'Apartment',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	325,
			'required'	=> '',
			'slug'	=> 'apartment',
			'type'	=>	'text',
			),	
		array( // 7A
			'dedup'	=>	false,
			'group'	=>	'address',
			'label'	=>	'City',
			'like'	=>	false,
			'list'	=> '20',
			'online'	=>	true,
			'order'	=>	330,
			'required'	=> '',
			'slug'	=> 'city',
			'type'	=>	'text',
			),
		array( // 7A
			'dedup'	=>	false,
			'group'	=>	'address',
			'label'	=>	'State',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	340,
			'required'	=> '',
			'select_array' =>array (
				array(
					'value'	=> 'MA',
					'label'	=>	'MA'),
				),
			'slug'	=> 'state',
			'type'	=>	'select',
			),					
		array( // 7A
			'dedup'	=>	false,
			'group'	=>	'address',
			'label'	=>	'Zip',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	345,
			'required'	=> '',
			'select_array' => array (
				array(
					'value'	=> '02472',
					'label'	=>	'WATERTOWN (02472)'),
			
				array(
					'value'	=> '02478',
					'label'	=> 'BELMONT (02478)'),
				array(
					'value'	=> '02135',
					'label'	=> 'BRIGHTON (02135)'),
				array(
					'value'	=> '02116',
					'label'	=> 'BACKBAY (02116)'),
				array(
					'value'	=> '02115',
					'label'	=> 'BOSTON (02115)'),
				array(
					'value'	=> '02215',
					'label'	=> 'FENWAY (02215)'),
				array(
					'value'	=> '02134',
					'label'	=> 'ALLSTON (02134)'),
				array(
					'value'	=> '02140',
					'label'	=> 'NORTH CAMBRIDGE (02140)'),
				array(
					'value'	=> '02199',
					'label'	=> 'BOSTON (02199)'),
				array(
					'value'	=> '02474',
					'label'	=> 'ARLINGTON (02474)'),
				array(
					'value'	=> '02138',
					'label'	=> 'CAMBRIDGE (02138)'),
				array(
					'value'	=> '02120',
					'label'	=> 'BOSTON (02120)'),
				array(
					'value'	=> '02467',
					'label'	=> 'CHESTNUT HILL (02467)'),
				array(
					'value'	=> '02118',
					'label'	=> 'BOSTON (02118)'),
				array(
					'value'	=> '02127',
					'label'	=> 'BOSTON (02127)'),
				array(
					'value'	=> '02114',
					'label'	=> 'BOSTON (02114)'),
				array(
					'value'	=> '02476',
					'label'	=> 'ARLINGTON (02476)'),
				array(
					'value'	=> '02108',
					'label'	=> 'BOSTON (02108)'),
				array(
					'value'	=> '02113',
					'label'	=> 'BOSTON (02113)'),
				array(
					'value'	=> '02128',
					'label'	=> 'BOSTON (02128)'),
				array(
					'value'	=> '02210',
					'label'	=> 'BOSTON (02210)'),
				array(
					'value'	=> '02109',
					'label'	=> 'BOSTON (02109)'),
				array(
					'value'	=> '02111',
					'label'	=> 'BOSTON (02111)'),
				array(
					'value'	=> '02471',
					'label'	=> 'WATERTOWN (02471)'),
				array(
					'value'	=> '02445',
					'label'	=> 'BROOKLINE (02445)'),
				array(
					'value'	=> '02139',
					'label'	=> 'CAMBRIDGE (02139)'),
				array(
					'value'	=> '02421',
					'label'	=> 'LEXINGTON (02421)'),
				array(
					'value'	=> '02446',
					'label'	=> 'BROOKLINE (02446)'),
				array(
					'value'	=> '02117',
					'label'	=> 'BOSTON (02117)'),
				array(
					'value'	=> '02124',
					'label'	=> 'BOSTON (02124)'),
				array(
					'value'	=> '02123',
					'label'	=> 'BOSTON (02123)'),
				array(
					'value'	=> '02110',
					'label'	=> 'BOSTON (02110)'),
				),	
			'slug'	=> 'zip',
			'type'	=>	'select',
			),											
		array( // 10
			'dedup'	=>	false,
			'group'	=>	'case_management',
			'label'	=>	'Staff',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	50,
			'required'	=> '',
			'select_array' => 'wic_get_user_list',
			'select_parameter' => 'Administrator',
			'slug'	=> 'assigned',
			'type'	=>	'select',
			), 
		array( // 10A
			'dedup'	=>	false,
			'group'	=>	'case_management',
			'label'	=>	'Review Date',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	60,	
			'required'	=> '',
			'slug'	=> 'case_review_date',
			'type'	=>	'date',
			),
		array( // 10A
			'dedup'	=>	false,
			'group'	=>	'case_management',
			'label'	=>	'Case Status',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	55,	
			'required'	=> '',
			'slug'	=> 'case_status',
			'select_array'	=>	array ( 
				array(
					'value'	=> '0',
					'label'	=>	'Closed' ),
				array(
					'value'	=> '1',
					'label'	=>	'Open' ),
				),
			'type'	=> 'select',
			),
		array( // 11
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Occupation',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	80,
			'required'	=> '',
			'slug'	=> 'occupation',
			'type'	=>	'text',
			),
		array( // 12
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Organization',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	85,
			'required'	=> '',
			'slug'	=> 'organization_name',
			'type'	=>	'text',
			),
		array( // 13
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Gender',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	75,
			'required'	=> '',
			'slug'	=> 'gender',
			'select_array'	=>	array ( 
				array(
					'value'	=> 'm',
					'label'	=>	'Male' ),
				array(
					'value'	=> 'f',
					'label'	=>	'Female' ),
				),
			'type'	=> 'select',
			),

		array( // 14
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Date of Birth',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	70,	
			'required'	=> false,
			'slug'	=> 'dob',
			'type'	=>	'date',
			),
		array( // 15
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Is Deceased',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	90,
			'required'	=> '',
			'slug'	=> 'is_deceased',
			'type'	=>	'check',
			),
		array( // 15
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Mark Deleted',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	91,
			'required'	=> '',
			'slug'	=> 'is_deleted',
			'type'	=>	'check',
			),	
		array( // 16
			'dedup'	=>	false,
			'group'	=>	'links',
			'label'	=>	'CiviCRM ID',
			'like'	=>	false,
			'online'	=>	true,
			'order'	=>	100,
			'readonly_subtype' => 'text',
			'required'	=> '',
			'slug'	=> 'civicrm_id',
			'type'	=>	'readonly',
			'list'	=> '0',
			),
		array( // 18
			'dedup'	=>	false,
			'group'	=>	'links',
			'label'	=>	'VAN ID',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	120,
			'readonly_subtype' => 'text',			
			'required'	=> false,
			'slug'	=> 'VAN_id',
			'type'	=>	'readonly',
			),
			
		array( // 13
			'dedup'	=>	false,
			'group'	=>	'registration',
			'label'	=>	'Voter Status',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	200,
			'readonly_subtype' => 'select',
			'required'	=> '',
			'slug'	=> 'voter_status',
			'select_array'	=>	array ( 
				array(
					'value'	=> 'a',
					'label'	=>	'Active' ),
				array(
					'value'	=> 'i',
					'label'	=>	'Inactive' ),
				array(
					'value'	=> 'x',
					'label'	=>	'Not Registered' ),
				),				
			'type'	=> 'readonly',
			),	
			
		array( // 13
			'dedup'	=>	false,
			'group'	=>	'registration',
			'label'	=>	'Registration Date',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	205,
			'readonly_subtype' => 'text',
			'required'	=> '',
			'slug'	=> 'reg_date',
			'type'	=> 'readonly',
			),			
		array( // 13
			'dedup'	=>	false,
			'group'	=>	'registration',
			'label'	=>	'Party',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	210,
			'readonly_subtype' => 'select',
			'required'	=> '',
			'slug'	=> 'party',
			'select_array'	=>	array ( 
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
				),	
			'type'	=> 'readonly',
			),		
		array( // 18
			'dedup'	=>	false,
			'group'	=>	'registration',
			'label'	=>	'Ward',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	220,
			'readonly_subtype' => 'text',			
			'required'	=> false,
			'slug'	=> 'ward',
			'type'	=>	'readonly',
			),
			
		array( // 18
			'dedup'	=>	false,
			'group'	=>	'registration',
			'label'	=>	'Precinct',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	240,
			'readonly_subtype' => 'text',			
			'required'	=> false,
			'slug'	=> 'precinct',
			'type'	=>	'readonly',
			),	
			
		array( // 17
			'dedup'	=>	false,
			'group'	=>	'registration',
			'label'	=>	'Secretary of State ID',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	250,
			'readonly_subtype' => 'text',			
			'required'	=> '',
			'slug'	=> 'ssid',
			'type'	=>	'readonly',
			),	
		array(
			'dedup'	=>	false,
			'group'	=>	'audit',
			'label'	=>	'Last Updated By',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	400,
			'readonly_subtype' => 'select',	
			'required'	=> '',
			'select_array' => 'wic_get_user_list',
			'select_parameter' => 'Administrator',
			'slug'	=> 'last_updated_by',
			'type'	=>	'readonly',
			), 
		array( // 16
			'dedup'	=>	false,
			'group'	=>	'audit',
			'label'	=>	'Lasted Updated Time',
			'like'	=>	true,
			'online'	=>	true,
			'order'	=>	410,
			'readonly_subtype' => 'text',
			'required'	=> '',
			'slug'	=> 'last_updated_time',
			'type'	=>	'readonly',
			'list'	=> '0',
			),
	);
		

/*
*
* Option lists -- called externally in form utilities
*/

	public $phone_type_options = array(	
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

	public $email_type_options = array(	
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
		
	public $address_type_options	 = array(	
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
	
		



 }

$wic_constituent_definitions = new WP_Issues_CRM_Constituent_Definitions;