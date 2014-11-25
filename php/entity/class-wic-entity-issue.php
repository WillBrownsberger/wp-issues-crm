<?php
/*
*
*	wic-entity-issue.php
*
*  This class is instantiated and takes control from the parent class in the parent constructor
*  It takes action on user requests which are the named functions.
*  It receives the $args passed from the button ( via WP_Issues_CRM and the parent )  
*		BUT only $arg actually used is in the ID requested function.
*	It is able to use generic functions from the parent.
*
*/

class WIC_Entity_Issue extends WIC_Entity_Parent {

	protected function set_entity_parms( $args ) { // 
		// accepts args to comply with abstract function definition, but as a parent does not process them -- no instance
		$this->entity = 'issue';
	} 

	// handle a request for a new standard form
	protected function new_form() { 
		$this->new_form_generic( 'WIC_Form_Issue_Search' );
		return;
	}

	// handle a search request coming from a standard form
	protected function form_search () { 
		$this->form_search_generic ( 'WIC_Form_Issue_Save', 'WIC_Form_Issue_Update');
		return;				
	}
	
	// handle a search request for an ID coming from anywhere
	protected function id_search ( $args ) {
		$id = $args['id_requested']; 
		$this->id_search_generic ( $id, 'WIC_Form_Issue_Update' );
		return;		
	}

	//handle an update request coming from a standard form
	protected function form_update () {
		$this->form_save_update_generic ( false, 'WIC_Form_Issue_Update', 'WIC_Form_Issue_Update' );
		return;
	}
	
	//handle a save request coming from a standard form
	protected function form_save () {
		$this->form_save_update_generic ( true, 'WIC_Form_Issue_Save', 'WIC_Form_Issue_Update' );
		return;
	}
	
	protected function special_entity_value_hook ( &$wic_access_object ) {
		$this->data_object_array['post_author']->set_value( $wic_access_object->post_author );
		$this->data_object_array['post_date']->set_value( $wic_access_object->post_date );
		$this->data_object_array['post_status']->set_value( $wic_access_object->post_status );		
	}	
	
	
	/***************************************************************************
	*
	* Constituent properties, setters and getters
	*
	****************************************************************************/ 	

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
	);	

	
	/*
	*	option array get functions
	*/

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
	

	public static function wic_live_issue_formatter( $value ) {
		return wic_value_label_lookup ( $value,  self::$wic_live_issue_options );	
	} 


	public static function get_wic_live_issue_options() {
		return self::$wic_live_issue_options; 
	} 

	
	public static function get_issue_staff_options() {
		return wic_get_administrator_array();
	}

		
  	public static function get_follow_up_status_options() {
		return self::$follow_up_status_options; 
	} 

		
  	public static function follow_up_status_formatter( $value ) {
		return wic_value_label_lookup ( $value,  self::$follow_up_status_options );	 
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
		return wic_value_label_lookup ( $value,  self::$post_status_options ); 
	} 
	
	public static function get_post_status_label ( $value ) {
		return self::post_status_formatter ( $value );	
	}
	
	public static function tags_sanitizor ( $value ) {
		return wic_sanitize_textcsv( $value );	
	}	
}