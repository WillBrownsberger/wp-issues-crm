<?php
/*
*
*	wic-entity-issue.php
*
*  This class is instantiated and takes control from the parent class in the parent constructor
*  It takes action on user requests which are the named functions.
*  It receives the $args passed from the button ( via the dashboard ).  
*	It is able to use generic functions from the parent.
*
*/

class WIC_Entity_Trend extends WIC_Entity_Parent {

	protected function set_entity_parms( $args ) { // 
		// accepts args to comply with abstract function definition, but as a top level entity does not process them -- no instance arg
		$this->entity = 'trend';
	} 

	// handle a request for a new search form
	protected function new_form() { 
		$this->new_form_generic( 'WIC_Form_Trend_Search' );
		return;
	}
	
	// handle a search request coming from a search form
	protected function form_search () { 
		$this->form_search_generic ( 'WIC_Form_Trend_Search_Again', 'WIC_Form_Trend_Search_Again');
		return;				
	}

	// handle search results
	protected function handle_search_results ( $wic_query, $not_found_form, $found_form ) {
		$sql = $wic_query->sql;
		if ( 0 == $wic_query->found_count ) {
			$message = __( 'No activity found matching search criteria matching record found -- you can search again.', 'wp-issues-crm' );
			$message_level =  'error';
			var_dump ( $this->data_object_array );
			die;
			$form = new $not_found_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level, $sql );			
		} else { 
			$message = sprintf( __( 'Showing %s issues with activity matching selection criteria -- you can search again.' , 'wp-issues-crm' ), $wic_query->found_count );
			$message_level = 'notice';	
			$form = new $found_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level, $sql );
			$lister_class = 'WIC_List_Trend' ;
			$lister = new $lister_class;
			$list = $lister->format_entity_list( $wic_query, false );
			echo $list;	
		}
	}


	//handle a search request coming search log
	protected function redo_search_from_query ( $meta_query_array ) {
		$this->redo_search_from_meta_query ( $meta_query_array, 'WIC_Form_Issue_Save', 'WIC_Form_Issue_Update' );
		return;
	}	
	
	
	protected function special_entity_value_hook ( &$wic_access_object ) {
		$control = $this->data_object_array['post_date'];
		$post_date = $control->get_value();
		if ( '' == $post_date ) { 
			$this->data_object_array['post_author']->set_value( $wic_access_object->post_author );
			$this->data_object_array['post_date']->set_value( $wic_access_object->post_date );
			$this->data_object_array['post_status']->set_value( $wic_access_object->post_status );
		}		
	}	
	
	protected function list_after_form  ( &$wic_query ) {
		
		// extract $post_id	
		$post_id = $this->data_object_array['ID']->get_value();
		
		// retrieve ID's of constituents referencing this issue in activities or comments
		$args = array(
			'id_array' => array ( $post_id ),
			'search_id' => $wic_query->search_id,
			); 
		$wic_comment_query = new WIC_Entity_Comment ( 'get_constituents_by_issue_id', $args ) ;
		
		// append the list to the form
		if ( 0 < $wic_comment_query->found_count ) {
			$lister = new WIC_List_Constituent;
			$list = $lister->format_entity_list( $wic_comment_query, true );
		echo $list;			 
		}	else {
			echo '<div id="no-activities-found-message">' . __( 'No comments or activities found for issue.', 'wp-issue-crm' ) . '</div>';
		} 
	}		
	
	
	
	/***************************************************************************
	*
	* Constituent properties, setters and getters
	*
	****************************************************************************/
	
	public static function get_activity_type_options ()	{
		return WIC_Entity_Activity::get_activity_type_options();
	}
	
	public static function get_last_updated_by_options() {
		return WIC_Entity_Issue::get_issue_staff_options();	
	}
	
	/* 	

	private static $category_search_mode_options = array (
		array(
			'value'	=> 'cat',
			'label'	=>	'Post must have ANY of selected categories and child categories will be included.' ),
		array(
			'value'	=> 'category__in',
			'label'	=>	'Post must have ANY of selected categories and child categories will NOT be included.' ),
		array(
			'value'	=> 'category__and',
			'label'	=>	'Post must have ALL selected categories.' ),
		array(
			'value'	=> 'category__not_in',
			'label'	=>	'Post must have NONE of selected categories.' ),
 	);

	private static $wic_live_issue_options = array (
		array(
			'value'	=> 'closed',
			'label'	=>	'Closed' ),
		array(
			'value'	=> 'open',
			'label'	=>	'Open' ),
	);


  	private static $follow_up_status_options = array ( 
		array(
			'value'	=> 'closed',
			'label'	=>	'Closed' ),
		array(
			'value'	=> 'open',
			'label'	=>	'Open' ),
	);
	
	private static $post_status_options = array (
 		array(
			'value'	=> 'publish',
			'label'	=>	'Public' ),
		array(
			'value'	=> 'private',
			'label'	=>	'Private' ),
		array(
			'value'	=> 'draft',
			'label'	=>	'Draft' ),
		array(
			'value'	=> 'trash',
			'label'	=>	'Trash' ),
	);	

	private static $retrieve_limit_options = array ( 
		array(
			'value'	=> '50',
			'label'	=>	'Up to 50' ),
		array(
			'value'	=> '100',
			'label'	=>	'Up to 100' ),
		array(
			'value'	=> '500',
			'label'	=>	'Up to 500' ),
		);
	
	
	//	option array get functions
	

	public static function get_post_category_options() {
		global $wic_category_select_array;
		global $wic_category_array_depth;
		$wic_category_select_array = array();
		$wic_category_array_depth = 0;
		return ( self::wic_get_category_list(0) );
	} 		


	private static function wic_get_category_list ( $parent ) {

		global $wic_category_select_array;
		global $wic_category_array_depth;
		
		$wic_category_array_depth++;		
		// echo " depth is now $wic_category_array_depth";
		$args = array(
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 0,
			'taxonomy'                 => 'category',
			'pad_counts'               => false, 
			'parent'							=> $parent,
		); 

		$categories = get_categories( $args );
		if ( 0 < count ( $categories ) ) {		
			foreach ( $categories as $category ) {
				$temp_array = array (
					'value' => $category->term_id,
					'label' => $category->name,
					'class' => 'wic-multi-select-depth-' . $wic_category_array_depth,
				);			
				$wic_category_select_array[] = $temp_array;
				self::wic_get_category_list ($category->term_id);	
			}
		}
		$wic_category_array_depth--;
		return ( $wic_category_select_array );
	} 	

	public static function get_post_categories ( $post_id ) {
		$categories = get_the_category ( $post_id );
		$return_list = '';
		foreach ( $categories as $category ) {
			$return_list .= ( '' == $return_list ) ? $category->cat_name : ', ' . $category->cat_name;		
				}
		return ( $return_list ) ;	
	}	
	
	public static function get_category_search_mode_options() {
		return self::$category_search_mode_options;
	} 

	public static function wic_live_issue_formatter( $value ) {
		return WIC_Function_Utilities::value_label_lookup ( $value,  self::$wic_live_issue_options );	
	} 


	public static function get_wic_live_issue_options() {
		return self::$wic_live_issue_options; 
	} 

	
	public static function get_issue_staff_options() {
		return WIC_Function_Utilities::get_administrator_array();
	}

		
  	public static function get_follow_up_status_options() {
		return self::$follow_up_status_options; 
	} 

		
  	public static function follow_up_status_formatter( $value ) {
		return WIC_Function_Utilities::value_label_lookup ( $value,  self::$follow_up_status_options );	 
	} 



	public static function issue_staff_formatter ( $user_id ) {
		
		$display_name = '';		
		if ( isset ( $user_id ) ) { 
			if ( $user_id > 0 ) {
				$user =  get_users( array( 'fields' => array( 'display_name' ), 'include' => array ( $user_id ) ) );
				$display_name = $user[0]->display_name; // best to generate an error here if this is not set on non-zero user_id
			}
		}
		return ( $display_name );
	}

	public static function get_post_author_label ( $user_id ) {
		return ( self::issue_staff_formatter ( $user_id ) );
	}
	
	public static function get_post_author_options () {
	
		global $wpdb;
		
		$query_args = array(
			'orderby' => 'name',
			'order' => 'ASC',
			'number' => '',
			'fields' => array ( 'ID' , 'display_name' ),
		);
		$authors = get_users( $query_args );
		
		$author_options = array();
		foreach ( $authors as $author ) {
			$author_options[] = array (
				'value' => $author->ID,
				'label' => $author->display_name,			
			); 
		}
		return ( $author_options ) ;
	}
		
	
	public static function get_post_status_options() {
		return self::$post_status_options; 
	} 
	
	public static function post_status_formatter( $value ) {
		return WIC_Function_Utilities::value_label_lookup ( $value,  self::$post_status_options ); 
	} 
	
	public static function get_post_status_label ( $value ) {
		return self::post_status_formatter ( $value );	
	}
	
	public static function get_retrieve_limit_options() {
		return self::$retrieve_limit_options; 
	}	
	
	public static function tags_input_sanitizor ( $value ) {
		return WIC_Function_Utilities::sanitize_textcsv( $value );	
	}	*/
}