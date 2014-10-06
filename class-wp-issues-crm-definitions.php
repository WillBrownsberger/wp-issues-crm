<?php
/*
* post-type and taxonomy definitions for wp_issues_crm
* 
*/

class WP_Issues_CRM_Definitions {
	
	public $wic_metakey = 'wic_data_';	
	
	public $constituent_field_groups = array (
		array (
			'name'		=> 'required',
			'label'		=>	'Identity',
			'legend'		=>	'',
			'order'		=>	10,
		),
		array (
			'name'		=> 'contact',
			'label'		=>	'Contact Information',
			'legend'		=>	'',
			'order'		=>	20,
		),
		array (
			'name'		=> 'personal',
			'label'		=>	'Personal Information',
			'legend'		=>	'',
			'order'		=> 30,
		),
		array (
			'name'		=> 'links',
			'label'		=>	'Identity Codes',
			'legend'		=>	'These codes can be searched, but cannot be updated online.',
			'order'		=> 40,
		),	
	);

	public $constituent_fields = array( 
	  	/* fields control -- all definitions of fields are in this array (except for native post fields -- content and title)
	  	*  the only field slug specific logic in the whole class is requirement that one of first_name, last_name and email not be blank
	  	*  so, fields must include so labeled first_name, last_name, email, otherwise may be replaced freely
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
	  	*/
		array( // 1
			'dedup'	=>	true,	
			'group'	=>	'required',
			'label'	=>	'First Name',
			'like'	=>	true,	
			'list'	=> '14',
			'online'	=>	true,	
			'order'	=>	10,
			'required'	=> 'group',
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
	 		'order'	=>	100,	
			'required'	=> false,
			'slug'	=> 'middle_name',
	 		'type'	=>	'text',
		),		
		array( // 3
			'dedup'	=>	true,
			'group'	=>	'required',
			'label'	=>	'Last Name',
			'like'	=>	true,
			'list'	=> '14',
			'online'	=>	true,
			'order'	=>	20,
			'required'	=> 'group',
			'slug'	=> 'last_name',
			'type'	=>	'text',
			),	
		array( // 4
			'dedup'	=>	true,
			'group'	=>	'required',
			'label'	=>	'eMail',
			'like'	=>	true,
			'list'	=> '28',
			'online'	=>	true,
			'order'	=>	30,
			'required'	=> 'group',
			'slug'	=> 'email',
			'type'	=>	'email',
			),	
		array( // 5
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'Land Line',
			'like'	=>	true,
			'list'	=> '15',
			'online'	=>	true,
			'order'	=>	70,
			'required'	=> false,
			'slug'	=> 'phone',
			'type'	=>	'text',
			),		
		array(  // 6
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'Mobile Phone',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	80,
			'required'	=> '',
			'slug'	=> 'mobile_phone',
			'type'	=>	'text',

			),
		array( // 7
			'dedup'	=>	true,
			'group'	=>	'contact',
			'label'	=>	'Street Address',
			'like'	=>	true,
			'list'	=> '29',
			'online'	=>	true,
			'order'	=>	40,
			'required'	=> '',
			'slug'	=> 'street_address',
			'type'	=>	'text',

			),
		array( // 8
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'City',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	50,
			'required'	=> '',
			'slug'	=> 'city',
			'type'	=>	'text',
			),
		array( // 9
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'State',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	60,
			'required'	=> false,
			'slug'	=> 'state',
			'type'	=>	'text',
			),
		array( // 10
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'Zip Code',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	65,
			'required'	=> 'individual',
			'slug'	=> 'zip',
			'type'	=>	'text',
			),
		array( // 11
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Job Title',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	90,
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
			'order'	=>	95,
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
			'order'	=>	85,
			'required'	=> '',
			'slug'	=> 'gender_id',
			'type'	=>	array ( 
				array(
					'value'	=> 'm',
					'label'	=>	'Male' ),
				array(
					'value'	=> 'f',
					'label'	=>	'Female' ),
				),
			),
		array( // 14
			'dedup'	=>	false,
			'group'	=>	'personal',
			'label'	=>	'Date of Birth',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	84,	
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
			'order'	=>	97,
			'required'	=> '',
			'slug'	=> 'is_deceased',
			'type'	=>	'check',
			),
		array( // 16
			'dedup'	=>	false,
			'group'	=>	'links',
			'label'	=>	'CiviCRM ID',
			'like'	=>	false,
			'online'	=>	true,
			'order'	=>	1,
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
			'order'	=>	3,
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
			'order'	=>	5,
			'required'	=> false,
			'slug'	=> 'VAN_id',
			'type'	=>	'readonly',
			),
	);
		
	public function __construct() {
		add_action( 'init', array( $this, 'custom_post_type' ), 0 );
		$this->constituent_field_groups = $this->multi_array_key_sort ( $this->constituent_field_groups, 'order' );
		$this->constituent_fields		  = $this->multi_array_key_sort ( $this->constituent_fields, 'order' );
	}

	// Register Custom Post Type
	function custom_post_type() {
	
		$labels = array(
			'name'                => _x( 'Constituents', 'Post Type General Name', 'simple-wp-crm' ),
			'singular_name'       => _x( 'Constituent', 'Post Type Singular Name', 'simple-wp-crm' ),
			'menu_name'           => __( 'Constituents', 'simple-wp-crm' ),
			'parent_item_colon'   => __( 'Parent Constituent', 'simple-wp-crm' ),
			'all_items'           => __( 'All Constituents', 'simple-wp-crm' ),
			'view_item'           => __( 'View Constituent', 'simple-wp-crm' ),
			'add_new_item'        => __( 'Add New Constituent', 'simple-wp-crm' ),
			'add_new'             => __( 'Add New', 'simple-wp-crm' ),
			'edit_item'           => __( 'Edit Constituent', 'simple-wp-crm' ),
			'update_item'         => __( 'Update Constituent', 'simple-wp-crm' ),
			'search_items'        => __( 'Search Constituent', 'simple-wp-crm' ),
			'not_found'           => __( 'Not found', 'simple-wp-crm' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'simple-wp-crm' ),
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
			'label'               => __( 'constituent', 'simple-wp-crm' ),
			'description'         => __( 'constituents -- people', 'simple-wp-crm' ),
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
	
}

$wic_definitions = new WP_Issues_CRM_Definitions;