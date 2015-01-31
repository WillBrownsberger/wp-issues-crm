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

class WIC_Entity_Issue extends WIC_Entity_Parent {

	protected function set_entity_parms( $args ) { // 
		// accepts args to comply with abstract function definition, but as a top level entity does not process them -- no instance arg
		$this->entity = 'issue';
	} 

	// handle a request for a new search form
	protected function new_form() { 
		$this->new_form_generic( 'WIC_Form_Issue_Search' );
		return;
	}
	
	// handle a request for a blank new issue form
	protected function new_blank_form() {
		$this->new_form_generic ( 'WIC_Form_Issue_Save', __( 'Create a new issue and save.', 'wp-issues-crm' ) );	
	}

	// handle a search request coming from a search form
	protected function form_search () { 
		$this->form_search_generic ( 'WIC_Form_Issue_Search_Again', 'WIC_Form_Issue_Update');
		return;				
	}

	// show a constituent save form using values from a completed search form (search again)
	protected function save_from_search_request() { 
		parent::save_from_search ( 'WIC_Form_Issue_Save',  $message = '', $message_level = 'good_news', $sql = '' );	
	}	
	
	// handle a search request for an ID coming from anywhere
	protected function id_search ( $args ) {
		$id = $args['id_requested']; 
		$this->id_search_generic ( $id, 'WIC_Form_Issue_Update' );
		return;		
	}

	//handle an update request coming from an update form
	protected function form_update () {
		$this->form_save_update_generic ( false, 'WIC_Form_Issue_Update', 'WIC_Form_Issue_Update' );
		return;
	}
	
	//handle a save request coming from a save form
	protected function form_save () {
		$this->form_save_update_generic ( true, 'WIC_Form_Issue_Save', 'WIC_Form_Issue_Update' );
		return;
	}

	//handle a search request coming search log
	protected function redo_search_from_query ( $meta_query_array ) {
		$this->redo_search_from_meta_query ( $meta_query_array, 'WIC_Form_Issue_Save', 'WIC_Form_Issue_Update' );
		return;
	}	
	
	// handle a request to return to a search form
	protected function redo_search_form_from_query ( $search ) { 
		$this->search_form_from_search_array ( 'WIC_Form_Issue_Search',  __( 'Redo search.', 'wp-issues-crm'), $search );
		return;
	}	
	
	
	protected function special_entity_value_hook ( &$wic_access_object ) {
		$control = $this->data_object_array['post_date'];
		$post_date = $control->get_value();
		if ( '' == $post_date ) { 
			// these values specially prepared for this hook by wic-db-access-wic->process_save_update_array
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
	
	public function get_the_title () {
		return ( $this->data_object_array['post_title']->get_value() );	
	}	
	
	
	/***************************************************************************
	*
	* Functions related to issue properties
	*
	****************************************************************************/ 	

	// this is special purpose option array generator -- uses recursive function below
	public static function get_post_category_options() {
		global $wic_category_select_array;
		global $wic_category_array_depth;
		$wic_category_select_array = array();
		$wic_category_array_depth = 0;
		return ( self::wic_get_category_list(0) );
	} 		


	// this is recursive to traverse the category list -- initiatied by get_post_category_options
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

	// for issue list: retrieve the values as well as formatting
	public static function get_post_categories ( $post_id ) {
		$categories = get_the_category ( $post_id );
		$return_list = '';
		foreach ( $categories as $category ) {
			$return_list .= ( '' == $return_list ) ? $category->cat_name : ', ' . $category->cat_name;		
				}
		return ( $return_list ) ;	
	}	
	
	// for issue list: look up assigned user's display name 
	public static function issue_staff_formatter ( $user_id ) {
		
		$display_name = '';		
		if ( isset ( $user_id ) ) { 
			if ( $user_id > 0 ) {
				$user =  get_users( array( 'fields' => array( 'display_name' ), 'include' => array ( $user_id ) ) );
				$display_name = esc_attr( $user[0]->display_name ); // best to generate an error here if this is not set on non-zero user_id
			}
		}
		return ( $display_name );
	}

	// for author search, author drop down 
	public static function get_post_author_options () {
	
		global $wpdb;
		
		$query_args = array(
			'orderby' => 'name',
			'order' => 'ASC',
			'number' => '',
			'fields' => array ( 'ID' , 'display_name' ),
		);
		$authors = get_users( $query_args );
		
		$author_options = array(
			array (
				'value' => '',
				'label' => '',			
			)		
		);
		foreach ( $authors as $author ) {
			$author_options[] = array (
				'value' => $author->ID,
				'label' => $author->display_name,			
			); 
		}
		return ( $author_options ) ;
	}
	
	// for tag input -- sanitize to csv
	public static function tags_input_sanitizor ( $value ) {
		return WIC_Function_Utilities::sanitize_textcsv( $value );	
	}	
}