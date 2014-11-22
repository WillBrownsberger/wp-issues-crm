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
	
	/***************************************************************************
	*
	* Constituent properties, setters and getters
	*
	****************************************************************************/ 	

	private static $comments_status_options = array (
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

	public static function get_category_options() {
		// wic_get_category_list	
	}

	public static function get_post_categories ( $post_id ) {
		$categories = get_the_category ( $post_id );
		$return_list = '';
		foreach ( $categories as $category ) {
			$return_list .= ( '' == $return_list ) ? $category->cat_name : ', ' . $category->cat_name;		
				}
		return ( $return_list ) ;	
	}	
	
	
	public static function get_issue_staff_options() {
		return wic_get_administrator_array();
	}

		
  	public static function get_follow_up_status_options() {
		return self::$follow_up_status_options; 
	} 

		
  	public static function follow_up_status_formatter( $value ) {
		return value_label_lookup ( $value,  self::$follow_up_status_options );	 
	} 


	public static function comments_status_formatter( $value ) {
		return value_label_lookup ( $value,  self::$comments_status_options );	
	} 


	public static function get_comments_status_options() {
		return self::$comments_status_options; 
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
	
	public static function get_post_status_options() {
		return self::$post_status_options; 
	} 
	
	public static function post_status_formatter( $value ) {
		return value_label_lookup ( $value,  self::$post_status_options ); 
	} 
	
	public static function get_post_status_label ( $value ) {
		return self::post_status_formatter ( $value );	
	}
	
}