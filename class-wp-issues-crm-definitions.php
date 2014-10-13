<?php
/*
* post-type and taxonomy definitions for wp_issues_crm
* 
*/

class WP_Issues_CRM_Definitions {
	
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
			'name'		=> 'case',
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
	 		'order'	=>	100,	
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
/*		array( // 4
			'dedup'	=>	true,
			'group'	=>	'required',
			'label'	=>	'eMail',
			'like'	=>	true,
			'list'	=> '0',
			'online'	=>	false,
			'order'	=>	30,
			'required'	=> 'group', // see note above -- note, in default installation, this field is not accessible online (use group)
			'slug'	=> 'email',
			'type'	=>	'email',
			),	*/
		array( // 4A
			'dedup'	=>	true,
			'group'	=>	'contact',
			'label'	=>	'eMail',
			'like'	=>	true,
			'list'	=> '28',
			'online'	=>	true,
			'order'	=>	31,
			'required'	=> 'group', // see note above -- do not have to include in group required, if want to force to have a fn or ln
			'slug'	=> 'email_group',
			'type'	=>	'emails',
			),				
/*		array( // 5
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'Land Line',
			'like'	=>	true,
			'list'	=> '0',
			'online'	=>	false,
			'order'	=>	70,
			'required'	=> false,
			'slug'	=> 'phone',
			'type'	=>	'text',
			), */		
		array(  // 6
			'dedup'	=>	false,
			'group'	=>	'contact',
			'label'	=>	'Phone',
			'like'	=>	true,
			'list'	=> '15',
			'online'	=>	true,
			'order'	=>	80,
			'required'	=> '',
			'slug'	=> 'phone_numbers',
			'type'	=>	'phones',

			),
/*		array( // 7
			'dedup'	=>	true,
			'group'	=>	'contact',
			'label'	=>	'Street Address',
			'like'	=>	true,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	40,
			'required'	=> '',
			'slug'	=> 'street_address',
			'type'	=>	'text',

			), */
		array( // 7A
			'dedup'	=>	true,
			'group'	=>	'contact',
			'label'	=>	'Address',
			'like'	=>	true,
			'list'	=> '29',
			'online'	=>	true,
			'order'	=>	41,
			'required'	=> '',
			'slug'	=> 'street_addresses',
			'type'	=>	'addresses',
			),	
/*		array( // 8
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
			'required'	=> '',
			'slug'	=> 'zip',
			'type'	=>	'text',
			), */
		array( // 10
			'dedup'	=>	false,
			'group'	=>	'case',
			'label'	=>	'Staff',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	90,
			'required'	=> '',
			'slug'	=> 'assigned',
/*			'user_role' => 'Administrator', */
			'type'	=>	'user',
			),
		array( // 10A
			'dedup'	=>	false,
			'group'	=>	'case',
			'label'	=>	'Review Date',
			'like'	=>	false,
			'list'	=> '0',
			'online'	=>	true,
			'order'	=>	91,	
			'required'	=> '',
			'slug'	=> 'case_review_date',
			'type'	=>	'date',
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
	
	/*
	*
	*	The following group of functions create generic controls -- no field-specific logic
	*		 -- checked, text, selected
	*/	
	
	
	public function create_check_control ( $control_args ) {
		
		/* control args = array (
			'field_name_id' 		=> name/id
			'field_label'			=>	label for field
			'label_class'			=> for css
			'value'					=> from database or blank
			'read_only_flag'		=>	whether should be a read only -- true false
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)								
		);	
		*/			

		$read_only_flag 		= false; 				
		$field_label_suffix 	= '';
		$label_class = 'wic-label';

		
		extract ( $control_args, EXTR_OVERWRITE ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-constituent-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ?  '<label class="' . $label_class . '" for="' . 
				esc_attr( $field_name_id ) . '">' . esc_html( $field_label ) . ' ' . '</label>' : '';
		$control .= '<input class="wic-input-checked"  id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) . 
			'" type="checkbox"  value="1"' . checked( $value, 1, false) . $readonly  .'/>' . 
			$field_label_suffix_span  ;	

		return ( $control );

	}
	
	public function create_text_control ( $control_args ) {
		
		/* control args = array (
			'field_name_id' 		=> name/id
			'field_label'			=>	label for field
			'label_class'			=> for css
			'input_class'			=>	for css
			'placeholder'			=> placeholder in input field
			'value'					=> from database or blank
			'read_only_flag'		=>	whether should be a read only -- true false
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)								
		);	
		*/			

		$read_only_flag 		= false; 				
		$field_label_suffix 	= '';
		$label_class = 'wic-label';
		$input_class = 'wic-input';
		$placeholder = '';

		extract ( $control_args, EXTR_OVERWRITE ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-constituent-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . esc_html( $field_label ) . '</label>' : '' ;
		$control .= '<input class="' . $input_class . '" id="' . esc_attr( $field_name_id )  . 
			'" name="' . esc_attr( $field_name_id ) . '" type="text" placeholder = "' .
			 esc_attr( $placeholder ) . '" value="' . esc_attr ( $value ) . '" ' . $readonly  . '/>' . $field_label_suffix_span; 
			
		return ( $control );

	}
	
	public function create_text_area_control ( $control_args ) {
		
		/* control args = array (
			'field_name_id' 		=> name/id
			'field_label'			=>	label for field
			'label_class'			=> for css
			'input_class'			=>	for css
			'placeholder'			=> placeholder in input field
			'value'					=> from database or blank
			'read_only_flag'		=>	whether should be a read only -- true false
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)								
		);	
		*/			

		$read_only_flag 		= false; 				
		$field_label_suffix 	= '';
		$label_class = 'wic-label';
		$input_class = 'wic-input';
		$placeholder = '';

		
		extract ( $control_args, EXTR_OVERWRITE ); 
	
		$readonly = $read_only_flag ? 'readonly' : '';
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-constituent-form-legend-flag">' .$field_label_suffix . '</span>' : '';
		 
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . esc_attr( $field_label ) . '</label>' : '' ;
		$control .= '<textarea class="' . $input_class . '" id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) . '" type="text" placeholder = "' . 
			esc_attr( $placeholder ) . '" ' . $readonly  . '/>' . esc_textarea( $value ) . '</textarea>' . $field_label_suffix_span;
			
		return ( $control );

	}	
	
	
	public function create_select_control ( $control_args ) {
		
		/* $control_args = array (
			'field_name_id' => name/id
			'field_label'	=>	label for field
			'placeholder' => label that will appear in drop down for empty string
			'value'		=> initial value 
			'label_class'			=> for css
			'field_input_class'			=> for css
			'select_array'	=>	the options for the selected -- key value array with keys 'value' and 'label' 
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)
		*/								

		$label_suffix = '';
		$value = '';
		$label_class = 'wic-label';
		$field_input_class = 'wic-input';
		$placeholder = '';
	
		$value = stripslashes( esc_html ( $value ) ); 

		extract ( $control_args, EXTR_OVERWRITE ); 
		
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-constituent-form-legend-flag">' .$field_label_suffix . '</span>' : '';

		$control = '';
				
		$not_selected_option = array (
			'value' 	=> '',
			'label'	=> $placeholder,								
		);  
		$option_array =  $select_array;
		array_push( $option_array, $not_selected_option );
		
		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . 
				esc_html( $field_label ) . '</label>' : '';
		$control .= '<select class="' . $field_input_class . '" id="' . esc_attr( $field_name_id ) . '" name="' . esc_attr( $field_name_id ) 
				. '" >' ;
		$p = '';
		$r = '';
		foreach ( $option_array as $option ) {
			$label = $option['label'];
			if ( $value == $option['value'] ) { // Make selected first in list
				$p = '<option selected="selected" value="' . esc_attr( $option['value'] ) . '">' . esc_html ( $label ) . '</option>';
			} else {
				$r .= '<option value="' . esc_attr( $option['value'] ) . '">' . esc_html( $label ) . '</option>';
			}
		}
		$control .=	$p . $r .	'</select>' . $field_label_suffix_span;	
	
		return ( $control );
	
	}	

/*
*
*	The functions below are used to create the repeating special groups -- phones, emails, addresses
*    -- they build in logic about these types of data.
*
*  In every instance, the second position of the group array is the main datum.  
* 	It is extracted from the first instance in search functions requiring strings.
*
*  To add a new repeater group x, ( x like phones, emails, addresses ) 
*		+ add x to $serialized_field_types above, 
*		+ add create_x_group function here to set up the display, 
*     + add a validate_x function here to handle form input for each row
*
*/

	
	/* this button will destroy the form element (e.g., paragraph for repeater row) containing it */
	public function create_destroy_button () {

		$button = '<button ' .  
			' class	="destroy-button"' . 
			' onclick = {this.parentNode.parentNode.removeChild(this.parentNode);}' .
			' type 	= "button" ' .
			' name	= "destroy-button" ' .
			' title  = ' . __( 'Remove Row', 'wp-issues-crm' ) .
			' >x</button>';	

		return ($button);
	}
	
	
	/* this button will create a new instance of the templated base paragraph (repeater row) and insert it above itself in the DOM*/
	public function create_add_button ( $base, $button_label ) {
		
		$button ='<div class = "add-button-spacer"></div>' .  
			'<button ' . 
			' class = "row-add-button" ' .
			' id = "' . esc_attr( $base ) . '-add-button" ' .
			' type = "button" ' .
			' onclick="moreFields(\'' . esc_attr( $base ) . '\')" ' .
			' >' . esc_html(  $button_label ) . '</button>'; 

		return ($button);
	}

	/*
	*
	*	output show-hide-button
	*  calls toggleConstituentFormSection in wic-utilities.js
	*
	*/
	public function output_show_hide_toggle_button( $args ) {

		$class 			= 'field-group-show-hide-button';		
		$name_base 		= 'wic-inner-field-group-'  ;
		$name_variable = ''; // group['name']
		$label = ''; // $group['label']
		$show_initial = true;
		
		extract( $args, EXTR_OVERWRITE );

		$show_legend = $show_initial ? __( 'Hide', 'wp-issues-crm' ) : __( 'Show', 'wp-issues-crm' );

		
		$button =  '<button ' . 
		' class = "' . $class . '" ' .
		' id = "' . $name_base . esc_attr( $name_variable ) . '-toggle-button" ' .
		' type = "button" ' .
		' onclick="toggleConstituentFormSection(\'' . $name_base . esc_attr( $name_variable ) . '\')" ' .
		' >' . esc_html ( $label ) . '<span class="show-hide-legend" id="' . $name_base . esc_attr( $name_variable ) .
		'-show-hide-legend">' . $show_legend . '</span>' . '</button>';

		return ($button);
}



		
	public function create_phones_group ( $repeater_group_args ) {
		/*
      *		'repeater_group_id'		=> $field['slug'],
		*		'repeater_group_label'		=> $field['label'],
		*		'repeater_group_data_array'	=>	$next_form_output[$field['slug']],
		*		'repeater_group_label_suffix'	=> $required_individual . $required_group . $contains,		
		*/
		
		
		extract ( $repeater_group_args, EXTR_OVERWRITE );
		
				
		$repeater_group_id = esc_attr( $repeater_group_id );
		// create phones division opening tag 		
		$phone_group_control_set = '<div id = "' . $repeater_group_id . '-control-set' . '">';

		// create a hidden template row for adding phone fields in wic-utilities.js through moreFields() 
		// moreFields will replace the string 'row-template' with row-counter index value after creating the new row
		// this will change row id and the field indexes - the array will be $repeater_group_id[x][y] (x = row, y = field)
		$row = '<p class = "hidden-template" id = "' . $repeater_group_id . '-row-template' . '">'; // template opening line	
	
		$phone_type_array = array ( 
				'field_name_id' 	=> $repeater_group_id . '[row-template][0]',
				'field_label'		=>	'',
				'placeholder' => __( 'Phone type?', 'wp-issues-crm' ),
				'select_array'		=>	$this->phone_type_options,
				'value'			=> '',
				'field_input_class' 	=> 'wic-input wic-phone-type-dropdown',
				'field_label_suffix'	=> '',
			);	
		$row .= $this->create_select_control ( $phone_type_array );

		$phone_number_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][1]',
			'field_label'			=>	'',
 			'value'					=> '', 
 			'placeholder'			=> __( 'Phone number?', 'wic-issues-crm' ),
			'input_class' 	=> 'wic-input wic-phone-number',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_text_control( $phone_number_array );

		$phone_extension_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][2]',
			'field_label'			=>	'',
 			'value'					=> '',
			'placeholder'			=> __( 'Extension?', 'wic-issues-crm' ),
			'input_class' 	=> 'wic-input wic-phone-extension',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_text_control( $phone_extension_array );

		$row .= $this->create_destroy_button ();

		$row .= '</p>';
	
		// put completed template row into phones division			
		$phone_group_control_set .= $row;


		// now proceed to add rows for any existing phones from database or previous form
		$i = '0'; // array index
		
		if ( is_array( $repeater_group_data_array ) ) {

			foreach ( $repeater_group_data_array as $phone_number ) {
				
				// note, in this loop, need only instantiate the changing arguments in the arrays			
				
				$row = '<p class = "phone-number-row" id = "' . $repeater_group_id . '-' . $i . '">';
							
				$phone_type_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][0]';
				$phone_type_array['value']			= $repeater_group_data_array[$i][0];
				$row .= $this->create_select_control ( $phone_type_array );
	
			
				$phone_number_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][1]';
	 			$phone_number_array['value']				= $this->format_phone ( $repeater_group_data_array[$i][1] );
				$row .= $this->create_text_control( $phone_number_array );
	

				$phone_extension_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][2]';
	 			$phone_extension_array['value']				= $repeater_group_data_array[$i][2];
				$row .= $this->create_text_control( $phone_extension_array );
				
				$row .= $this->create_destroy_button ();
				
				$row .= '</p>';
				
				$phone_group_control_set .= $row;
				
				$i++;
	
			}
		}		
		
		$phone_group_control_set .= '<div class = "hidden-template" id = "' . $repeater_group_id . '-row-counter">' . $i . '</div>';
		$phone_group_control_set .= $this->create_add_button ( $repeater_group_id, __( 'Add Phone', 'wp-issues-crm' ) . ' ' . $repeater_group_label_suffix ) ;
		$phone_group_control_set .= '</div>';
		
		
		
		return ($phone_group_control_set);	
	}
	
	/* little function to format phone numbers for display */	
   function format_phone ($raw_phone) {
   	
		$phone = preg_replace( "/[^0-9]/", '', $raw_phone );
   	
		if ( 7 == strlen($phone) ) {
			return ( substr ( $phone, 0, 3 ) . '-' . substr($phone,3,4) );		
		} elseif ( 10  == strlen($phone) ) {
			return ( '(' . substr ( $phone, 0, 3 ) . ') ' . substr($phone, 3, 3) . '-' . substr($phone,6,4) );	
		} else {
			return ($phone);		
		}
    
    }

	/*
	*	repeater validation function for phones
	*/

	function validate_phones( $phone_number_row ) {
		
		$outcome = array(
			'result' 	=> '',
			'error'		=> '',
			'present' 	=> false
		);

		$outcome['result'] = array(
				preg_replace( "/[^0-9]/", '', $phone_number_row[0] ),
				preg_replace( "/[^0-9]/", '', $phone_number_row[1] ),
				preg_replace( "/[^0-9]/", '', $phone_number_row[2] ), 
			);
			
		$outcome['present'] = $outcome['result'][1] > '' ;
		
		return( $outcome );		
			
	}
	
	public function create_emails_group ( $email_group_args ) {
		/*
      *		'repeater_group_id'		=> $field['slug'],
		*		'repeater_group_label'		=> $field['label'],
		*		'repeater_group_data_array'	=>	$next_form_output[$field['slug']],
		*		'repeater_group_label_suffix'	=> $required_individual . $required_group . $contains,		
		*/
		
		
		extract ( $email_group_args, EXTR_OVERWRITE );
		
		$repeater_group_id = esc_attr( $repeater_group_id );
		// create emails division opening tag 		
		$email_group_control_set = '<div id = "' . $repeater_group_id . '-control-set' . '">';

		// create a hidden template row for adding email fields in wic-utilities.js through moreFields() 
		// moreFields will replace the string 'row-template' with row-counter index value after creating the new row
		// this will change row id and the field indexes - the array will be $repeater_group_id[x][y] (x = row, y = field)
		$row = '<p class = "hidden-template" id = "' . $repeater_group_id . '-row-template' . '">'; // template opening line	
	
		$email_type_array = array ( 
				'field_name_id' 	=> $repeater_group_id . '[row-template][0]',
				'field_label'		=>	'',
				'placeholder' => __( 'eMail type?', 'wp-issues-crm' ),
				'select_array'		=>	$this->email_type_options,
				'value'			=> '',
				'field_input_class' 	=> 'wic-input wic-email-type-dropdown',
				'field_label_suffix'	=> '',
			);	
		$row .= $this->create_select_control ( $email_type_array );

		$email_address_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][1]',
			'field_label'			=>	'',
 			'value'					=> '', 
 			'placeholder'			=> __( 'eMail address?', 'wic-issues-crm' ),
			'input_class' 		=> 'wic-input wic-email-address',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_text_control( $email_address_array );

		$row .= $this->create_destroy_button ();

		$row .= '</p>';
	
		// put completed template row into emails division			
		$email_group_control_set .= $row;


		// now proceed to add rows for any existing emails from database or previous form
		$i = '0'; // array index
		
		if ( is_array( $repeater_group_data_array ) ) {

			foreach ( $repeater_group_data_array as $email_address ) {
				
				// note, in this loop, need only instantiate the changing arguments in the arrays			
				
				$row = '<p class = "email-address-row" id = "' . $repeater_group_id . '-' . $i . '">';
							
				$email_type_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][0]';
				$email_type_array['value']			= $repeater_group_data_array[$i][0];
				$row .= $this->create_select_control ( $email_type_array );
	
			
				$email_address_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][1]';
	 			$email_address_array['value']				= $repeater_group_data_array[$i][1];
				$row .= $this->create_text_control( $email_address_array );
				
				$row .= $this->create_destroy_button ();
				
				$row .= '</p>';
				
				$email_group_control_set .= $row;
				
				$i++;
	
			}
		}		
		$email_group_control_set .= '<div class = "hidden-template" id = "' . $repeater_group_id . '-row-counter">' . $i . '</div>';		
		$email_group_control_set .= $this->create_add_button ( $repeater_group_id, __( 'Add eMail', 'wp-issues-crm' ) . ' ' . $repeater_group_label_suffix ) ;
		$email_group_control_set .= '</div>';

		
		
		return ($email_group_control_set);	
	}

	function validate_emails( $email_row ) {
		
		$outcome = array(
			'result' 	=> '',
			'error'		=> '',
			'present' 	=> false
		);

		$outcome['result'] = array(
				preg_replace( "/[^0-9]/", '', $email_row[0] ),
				stripslashes( sanitize_text_field ( $email_row[1] )),
			);
			
		$outcome['present'] = $outcome['result'][1] > '';
		
  		if ( $outcome['present'] ) {
	   	$outcome['error'] =  $this->validate_individual_email( $outcome['result'][1] );
		}	
		
		return( $outcome );		
			
	}
	
	function validate_individual_email( $email ) {
		$error = filter_var( $email, FILTER_VALIDATE_EMAIL ) ? '' : __( 'Email address appears to be not valid. ', 'wp-issues-crm' );
		return $error;	
	}	

