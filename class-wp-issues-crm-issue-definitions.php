<?php
/*
*
*
* definitions for Issues -- regular posts  . . . no meta fields?
*
*
*
*/

class WP_Issues_CRM_Issue_Definitions {



	public function __construct() {
		
		global $wic_constituent_definitions;
		$this->wic_post_field_groups = $wic_constituent_definitions->multi_array_key_sort ( $this->wic_post_field_groups, 'order' );
		$this->wic_post_fields		 = $this->initialize_wic_post_fields_array();		
		$this->wic_post_fields		 = $wic_constituent_definitions->multi_array_key_sort ( $this->wic_post_fields, 'order' );
		
		add_action('add_meta_boxes', array ( $this, 'wic_call_live_issue_meta_box' ), 10, 2);
		add_action('save_post', array ($this, 'wic_save_live_issue_meta_box' ),10,2);

	}
	
	public $wic_post_type_labels = array (
		'singular' => 'Issue',
		'plural'	  => 'Issues'	
	);	
	
	public $wic_post_type_sort_order = array (
		'orderby' => 'date',
		'order'	  => 'DESC'	
	);		
	
	public $wic_post_type_dups_ok = true;
	
	public $wic_post_field_groups = array (
		array (
			'name'		=> 'case_management',
			'label'		=>	'Issue Management',
			'legend'		=>	'',
			'order'		=>	30,
			'initial-open'	=> false,
		),
		array (
			'name'		=> 'post_info',
			'label'		=>	'Issue Content',
			'legend'		=>	'',
			'order'		=> 10,
			'initial-open'	=> true,
		),

		array (
			'name'		=> 'post_fixed',
			'label'		=>	'Issue Creation',
			'legend'		=>	'These fields can be searched but not changed online.  They are not displayed for new records.',
			'order'		=> 40,
			'initial-open'	=> false,
		),		
		
		array (
			'name'		=> 'post_categories',
			'label'		=>	'Issue Categories',
			'legend'		=>	'',
			'order'		=> 20,
			'initial-open'	=> false,
		),
	);




	
	public $wic_post_fields = array();
		
