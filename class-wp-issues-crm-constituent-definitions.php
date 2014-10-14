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
		add_action( 'init', array( $this, 'custom_post_type' ), 0 );
		$this->constituent_field_groups = $this->multi_array_key_sort ( $this->constituent_field_groups, 'order' );
		$this->constituent_fields		  = $this->multi_array_key_sort ( $this->constituent_fields, 'order' );
	}

	/*
	*	sort array of arrays by one value of the arrays
	*
	*/		
	public function multi_array_key_sort ( $multi_array, $key )	{
		$temp = array();
		
		foreach ( $multi_array as $line_item ) {
			 $temp[$line_item[$key]] = $line_item;
		}
		ksort ($temp);
		$sorted_line_items = array();
		foreach ($temp as $key => $value ) {
			array_push( $sorted_line_items, $value );			
		}
		return ( $sorted_line_items) ;
	}


	public $wic_metakey = 'wic_data_';	
	
	/* this array determines:
		- whether field will be handled as array for display purposes -- multi lines of same field
		- whether field will always be searched on a like compare (instead of = ), regardless of field or screen settings
		- whether will look second field at first member of array when doing dedup and required field checking (i.e., first phone, email or street address)
	*/
	public $serialized_field_types = array ( 
		'phones',
		'emails',
		'addresses',
	);
	
	public $constituent_field_groups = array (
	/*	array (
			'name'		=> 'required',
			'label'		=>	'Identity',
			'legend'		=>	'',
			'order'		=>	10,
		), */
		array (
			'name'		=> 'contact',
			'label'		=>	'Contact',
			'legend'		=>	'',
			'order'		=>	20,
			'initial-open'	=> true,
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
			'name'		=> 'links',
			'label'		=>	'Identity Codes',
			'legend'		=>	'These codes can be searched, but cannot be updated online.',
			'order'		=> 40,
			'initial-open'	=> false,

		),	
	);

	public $constituent_fields = array( 
	  	/* fields control -- all definitions of fields are in this array (except for native post fields -- content and title)
	  	*  -- slug is the no-spaces name of the field
	  	* 	-- is the front facing name
	  	*	--	online is whether field should appear at all in online access (may or may not be updateable -- that is determined by type)
	  	*	-- type determines what control is displayed for the field ( may be readonly ) and also validation
	  	*	-- like indicates whether full text searching is enabled for the field
	  	*	-- dedup indicates whether field should be on list of fields tested for deduping
	  	*	-- group is just for form layout purposes
	  	*	-- order is just for form layout purposes
	  	*  -- required may be false, group or individual.  If group, at least one in group must be provided.
	  	*  -- list include in standard constituent lists -- if > 0 value is % width for display -- should sum to 100.
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
	 		'dedup'	=>	false,
	 		'group'	=>	'personal',
	 		'label'	=>	'Middle Name',
	 		'like'	=>	false,
			'list'	=> '0',
	 		'online'	=>	false,
	 		'order'	=>	15,	
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
			'slug'	=> 'email_group',
			'type'	=>	'emails',
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
			'slug'	=> 'phone_numbers',
			'type'	=>	'phones',
			),
	array( // 7A
			'dedup'	=>	true,
			'group'	=>	'contact',
			'label'	=>	'Address',
			'like'	=>	true,
			'list'	=> '29',
			'online'	=>	true,
			'order'	=>	30,
			'required'	=> '',
			'slug'	=> 'street_addresses',
			'type'	=>	'addresses',
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
			'slug'	=> 'assigned',
			'user_role' => 'Administrator', 
			'type'	=>	'user',
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
			'label'	=>	'Job Title',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	80,
			'required'	=> '',
			'slug'	=> 'job_title',
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
			'slug'	=> 'gender_id',
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
			'slug'	=> 'birth_date',
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
		array( // 14
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Date Deceased',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	95,	
			'required'	=> false,
			'slug'	=> 'deceased_date',
			'type'	=>	'date',
			),
		array( // 16
			'dedup'	=>	false,
			'group'	=>	'links',
			'label'	=>	'CiviCRM ID',
			'like'	=>	false,
			'online'	=>	true,
			'order'	=>	100,
			'required'	=> '',
			'slug'	=> 'civicrm_id',
			'type'	=>	'readonly',
			'list'	=> '0',
			),
		array( // 17
			'dedup'	=>	false,
			'group'	=>	'links',
			'label'	=>	'Secretary of State ID',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	110,
			'required'	=> '',
			'slug'	=> 'ss_id',
			'type'	=>	'readonly',
			),
		array( // 18
			'dedup'	=>	false,
			'group'	=>	'links',
			'label'	=>	'VAN ID',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	120,
			'required'	=> false,
			'slug'	=> 'VAN_id',
			'type'	=>	'readonly',
			),
	);
		
	// Register Custom Post Type
	function custom_post_type() {
	
		$labels = array(
			'name'                => _x( 'Constituents', 'Post Type General Name', 'wp-issues-crm' ),
			'singular_name'       => _x( 'Constituent', 'Post Type Singular Name', 'wp-issues-crm' ),
			'menu_name'           => __( 'Constituents', 'wp-issues-crm' ),
			'parent_item_colon'   => __( 'Parent Constituent', 'wp-issues-crm' ),
			'all_items'           => __( 'All Constituents', 'wp-issues-crm' ),
			'view_item'           => __( 'View Constituent', 'wp-issues-crm' ),
			'add_new_item'        => __( 'Add New Constituent', 'wp-issues-crm' ),
			'add_new'             => __( 'Add New', 'wp-issues-crm' ),
			'edit_item'           => __( 'Edit Constituent', 'wp-issues-crm' ),
			'update_item'         => __( 'Update Constituent', 'wp-issues-crm' ),
			'search_items'        => __( 'Search Constituent', 'wp-issues-crm' ),
			'not_found'           => __( 'Not found', 'wp-issues-crm' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'wp-issues-crm' ),
		);
		$capabilities = array(
			'edit_post'           => 'activate_plugins',
			'read_post'           => 'activate_plugins',
			'delete_post'         => 'activate_plugins',
			'edit_posts'          => 'activate_plugins',
			'edit_others_posts'   => 'activate_plugins',
			'publish_posts'       => 'activate_plugins',
			'read_private_posts'  => 'activate_plugins',
		);
		$args = array(
			'label'               => __( 'constituent', 'wp-issues-crm' ),
			'description'         => __( 'constituents -- people', 'wp-issues-crm' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'author', 'editor', 'thumbnail', 'revisions', 'custom-fields', ),
			// comments are not private; labels inappropriate
			// general editor doesn't make sense
			// 'taxonomies'          => array( 'category' ),
			'hierarchical'        => false,
			'public'              => false, // controls if view link appears in edit menu (but not whether URL is visible on front end)
			'show_ui'             => true, // exclusively through our front end
			'show_in_menu'        => true, // exclusively through our front end
			'show_in_nav_menus'   => false, // not something that one would navigate to
			'show_in_admin_bar'   => false, // assure that all navigation to constituents goes through the plugin
			'menu_position'       => 100,    // irrelevant
			'can_export'          => false, // control export through own security
			'has_archive'         => false, // no support in general themes
			'exclude_from_search' => true,  // don't want in queries
			'publicly_queryable'  => false, // controls if URL to constituent is accessible on front end (independent of log in status) -- false for privacy
			'capabilities'        => $capabilities,
		);
		register_post_type( 'wic_constituent', $args );
	
	}

/*
*
* Option lists
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
		
	public $address_zip_options = array (
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
	);		


 }

$wic_constituent_definitions = new WP_Issues_CRM_Constituent_Definitions;