<?php
/*
*
* class-wp-issues-crm-activity-definitions.php
*
* post-type and field definitions for wp_issues_crm constituent contacts
*
*
* contents: 
*   group and field definitions  
*   custom post type
*   option arrays for fields
* 
*/

class WP_Issues_CRM_Activity_Definitions {
	
	public function __construct() {

		global $wic_constituent_definitions;
		// Hook into the 'init' action
		add_action( 'init',  array( $this,'wic_activity' ), 0 ) ;
		$this->wic_post_field_groups = $wic_constituent_definitions->multi_array_key_sort ( $this->wic_post_field_groups, 'order' );
		$this->wic_post_fields		 = $this->initialize_wic_post_fields_array();		
		$this->wic_post_fields		 = $wic_constituent_definitions->multi_array_key_sort ( $this->wic_post_fields, 'order' );
		
	}

	public $wic_post_field_groups = array (

		array (
			'name'		=> 'activity',
			'label'		=>	'Activity Details',
			'legend'		=>	'',
			'order'		=> 10,
			'initial-open'	=> true,
		),
	/*	array (
			'name'		=> 'case_management',
			'label'		=>	'Case Management',
			'legend'		=>	'',
			'order'		=> 20,
			'initial-open'	=> false,

		), */	
	);
  
	public $wic_post_fields = array();

	// to initialize this class property with other class properties, had to do it in a function, not directly.

	public function initialize_wic_post_fields_array() {
 
		$output = array( 
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
				'dedup'	=>	false,	
				'group'	=>	'activity',
				'label'	=>	'Constituent',
				'like'	=>	false,	
				'list'	=> '0',
				'online'	=>	true,	
				'order'	=>	10,
				'required'	=> 'individual', // but always supplied
				'slug'	=> 'activity_constituent_id', 	
				'type'	=>	'text', 	
				),		
			array( // 2
		 		'dedup'	=>	false,
		 		'group'	=>	'activity',
		 		'label'	=>	'Issue',
		 		'like'	=>	false,
				'list'	=> '0',
		 		'online'	=>	true,
		 		'order'	=>	20,	
				'required'	=> 'individual',
				'slug'	=> 'activity_issue_id',
		 		'type'	=>	'text',
			),		
			array( // 3 (might be different from date of entry -- leave post_date as record of that, use meta for date of activity)
		 		'dedup'	=>	false,
		 		'group'	=>	'activity',
		 		'label'	=>	'Date',
		 		'like'	=>	false,
				'list'	=> '0',
		 		'online'	=>	true,
		 		'order'	=>	30,	
				'required'	=> 'individual',
				'slug'	=> 'activity_date',
		 		'type'	=>	'date',
			),		
			// let entered-by be the user who enters -- i.e., author -- no meta for author
			array( // 4
		 		'dedup'	=>	false,
		 		'group'	=>	'activity',
		 		'label'	=>	'Pro/Con',
		 		'like'	=>	false,
				'list'	=> '0',
		 		'online'	=>	true,
		 		'order'	=>	40,	
				'required'	=> '',
				'select_array' => $this->activity_pro_con_options,// $this->activity_type_options, // $value, // 'activity',// $this->activity_pro_con_options,
				'slug'	=> 'activity_pro_con',
		 		'type'	=>	'select',
			),	
			// let entered-by be the user who enters -- i.e., author -- no meta
			array( // 5
		 		'dedup'	=>	false,
		 		'group'	=>	'activity',
		 		'label'	=>	'Activity Type',
		 		'like'	=>	false,
				'list'	=> '0',
		 		'online'	=>	true,
		 		'order'	=>	50,	
				'required'	=> '',
				'select_array' => $this->activity_type_options,
				'slug'	=> 'activity_type',
		 		'type'	=>	'select',
			),
			// don't use "subject" value as a meta -- title will be fixed by details and info that could be in title belongs to the issue
			// post content is detail . . . attachments?
			/* the next three fields could be uncommented to allow case management at the activity level
			array( // 6
				'dedup'	=>	false,
				'group'	=>	'case_management',
				'label'	=>	'Staff',
				'like'	=>	false,
				'list'	=> '0',
				'online'	=>	false,
				'order'	=>	60,
				'required'	=> '',
				'slug'	=> 'activity_assigned',
				'user_role' => 'Administrator', 
				'type'	=>	'user',
				), 
			array( // 7
				'dedup'	=>	false,
				'group'	=>	'case_management',
				'label'	=>	'Review Date',
				'like'	=>	false,
				'list'	=> '0',
				'online'	=>	false,
				'order'	=>	70,	
				'required'	=> '',
				'slug'	=> 'activity_case_review_date',
				'type'	=>	'date',
				),
			array( // 8
				'dedup'	=>	false,
				'group'	=>	'case_management',
				'label'	=>	'Case Status',
				'like'	=>	false,
				'list'	=> '0',
				'online'	=>	false,
				'order'	=>	80,	
				'required'	=> '',
				'slug'	=> 'activity_case_status',
				'select_array'	=>	array ( 
					array(
						'value'	=> '0',
						'label'	=>	'Closed' ),
					array(
						'value'	=> '1',
						'label'	=>	'Open' ),
					),
				'type'	=> 'select',
			),		*/
		);
	   return($output);
	}

		
// Register Custom Post Type
function wic_activity() {

	$labels = array(
		'name'                => _x( 'Activities', 'Post Type General Name', 'wp-issues-crm' ),
		'singular_name'       => _x( 'Activity', 'Post Type Singular Name', 'wp-issues-crm' ),
		'menu_name'           => __( 'Activities', 'wp-issues-crm' ),
		'parent_item_colon'   => __( 'Parent Item:', 'wp-issues-crm' ),
		'all_items'           => __( 'All Constituent Activities', 'wp-issues-crm' ),
		'view_item'           => __( 'View Activity', 'wp-issues-crm' ),
		'add_new_item'        => __( 'Add New Activity', 'wp-issues-crm' ),
		'add_new'             => __( 'Add New', 'wp-issues-crm' ),
		'edit_item'           => __( 'Edit Constituent Activity', 'wp-issues-crm' ),
		'update_item'         => __( 'Update Constituent Activity', 'wp-issues-crm' ),
		'search_items'        => __( 'Search Constituent Activity', 'wp-issues-crm' ),
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
		'label'               => __( 'wic_activity', 'wp-issues-crm' ),
		'description'         => __( 'Constituent Activities -- emails, calls, letters', 'wp-issues-crm' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'custom-fields', ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'menu_position'       => 100,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capabilities'        => $capabilities,
	);
	register_post_type( 'wic_activity', $args );

}



// Hook into the 'init' action

/*
*
* Option lists
*
*/

	public $activity_pro_con_options = array ( 
		array(
			'value'	=> '0',
			'label'	=>	'Pro' ),
		array(
			'value'	=> '1',
			'label'	=>	'Con' ),
		);

	public $activity_type_options = array ( 
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

 }

$wic_activity_definitions = new WP_Issues_CRM_Activity_Definitions;