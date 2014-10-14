<?php
/*
*
* class-wp-issues-crm-constituent-contact definitions.php
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

class WP_Issues_CRM_Touch_Definitions {
	
	public function __construct() {
		echo $this->test;
		global $wic_constituent_definitions;
		// Hook into the 'init' action
		add_action( 'init', 'wic_touch', 0 );
		$this->constituent_contact_field_groups = $wic_constituent_definitions->multi_array_key_sort ( $this->touch_field_groups, 'order' );
		$this->constituent_contact_fields		 = $wic_constituent_definitions->multi_array_key_sort ( $this->touch_fields, 'order' );
	}

	public $touch_field_groups = array (

		array (
			'name'		=> 'touch',
			'label'		=>	'Communication Details',
			'legend'		=>	'',
			'order'		=> 10,
			'initial-open'	=> true,
		),
		array (
			'name'		=> 'case_management',
			'label'		=>	'Case Management',
			'legend'		=>	'',
			'order'		=> 20,
			'initial-open'	=> false,

		),	
	);
 
  public $test = 'This sucks.';
   public $value = 'this sucks'; // $this->touch_field_groups;
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
			'dedup'	=>	false,	
			'group'	=>	'touch',
			'label'	=>	'Constituent',
			'like'	=>	false,	
			'list'	=> '0',
			'online'	=>	true,	
			'order'	=>	10,
			'required'	=> 'individual', // but always supplied
			'slug'	=> 'touch_constituent_id', 	
			'type'	=>	'text', 	

			),		
		array( // 2
	 		'dedup'	=>	false,
	 		'group'	=>	'touch',
	 		'label'	=>	'Issue',
	 		'like'	=>	false,
			'list'	=> '0',
	 		'online'	=>	true,
	 		'order'	=>	20,	
			'required'	=> 'individual',
			'slug'	=> 'touch_issue_id',
	 		'type'	=>	'text',
		),		
		array( // 3 (might be different from date of entry -- leave post_date as record of that, use meta for date of touch)
	 		'dedup'	=>	false,
	 		'group'	=>	'touch',
	 		'label'	=>	'Date',
	 		'like'	=>	false,
			'list'	=> '0',
	 		'online'	=>	true,
	 		'order'	=>	30,	
			'required'	=> 'individual',
			'slug'	=> 'touch_date',
	 		'type'	=>	'date',
		),		
		// let entered-by be the user who enters -- i.e., author -- no meta for author
		array( // 4
	 		'dedup'	=>	false,
	 		'group'	=>	'touch',
	 		'label'	=>	'Pro/Con',
	 		'like'	=>	false,
			'list'	=> '0',
	 		'online'	=>	true,
	 		'order'	=>	40,	
			'required'	=> '',
			'select_array' => 'test', // $value, // 'touch',// $this->touch_pro_con_options,
			'slug'	=> 'touch_pro_con',
	 		'type'	=>	'select',
		),	
		// let entered-by be the user who enters -- i.e., author -- no meta
		array( // 5
	 		'dedup'	=>	false,
	 		'group'	=>	'touch',
	 		'label'	=>	'Touch Type',
	 		'like'	=>	false,
			'list'	=> '0',
	 		'online'	=>	true,
	 		'order'	=>	50,	
			'required'	=> '',
			'select_array' => 'test',  // $this->touch_type_options,
			'slug'	=> 'touch_type',
	 		'type'	=>	'select',
		),
		// don't use "subject" value as a meta -- title will be fixed by details and info that could be in title belongs to the issue
		// post content is detail . . . attachments?
		array( // 6
			'dedup'	=>	false,
			'group'	=>	'case_management',
			'label'	=>	'Staff',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	60,
			'required'	=> '',
			'slug'	=> 'touch_assigned',
			'user_role' => 'Administrator', 
			'type'	=>	'user',
			), 
		array( // 7
			'dedup'	=>	false,
			'group'	=>	'case_management',
			'label'	=>	'Review Date',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	70,	
			'required'	=> '',
			'slug'	=> 'touch_case_review_date',
			'type'	=>	'date',
			),
		array( // 8
			'dedup'	=>	false,
			'group'	=>	'case_management',
			'label'	=>	'Case Status',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	80,	
			'required'	=> '',
			'slug'	=> 'touch_case_status',
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
	);



		
// Register Custom Post Type
function wic_touch() {

	$labels = array(
		'name'                => _x( 'Touches', 'Post Type General Name', 'wp-issues-crm' ),
		'singular_name'       => _x( 'Touch', 'Post Type Singular Name', 'wp-issues-crm' ),
		'menu_name'           => __( 'Touches', 'wp-issues-crm' ),
		'parent_item_colon'   => __( 'Parent Item:', 'wp-issues-crm' ),
		'all_items'           => __( 'All Constituent Touches', 'wp-issues-crm' ),
		'view_item'           => __( 'View Touch', 'wp-issues-crm' ),
		'add_new_item'        => __( 'Add New Touch', 'wp-issues-crm' ),
		'add_new'             => __( 'Add New', 'wp-issues-crm' ),
		'edit_item'           => __( 'Edit Constituent Touch', 'wp-issues-crm' ),
		'update_item'         => __( 'Update Constituent Touch', 'wp-issues-crm' ),
		'search_items'        => __( 'Search Constituent Touch', 'wp-issues-crm' ),
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
		'label'               => __( 'wic_touch', 'wp-issues-crm' ),
		'description'         => __( 'Constituent touches -- emails, calls, letters', 'wp-issues-crm' ),
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
	register_post_type( 'wic_touch', $args );

}



// Hook into the 'init' action

/*
*
* Option lists
*
*/

	public $touch_pro_con_options = array ( 
		array(
			'value'	=> '0',
			'label'	=>	'Pro' ),
		array(
			'value'	=> '1',
			'label'	=>	'Con' ),
		);

	public $touch_type_options = array ( 
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

$wic_touch_definitions = new WP_Issues_CRM_Touch_Definitions;