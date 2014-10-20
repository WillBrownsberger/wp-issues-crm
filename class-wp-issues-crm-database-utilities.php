<?php
/*
*
* class-wp-issues-crm-database-utilities.php
*
* database access functions shared across others classes
*
*
* 
*/

class WP_Issues_CRM_Database_Utilities {
	
	public function __construct() {
		// get label for 
		global $wic_base_definitions;
		
		$this->wic_metakey = $wic_base_definitions->wic_metakey;
		
	}
	
	/*
	*  search_wic_posts
	*	does search based on passed array of form fields in one of three search modes:
	*		new -- searches based on all meta fields (this value is passed only when user request = 'search')
	*		db_check	-- searches based on wic_post_id
	*		dup -- searches based on only dup_check metafields	(this value passed only when user request = 'save' or 'update' )
	*
	*  note -- trusts Wordpress to escape strings for query -- they have had slashes and tags stripped in input validation, 
	*  but might have quotes & reserved words
	*/	
	
	public $search_terms_max = 5; // don't allow searches that will likely degrade performance 	
	
   public function search_wic_posts( $search_mode, &$next_form_output, $fields_array, $wic_post_type  ) {
		
		global $wic_base_definitions;	
		global ${ 'wic_' . $wic_post_type . '_definitions' };		
		
		$post_type = $wic_base_definitions->wic_post_types[$wic_post_type]['post_type'];	
		
		if ( 'dup' == $search_mode || 'new' == $search_mode ) {  	

	 		$query_args = array (
	 			'posts_per_page' => 100,
	 			'post_type' 	=>	$post_type,
	 			'orderby'		=> ${ 'wic_' . $wic_post_type . '_definitions' }->wic_post_type_sort_order['orderby'],
	 			'order'			=> ${ 'wic_' . $wic_post_type . '_definitions' }->wic_post_type_sort_order['order'],
	 			's'				=> $next_form_output['wic_post_content'] ,
	 		);

	   	$meta_query_args = array( // will be inserted into $query_args below
	     		'relation'=> 'AND',
	     	);
			$index = 1;
			$ignored_fields_list = '';			

	 		foreach ( $fields_array as $field ) {
	 			$wp_query_parameter = isset ( $field['wp_query_parameter'] ) ? $field['wp_query_parameter'] : ''; 
	 			if ( '' == $wp_query_parameter ) {
					if ( 'date' == $field['type'] && 'new' == $search_mode ) { // handle date as range in new searches 
						if ( $next_form_output[$field['slug'] . '_lo'] > '' || $next_form_output[$field['slug'] . '_hi'] > '' ) {
						array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
							if ( $next_form_output[$field['slug'] . '_lo'] > '' ) { 
								if ( ( $index - 1 ) < $this->search_terms_max )	{ 	
									$meta_query_args[$index] = array(
										'key' 	=> $this->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
										'value'		=> $next_form_output[$field['slug'] . '_lo'],
										'compare'	=>	'>=',
									);	
								} else { 
									$ignored_fields_list = ( $ignored_fields_list == '' ) ? $field['label'] . ' (low) '  : ( $ignored_fields_list .= ', ' . $field['label'] . ' (low) ' ); 
								}
								$index++;
							}	
							if ( $next_form_output[$field['slug'] . '_hi'] > '' ) {
								if ( ( $index - 1 ) < $this->search_terms_max )	{		
									$meta_query_args[$index] = array(
										'key' 	=> $this->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
										'value'		=> $next_form_output[$field['slug'] . '_hi'],
										'compare'	=>	'<=',
									);	
								} else { 
									$ignored_fields_list = ( $ignored_fields_list == '' ) ? $field['label'] . ' (high) ' : ( $ignored_fields_list .= ', ' . $field['label'] . ' (high) ' ); 
								}
								$index++;
							}					
						}	
					} else { // standard = or like handling (including for dates in dedup mode)
			 			if( $next_form_output[$field['slug']] > '' && ( 'new' == $search_mode  || $field['dedup'] ) )  { 
			 				array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
							if ( ( ( $index - 1 ) < $this->search_terms_max ) || $field['dedup'] )	{ // allow possibility to set more dedup fields than allowed search fields		
								if ( is_array( $next_form_output[$field['slug']] ) ) { // happens only for phone, email, street address; regardless of next action, have to flatten for search
									$meta_value = $next_form_output[$field['slug']][0][1]; // the first, phone, email or street address
								} else {
									$meta_value = $next_form_output[$field['slug']];
								}
								$meta_query_args[$index] = array(
									'key' 	=> $this->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
									'value'		=> $meta_value,
									'compare'	=>	(  // do strict match in dedup mode
															( $field['like'] && ! $next_form_output['strict_match'] && 'new' == $search_mode ) ||
															in_array( $field['type'], $wic_base_definitions->serialized_field_types ) 
														) ? 'LIKE' : '=' ,
								);	
							} else { 
								$ignored_fields_list = ( $ignored_fields_list == '' ) ? $field['label'] : ( $ignored_fields_list .= ', ' . $field['label'] ); 
							}
							$index++;
						}	
					}
				} else { 
					switch ( $field['wp_query_parameter'] ) {
						case 'author':
						case 's' :
							$query_args[$field['wp_query_parameter']] = $next_form_output[$field['slug']];
							break;
						case 'cat':
							$query_args['category__in'] = $next_form_output[$field['slug']];
							break;
						case 'post_status':
							$status_value = ( $next_form_output[$field['slug']] > '' ) ? $next_form_output[$field['slug']] : 'any';
							$query_args[$field['wp_query_parameter']] = $status_value;
							break;
						case 'date':
							$query_args['date_query'] = array(
								array(
									'after'     => $next_form_output[$field['slug'] . '_lo'],
									'before'    => $next_form_output[$field['slug'] . '_hi'],
									'inclusive' => true,
									),
								);							
							break;
						case 'tag': 
							$query_args['tag_slug__in'] = $next_form_output[$field['slug']];
							break;
					   // note: not presenting title as a search option -- captured in full text search; 
					}
				}		
	 		}
	 	
			$query_args['meta_query'] 	= $meta_query_args; 
	 	
	 		if ( $ignored_fields_list > '' ) {
	 			$next_form_output['search_notices'] .= sprintf( __( 'Note: Maximum %1$s search terms allowed to protect performance -- the search was executed, but excess search terms were ignored ( %2$s ).', 'wp-issues-crm' ), 
	 				$this->search_terms_max, $ignored_fields_list ); 
				$next_form_output['error_messages'] .= sprintf( __( 'Note: Maximum %1$s search terms allowed to protect performance -- the search was executed, but excess search terms were ignored ( %2$s ).', 'wp-issues-crm' ), 
	 				$this->search_terms_max, $ignored_fields_list ); 
	 		} 
	
	 		if ( $next_form_output['wic_post_content']  > '' ) {
				array_push( $next_form_output['initial_sections_open'], 'wic-post-content' ); // show field's section open in next form
			}	 		
	 		
	 	} elseif ( 'db_check' == $search_mode ) { 
			$query_args = array (
				'p' => $next_form_output['wic_post_id'],
				'post_type' => $post_type,			
			);	 	
	 	} 
		
 		$wic_query = new WP_Query($query_args);
		
 		return $wic_query;
	}


