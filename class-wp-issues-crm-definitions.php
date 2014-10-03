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
			'legend'		=>	'Constituents must be identified by at least one of these fields.',
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
			'legend'		=>	'Cannot be updated online.',
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
	  	*  . . . . not implemented: 'readonly', 'required', 'searchable'
	  	*/
		array( 'slug'	=> 'first_name', 		
			'label'	=>	'First Name',					
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	true,				
			'dedup'	=>	true,		
			'group'	=>	'required',
			'order'	=>	10,	
			),		
		array( 'slug'	=> 'middle_name',
	 		'label'	=>	'Middle Name',
	 		'online'	=>	false,		
	 		'type'	=>	'text', 		
	 		'like'	=>	false, 			
	 		'dedup'	=>	false,	
	 		'group'	=>	'personal',		
	 		'order'	=>	100,	
		 	),		
		array( 'slug'	=> 'last_name',
			'label'	=>	'Last Name',					
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	true, 			
			'dedup'	=>	true,		
			'group'	=>	'required',		
			'order'	=>	20,	
			),		
		array( 'slug'	=> 'email', 				
			'label'	=>	'eMail',							
			'online'	=>	true,			
			'type'	=>	'email', 	
			'like'	=>	true,				
			'dedup'	=>	true,		
			'group'	=>	'required',		
			'order'	=>	30,	
			),	
		array( 'slug'	=> 'phone', 				
			'label'	=>	'Land Line',					
			'online'	=>	true,			
			'type'	=>	'text',  	
			'like'	=>	true, 			
			'dedup'	=>	false,	
			'group'	=>	'contact',		
			'order'	=>	70,	
			),		
		array( 'slug'	=> 'mobile_phone',		
			'label'	=>	'Mobile Phone',				
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'contact',		
			'order'	=>	80,	
			),
		array( 'slug'	=> 'street_address', 	
			'label'	=>	'Street Address',				
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	true, 			
			'dedup'	=>	true,		
			'group'	=>	'contact',		
			'order'	=>	40,	
			),
		array( 'slug'	=> 'city', 				
			'label'	=>	'City',							
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'contact',		
			'order'	=>	50,	
			),
		array( 'slug'	=> 'state',				
			'label'	=>	'State', 						
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'contact',		
			'order'	=>	60,	
			),
		array( 'slug'	=> 'zip',
			'label'	=>	'Zip Code', 					
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'contact',		
			'order'	=>	65,	
			),
		array( 'slug'	=> 'job_title', 		
			'label'	=>	'Job Title',					
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'personal',		
			'order'	=>	90,	
			),
		array( 'slug'	=> 'organization_name',
			'label'	=>	'Organization',				
			'online'	=>	true,			
			'type'	=>	'text', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'personal',		
			'order'	=>	95,	
			),
		array( 
			'slug'	=> 'gender_id', 			
			'label'	=>	'Gender',						
			'online'	=>	true,		
			'type'	=>	array ( 
				array(
					'value'	=> 'M',
					'label'	=>	'Male' ),
				array(
					'value'	=> 'F',
					'label'	=>	'Female' ),
				),
			'like'	=>	false,	
			'dedup'	=>	false, 	
			'group'	=>	'personal',		
			'order'	=>	85,	
			),
		array( 'slug'	=> 'birth_date',		
			'label'	=>	'Date of Birth',				
			'online'	=>	true,			
			'type'	=>	'date', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'personal',		
			'order'	=>	84,	),
		array( 'slug'	=> 'is_deceased', 	
			'label'	=>	'Is Deceased',					
			'online'	=>	true,		
			'type'	=>	'check',  	
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'personal',		
			'order'	=>	87,	
			),
		array( 'slug'	=> 'civicrm_id', 	
			'label'	=>	'CiviCRM ID',					
			'online'	=>	false,		
			'type'	=>	'readonly',  	
			'like'	=>	false,			
			'dedup'	=>	false,	
			'group'	=>	'links',			
			'order'	=>	1,	
			),
		array( 'slug'	=> 'ss_id',			
			'label'	=>	'Secretary of State ID',	
			'online'	=>	true,		
			'type'	=>	'readonly', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'links',			
			'order'	=>	3,	
			),
		array( 'slug'	=> 'VAN_id', 			
			'label'	=>	'VAN ID',						
			'online'	=>	true,		
			'type'	=>	'readonly', 		
			'like'	=>	false, 			
			'dedup'	=>	false,	
			'group'	=>	'links',			
			'order'	=>	5, 
			),
	);
		
	public function __construct() {
		add_action( 'init', array( $this, 'custom_post_type' ), 0 );
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
			'taxonomies'          => array( 'category' ),
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
	
}

$wic_definitions = new WP_Issues_CRM_Definitions;