	private function initialize_wic_post_fields_array() { 
		
	$output = array(
	/*
	* note regarding author, date and status for posts (issues)
	*
	* these three fields are included in the definition array below for form processing and search purposes.
	* for update purposes, they get special handling -- they are never updated through this interface
	* for save purposes, they cannot be set by the user through this interface 
	* these are all updateable through the backend post interface
	*
	* note post status is also tested in form to prevent update of public posts in note format
	*
	* so named fields are populated after new post save in save_update_posts 
	*
	*/

		array( 
			'dedup'	=>	false,
			'group'	=>	'post_fixed',
			'label'	=>	'Created By',
			'like'	=>	false,
			'list'	=> '15',
			'list_call_back_key' => 'wic_get_post_author_display_name',
			'online'	=>	true,
			'order'	=>	40,
			'required'	=> '',
			'readonly_subtype' => 'select',
			'select_array' => 'wic_get_user_list',
			'select_parameter' => 'Administrator',
			'slug'	=> 'wic_post_author', // note, naming this field merely author triggers some kind of wordpress security, causes odd 404's 
			'type'	=>	'readonly',
			'wp_query_parameter' => 'author',
			), 	

		array(  
			'dedup'	=>	false,
			'group'	=>	'post_fixed',
			'label'	=>	'Created Date',
			'like'	=>	false,
			'list'	=> '17',
			'online'	=>	true,
			'order'	=>	70,
			'readonly_subtype' => 'date',
			'required'	=> '',
			'slug'	=> 'post_created_date',
			'type'	=>	'readonly',
			'wp_query_parameter' => 'date',
			), 								
		array(  
			'dedup'	=>	false,
			'group'	=>	'post_fixed',
			'label'	=>	'Visibility',
			'like'	=>	false,
			'list'	=> '10',
			'online'	=>	true,
			'order'	=>	90,
			'required'	=> '',
			'select_array'	=>	array ( 
				array(
					'value'	=> 'publish',
					'label'	=>	'Publicly Published' ),
				array(
					'value'	=> 'private',
					'label'	=>	'Private' ),
				array(
					'value'	=> 'draft',
					'label'	=>	'Draft' ),
				array(
					'value'	=> 'trash',
					'label'	=>	'Trash' ),
				),
			'slug'	=> 'post_status',
			'readonly_subtype' => 'select',
			'type'	=>	'readonly',
			'wp_query_parameter' => 'post_status',
			),
		/*
		*
		*	the search term variable hooks to the wordpress full text search
		* 
		*/		
		array(  
			'dedup'	=>	false,
			'group'	=>	'post_info',
			'label'	=>	'Search Term',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	80,
			'required'	=> '',
			'slug'	=> 'search_term',
			'readonly_subtype' => 'text',
			'type'	=>	'readonly',
			'wp_query_parameter' => 's',
			), 
	/*
	* post title, category and tag can be modified through this interface, 
	* post-title must be included as a required field or else generate database errors on blank saves
	*
	*
	*/		
		array( 
			'dedup'	=>	false,
			'group'	=>	'post_info',
			'label'	=>	'Issue Title',
			'like'	=>	false,
			'list'	=> '30',
			'online'	=>	true,
			'order'	=>	38,
			'required'	=> 'individual',
			'updateonly_subtype' => 'text',
			'slug'	=> 'post_title',
			'type'	=>	'updateonly',
			'wp_query_parameter' => 'post_title',
			), 
		array(  
			'dedup'	=>	false,
			'group'	=>	'post_categories',
			'label'	=>	'', // put alone in group and show group label
			'like'	=>	false,
			'list'	=> '25',
			'list_call_back_id' => 'wic_get_post_categories',
			'online'	=>	true,
			'order'	=>	50,
			'required'	=> '',
			'select_array' => 'wic_get_category_list',
			'select_parameter' => '0',
			'slug'	=> 'cat',
			'type'	=>	'multi_select',
			'wp_query_parameter' => 'cat',
			), 
		array(  
			'dedup'	=>	false,
			'group'	=>	'post_info',
			'label'	=>	'Post Tags',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	60,
			'required'	=> '',
			'slug'	=> 'tag',
			'type'	=>	'textcsv',
			'wp_query_parameter' => 'tag',
			),  				
			
		/* 
		* the following block is meta fields and can be modified and extended without any special considerations
		*
		*/
			array( 
				'dedup'	=>	false,
				'group'	=>	'case_management',
				'label'	=>	'Staff',
				'like'	=>	false,
				'list'	=> '0',
				'online'	=>	true,
				'order'	=>	10,
				'required'	=> '',
				'select_array' => 'wic_get_user_list',
				'select_parameter' => 'Administrator',
				'slug'	=> 'assigned',
				'type'	=>	'select',
				), 
			array( 
				'dedup'	=>	false,
				'group'	=>	'case_management',
				'label'	=>	'Review Date',
				'like'	=>	false,
				'list'	=> '0',
				'online'	=>	true,
				'order'	=>	20,	
				'required'	=> '',
				'slug'	=> 'case_review_date',
				'type'	=>	'date',
				),
			array( 
				'dedup'	=>	false,
				'group'	=>	'case_management',
				'label'	=>	'Issue Status',
				'like'	=>	false,
				'list'	=> '0',
				'online'	=>	true,
				'order'	=>	30,	
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
			);
							

		return $output;
	}
	
	/* following http://www.wproots.com/complex-meta-boxes-in-wordpress/ */	
	function wic_call_live_issue_meta_box($post_type, $post)
	{
	   add_meta_box(
	       'wic_live_issue_setting_box',
	       __( 'Issue Open/Closed for WP Issues CRM', 'wp-issues-crm' ),
	       array( $this, 'wic_live_issue_meta_box' ),
	       'post',
	       'side',
	       'high'
	   );
	}
	
	
	function wic_live_issue_meta_box( $post, $args ) {
		
		global $wic_form_utilities;		
		
	   wp_nonce_field( site_url(__FILE__), 'wic_live_issue_metabox_noncename' );
	
      $wic_live_issue_options = array(
			array(
				'value' =>	'closed',
				'label' =>  'Closed for WP Issues CRM'
			),
			array(
				'value' =>	'open',
				'label' =>  'Open for WP Issues CRM' 
			),
		);	 
	   
		$value = ( null !== get_post_meta($post->ID, 'wic_live_issue', true) ) ? esc_attr( get_post_meta($post->ID, 'wic_live_issue', true)) : '';	   
	   
		$args = array (
			'field_name_id' => 'wic_live_issue',
			'field_label'	=>	'',
			'value'	=> $value,
			'read_only_flag'		=>	false, 
			'field_label_suffix'	=> '',
			'placeholder'			=> __( 'Open/Closed?', 'wp-issues-crm' ),
			'select_array' =>	$wic_live_issue_options,							
		);	  

		echo '<p>' . $wic_form_utilities->create_select_control ( $args ) . '</p>';
	}
	
	function wic_save_live_issue_meta_box($post_id, $post) {
	   if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	   	return;
		}
	       
	   if( ! current_user_can( 'edit_post', $post_id ) ) {
	           return;
	   }
	
	   if ( isset($_POST['wic_live_issue_metabox_noncename']) && 
	   		wp_verify_nonce($_POST['wic_live_issue_metabox_noncename'], site_url(__FILE__)) && check_admin_referer(site_url(__FILE__), 'wic_live_issue_metabox_noncename'))   
	   		{
	           update_post_meta($post_id, 'wic_live_issue', $_POST['wic_live_issue'] );
			   }
	   
	   return;
	}

	public function title_callback( &$next_form_output ) {
		
		// for title, use group email if have it, otherwise use individual email 
		$title = isset ( $next_form_output['post_title'] ) ? $next_form_output['post_title'] : 'untitled';  
		
		return  ( $title );
	}
	



}

$wic_issue_definitions = new WP_Issues_CRM_Issue_Definitions;