   /*
   *
	*  save_update_wic_post
	*
	*  does save or update based on next form input ( update if wic_post_id is populated with value > 0 ) 
	*	
	*  note: here do serialization (and on extraction, so could change db interface for repeating fields with change here and in update/populate)
	*  serialization is built into save meta, so no actual change in this code to reflect array handling
	*
	*	note: trusting wordpress for data escaping on save -- no validation of post_content, except on display -- see comments in display form
	*/
   public function save_update_wic_post( &$next_form_output, $fields_array, $wic_post_type  ) { 

		global $wic_form_utilities;
		global $wic_base_definitions;
		global ${ 'wic_' . $wic_post_type . '_definitions' };	

		$outcome = array (
			'post_id'	=> 0,
		   'notices'	=> '', 
		);		
		
		$title =  ${ 'wic_' . $wic_post_type . '_definitions' }->title_callback( $next_form_output );		
		
		$post_type = $wic_base_definitions->wic_post_types[$wic_post_type]['post_type'];			
		
		$post_args = array(
		  'post_title'     => $title,
		  'post_type'      => $post_type,
		); 

		foreach ( $fields_array as $field ) {
		 	// setting the wp post fields
		 	$wp_query_parameter = isset ( $field['wp_query_parameter'] ) ? $field['wp_query_parameter'] : '';
		 	if ( '' < $wp_query_parameter && $field['type'] != 'readonly' && '' != $next_form_output[$field['slug']] ) {
		 		switch ( $field['wp_query_parameter'] ) {
					case 'cat':
						$post_args['post_category'] = $next_form_output[$field['slug']];
						break;
/*					case 'tag':
						$temp_tags =  preg_replace( "/[^0-9][^A-Z][^a-z]/,", '', $next_form_output[$field['slug']] );
						$post_args['tags_input'] = explode ( ',', $temp_tags );
						break; */
					case 'post_title':	
						$post_args['post_title'] = $title;
						break;						
					// note, as a policy decision, the following fields are treated as always readonly and ignored on update:
					// author, post_date, post_status -- these can be updated through the backend, but no good reason to update here
					// ( on save, use wp defaults )
				}
		 	} 
		} 	
		 	
		if ( $next_form_output['wic_post_id'] > 0 ) { // if have post ID, do update if notes or title changed
			$check_on_database = $this->search_wic_posts( 'db_check', $next_form_output, $fields_array, $wic_post_type  ); // bullet proofing and get values to see if changed
			if ( ! isset ( $check_on_database->post->ID ) )  {
				$outcome['notices'] = __( 'Unknown error. Could not find record to update', 'wp-issues-crm' );
				return ( $outcome );			
			} 
			$post_args['ID'] = $next_form_output['wic_post_id'];
			
			if ( 	trim( $next_form_output[ 'wic_post_content' ] )   > '' || 
					$post_args['post_title']      != $check_on_database->post->post_title  || 
					$post_args['post_category'] 	!= $check_on_database->post->post_category /*||
					$post_args['tags_input']		!= $check_on_database->post->post_tags */	)
					{ // conditions for update post
				if ( trim( $next_form_output[ 'wic_post_content' ] )   > '' ) { // condition for update post content
					array_push( $next_form_output['initial_sections_open'], 'wic_post_content' ); // show field's section open in next form
					$post_args['post_content'] = $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] )  . $check_on_database->post->post_content;
				}
				$outcome['post_id'] = wp_update_post( $post_args ); 
			} else { // this is the no update branch -- just maintaining the form
				// $post_args['post_content'] = $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] );
				$outcome['post_id'] = $next_form_output['wic_post_id'];			
			}
		} else {
			/* note, let wp set author and date to defaults; but default all posts created through this interface to status private */
			$post_args['post_status']  = 'private';
			$post_args['post_content'] = $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] );
			$outcome['post_id'] = wp_insert_post( $post_args );
		}				
		// save or update error return with error
		if ( 0 == $outcome['post_id'] ) {
			$outcome['notices'] = sprintf( __( 'Unknown error. Could not save/update %1$s record.  
				Do new %1$s search on same %1$s to check for partial results.', 'wp-issues-crm' ),
				${ 'wic_' . $wic_post_type . '_definitions' }->wic_post_type_labels['singular'] );
			return ($outcome);					
		}
		// otherwise proceed to update metafields
		 foreach ( $fields_array as $field ) {
		 	// note: in the not read only branch, explicitly set meta_return in every case 
		 	$wp_query_parameter = isset ( $field['wp_query_parameter'] ) ? $field['wp_query_parameter'] : '';
		 	if ( 'readonly' != $field['type'] && '' == $wp_query_parameter ) { // don't want to update readonly and have to handle non-meta fields above
				// note: add/update post meta automatically serializes arrays!				
				$post_field_key =  $this->wic_metakey . $field['slug'];
				// first handle existing post meta records already
				if ( $next_form_output['wic_post_id'] > 0 ) { 
					if ( $next_form_output[$field['slug']] > '' ) { 
						if ( isset ( $check_on_database->post->$post_field_key ) ) {
							if( $next_form_output[$field['slug']] != $check_on_database->post->$post_field_key ) {
								array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
								$meta_return = update_post_meta ( $next_form_output['wic_post_id'], $post_field_key, $next_form_output[$field['slug']] );
							} else {
								$meta_return = 1; // no action if field value already on db correctly
							} 
						} else { // no value yet on database 
							array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
							$meta_return = add_post_meta ( $next_form_output['wic_post_id'], $post_field_key, $next_form_output[$field['slug']] );							
						}
					} else { // have empty field value
						if ( isset ( $check_on_database->post->$post_field_key ) ) {
							array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
							$meta_return = delete_post_meta( $next_form_output['wic_post_id'], $post_field_key );					
						} else {
							$meta_return = 1; // no action of field is blank and meta record not exist					
						}
						
					}
				// new record
				} else { 
					if ( $next_form_output[$field['slug']] > '' ) { 
						$meta_return = add_post_meta ( $outcome['post_id'], $post_field_key, $next_form_output[$field['slug']] );
					} else { // for blank field set return to be OK (no action was taken)
						$meta_return = 1;					
					}
				}
				
				if ( ! $meta_return ) {
					$outcome['notices'] = sprintf( __( 'Unknown error. Could not save %2$s detail -- %1$s.   Do new %2$s search 
						on same %2$s to check for partial results.', 'wp-issues-crm' ),
					  	$field['label'], ${ 'wic_' . $wic_post_type . '_definitions' }->wic_post_type_labels['singular'] );
				}
			}	
		} 
		
		return ( $outcome );
	}	  


	/*
	* populate form from database
	*
	*/
	public function populate_form_from_database ( &$next_form_output, &$fields_array, &$wic_query ) {	
	   
		foreach ( $fields_array as $field ) {
			$wp_query_parameter = isset ( $field['wp_query_parameter'] ) ? $field['wp_query_parameter'] : '';
			if ( '' == $wp_query_parameter ) {
				$post_field_key =  $this->wic_metakey . $field['slug'];
				// the following isset check should be necessary only if a search requesting more than the maximum search terms is executed 
				// note -- don't need to unserialize phones, etc. -- wp_query does this. also automatic in save_update_wic_post  
				$next_form_output[$field['slug']] = isset ( $wic_query->post->$post_field_key ) ?  $wic_query->post->$post_field_key : '';
			} else {
				switch ( $wp_query_parameter ) {
					case 'author':
						$next_form_output[$field['slug']] = $wic_query->post->post_author;
						break;
					case 'cat':
						$next_form_output[$field['slug']] =  $this->wic_get_post_categories_array ( $wic_query->post->ID );
						break;
					case 'post_status':
						$next_form_output[$field['slug']] =  $wic_query->post->post_status;
						break;
					case 'date':
						$next_form_output[$field['slug']] =  $wic_query->post->post_date;
						break;
					case 'tag':
						$next_form_output[$field['slug']] =  $wic_query->post->tags_input;
						break;
					case 'post_title':	
						$next_form_output[$field['slug']] =  $wic_query->post->post_title;
						break;						
				}	
			}
		}
		$next_form_output['wic_post_content'] = ''; // don't want to bring search notes automatically into update mode 
		$next_form_output['old_wic_post_content'] = isset ( $wic_query->post->post_content )  ? $wic_query->post->post_content: '';	
		$next_form_output['wic_post_id'] 	= $wic_query->post->ID;			
	}

	public function get_children_lists ( $wic_post_id, $wic_post_type, $child_types ) {
		
		// note, could reconstruct type and child_types with wic_post id, but given calling context, no apparent need to reinvent the wheel		
		global $wic_base_definitions;

		$children_lists = array();
		
		foreach ( $child_types as $child_type ) {

			$child_list = array();

			global ${ 'wic_' . $child_type . '_definitions' };		
			$working_list_fields = array();		
			foreach ( ${ 'wic_' . $child_type . '_definitions' }->wic_post_fields as $field ) {
	 			if ( 'parent' == $field['type'] ) {
					$parent_pointer_slug = $field['slug']; // should be only one if db properly configured
				}
 			}

			$meta_query_args = array(
				array(
					'key'     => $this->wic_metakey . $parent_pointer_slug,
					'value'   => $wic_post_id,
					'compare' => '=',
				)
			);

			$post_type = $wic_base_definitions->wic_post_types[$child_type]['post_type'];

			$list_query_args = array (
	 			'posts_per_page' => 100,
	 			'post_type' 	=>	$post_type,
	 			'meta_query' 	=> $meta_query_args, 
	 			'order'			=> 'ASC',
	 		);
	 					
			$list_query = new WP_Query ( $list_query_args );
	
			$child_list = array(
				'list_query' 	=> $list_query,
				'fields_array' => ${ 'wic_' . $child_type . '_definitions' }->wic_post_fields,
				'child_type'	=> $child_type, 
			);
			
			$children_lists[] = $child_list;
		}
		
		$children_lists = ( count( $children_lists ) > 0 ) ? $children_lists : false;		
		
		return (	$children_lists );			
			
	}

	public function get_open_issues() {
			
		$meta_query_args = array(
			'relation' => 'AND',
			array(
				'meta_key'     => 'wic_live_issues',
				'value'   => 'open',
				'compare' => '=',
			)
		);
		
		$list_query_args = array (
			'ignore_sticky_posts'	=> true,
			'post_type'		=>	'post',
 			'posts_per_page' => 100,
 			'meta_query' 	=> $meta_query_args, 
 			'order'			=> 'DESC',
	 		);	
	 		
	 	$open_posts = new WP_Query ( $list_query_args );
	 	
	 	return ( $open_posts );	
		
	}
	
	public function wic_get_post ( $post_id ) {
		$wic_post_query = new WP_Query ( 'p=' . $post_id );		
		return $wic_post_query;	
	}

	public function wic_get_post_categories_array ( $post_id ) {

		$categories = get_the_category ( $post_id );
		$return_array = array();
		foreach ( $categories as $category ) {
			$return_array[] = $category->cat_ID; 		
		}
		return ( $return_array ) ;	
	}



}

$wic_database_utilities = new WP_Issues_CRM_Database_Utilities; 