/*
*	function for address groups
*
*/

		
	public function create_addresses_group ( $repeater_group_args ) {
		/*
      *		'repeater_group_id'		=> $field['slug'],
		*		'repeater_group_label'		=> $field['label'],
		*		'repeater_group_data_array'	=>	$next_form_output[$field['slug']],
		*		'repeater_group_label_suffix'	=> $required_individual . $required_group . $contains,		
		*/
		
		
		extract ( $repeater_group_args, EXTR_OVERWRITE );
		
		$repeater_group_id = esc_attr( $repeater_group_id );
		// create addresss division opening tag 		
		$address_group_control_set = '<div id = "' . $repeater_group_id . '-control-set' . '">';

		// create a hidden template row for adding address fields in wic-utilities.js through moreFields() 
		// moreFields will replace the string 'row-template' with row-counter index value after creating the new row
		// this will change row id and the field indexes - the array will be $repeater_group_id[x][y] (x = row, y = field)
		$row = '<p class = "hidden-template" id = "' . $repeater_group_id . '-row-template' . '">'; // template opening line	
	
		$address_type_array = array ( 
				'field_name_id' 	=> $repeater_group_id . '[row-template][0]',
				'field_label'		=>	'',
				'placeholder' => __( 'Address type?', 'wp-issues-crm' ),
				'select_array'		=>	$this->address_type_options,
				'value'			=> '',
				'field_input_class' 	=> 'wic-input wic-address-type-dropdown',
				'field_label_suffix'	=> '',
			);	
		$row .= $this->create_select_control ( $address_type_array );

		$address_street_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][1]',
			'field_label'			=>	'',
 			'value'					=> '', 
 			'placeholder'			=> __( 'Street Address?', 'wic-issues-crm' ),
			'input_class' 	=> 'wic-input wic-address-street',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_text_control( $address_street_array );

		$address_zip_array = array ( 
			'field_name_id' 	=> $repeater_group_id . '[row-template][2]',
			'field_label'			=>	'',
			'placeholder' => __( 'City/Zip?', 'wp-issues-crm' ),
			'select_array'		=>	$this->address_zip_options,
			'value'			=> '',
			'field_input_class' 	=> 'wic-input wic-address-zip',
			'field_label_suffix'	=> '',
		);

		$row .= $this->create_select_control( $address_zip_array );

		$row .= $this->create_destroy_button ();

		$row .= '</p>';
	
		// put completed template row into addresss division			
		$address_group_control_set .= $row;


		// now proceed to add rows for any existing addresss from database or previous form
		$i = '0'; // array index
		
		if ( is_array( $repeater_group_data_array ) ) {

			foreach ( $repeater_group_data_array as $address_number ) {
				
				// note, in this loop, need only instantiate the changing arguments in the arrays			
								
				$row = '<p class = "address-number-row" id = "' . $repeater_group_id . '-' . $i . '">';
							
				$address_type_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][0]';
				$address_type_array['value']			= $repeater_group_data_array[$i][0];
				$row .= $this->create_select_control ( $address_type_array );
	
			
				$address_street_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][1]';
	 			$address_street_array['value']				= $repeater_group_data_array[$i][1];
				$row .= $this->create_text_control( $address_street_array );
	

				$address_zip_array['field_name_id'] 	= $repeater_group_id . '[' . $i  . '][2]';
	 			$address_zip_array['value']				= $repeater_group_data_array[$i][2];
				$row .= $this->create_select_control( $address_zip_array );
				
				$row .= $this->create_destroy_button ();
				
				$row .= '</p>';
				
				$address_group_control_set .= $row;
				
				$i++;
	
			}
		}		
		$address_group_control_set .= '<div class = "hidden-template" id = "' . $repeater_group_id . '-row-counter">' . $i . '</div>';		
		$address_group_control_set .= $this->create_add_button ( $repeater_group_id, __( 'Add Address', 'wp-issues-crm' ) . ' ' . $repeater_group_label_suffix ) ;
		$address_group_control_set .= '</div>';

		
		
		return ($address_group_control_set);	
	}
	function validate_addresses( $address_row ) {
		
		$outcome = array(
			'result' 	=> '',
			'error'		=> '',
			'present' 	=> false
		);

		$outcome['result'] = array(
				preg_replace( "/[^0-9]/", '', $address_row[0] ),
				stripslashes( sanitize_text_field ( $address_row[1] ) ),
				stripslashes( sanitize_text_field ( $address_row[2] ) ),
			);
			
		$outcome['present'] = $outcome['result'][1] > '' || $outcome['result'][2] > '' ;
		
   	$outcome['error'] =  '';

		return( $outcome );		
			
	}
	
	public function format_constituent_notes ( $notes ) {

		$current_user = wp_get_current_user();
				
		$output = '<div class = "wic-notes-entry">' .
						'<div class = "wic-notes-header">' .
							'<div class = "wic-notes-author">' . __( 'Note by ' , 'wp-issues-crm' ) .  $current_user->display_name . '</div>' .
							'<div class = "wic-notes-date">' . '(' . date('Y-m-d, h:i:s A' ) . ')' . ':</div>' .
						'</div>' .
						'<div class = "wic-notes-content">' .
							$notes .
						'</div>' .
					'</div>';
					
		return ($output); 
	}	
	
	
	
 }

$wic_definitions = new WP_Issues_CRM_Definitions;