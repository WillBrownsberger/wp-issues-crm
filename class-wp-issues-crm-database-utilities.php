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
		
		$post_type = $wic_base_definitions->wic_post_types[$wic_post_type];	
		
		if ( 'dup' == $search_mode || 'new' == $search_mode ) {  	
	   	$meta_query_args = array(
	     		'relation'=> 'AND',
	     	);
			$index = 1;
			$ignored_fields_list = '';

	 		foreach ( $fields_array as $field ) {
				if ( 'date' == $field['type'] && 'new' == $search_mode ) { // handle date as range in new searches 
					if ( $next_form_output[$field['slug'] . '_lo'] > '' || $next_form_output[$field['slug'] . '_hi'] > '' ) {
					array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
						if ( $next_form_output[$field['slug'] . '_lo'] > '' ) { 
							if ( ( $index - 1 ) < $this->search_terms_max )	{ 	
								$meta_query_args[$index] = array(
									'key' 	=> $wic_base_definitions->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
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
									'key' 	=> $wic_base_definitions->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
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
								'key' 	=> $wic_base_definitions->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
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
	 		}
	 		if ( $ignored_fields_list > '' ) {
	 			$next_form_output['search_notices'] .= sprintf( __( 'Note: Maximum %1$s search terms allowed to protect performance -- the search was executed, but excess search terms were ignored ( %2$s ).', 'wp-issues-crm' ), 
	 				$this->search_terms_max, $ignored_fields_list ); 
				$next_form_output['error_messages'] .= sprintf( __( 'Note: Maximum %1$s search terms allowed to protect performance -- the search was executed, but excess search terms were ignored ( %2$s ).', 'wp-issues-crm' ), 
	 				$this->search_terms_max, $ignored_fields_list ); 
	 		} 
	 		$query_args = array (
	 			'posts_per_page' => 100,
	 			'post_type' 	=>	$post_type,
	 			'meta_query' 	=> $meta_query_args, 
	 			'orderby'		=> 'title',
	 			'order'			=> 'ASC',
	 			's'				=> $next_form_output['wic_post_content'] ,
	 		);
	 		
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
   public function save_update_wic_post( &$next_form_output, $fields_array ) { 
		
		global $wic_constituent_definitions;
		global $wic_base_definitions;
		global $wic_form_utilities;

		$outcome = array (
			'post_id'	=> 0,
		   'notices'	=> '', 
		);		
		
		// for title, use group email if have it, otherwise use individual email 
		$email_for_title = '';
		if ( isset( $next_form_output['email_group'] ) ) {
			$email_for_title = isset( $next_form_output['email_group'][0][1] ) ? $next_form_output['email_group'][0][1]  : '';
		} 
		if ( '' == $email_for_title ) {
			$email_for_title = isset( $next_form_output['email'] ) ? $next_form_output['email_group']  : ''; 
		}
		
   	// title is ln OR ln,fn OR fn OR email -- one of these is required in validation to be non-blank.	
		$title = 	isset ( $next_form_output['last_name'] ) ? $next_form_output['last_name'] : '';
		$title .= 	isset ( $next_form_output['first_name'] ) ? ( $title > '' ? ', ' : '' ) . $next_form_output['first_name'] : '';
		$title =		( '' == $title ) ? $email_for_title : $title;
		
		$post_args = array(
		  'post_title'     => $title,
		  'post_status'    => 'private',
		  'post_type'      => 'wic_constituent',
		  'comment_status' => 'closed' 
		); 
		
		if ( $next_form_output['wic_post_id'] > 0 ) { // if have constituent ID, do update if notes or title changed
			$check_on_database = $this->search_wic_posts( 'db_check', $next_form_output, $fields_array, 'constituent' ); // bullet proofing and get values to see if changed
			if ( ! isset ( $check_on_database->post->ID ) )  {
				$outcome['notices'] = __( 'Unknown error. Could not find record to update', 'wp-issues-crm' );
				return ( $outcome );			
			} 
			$post_args['ID'] = $next_form_output['wic_post_id'];
/*			if ( $next_form_output[ 'wic_post_content' ] != $check_on_database->post->post_content ||
				$title != $check_on_database->post->post_title ) { -- these were replaced by next two lines lines*/
			if ( trim( $next_form_output[ 'wic_post_content' ] )   > '' || $title != $check_on_database->post->post_title ) { // conditions for update post
				if ( trim( $next_form_output[ 'wic_post_content' ] )   > '' ) { // condition for update post content
					array_push( $next_form_output['initial_sections_open'], 'wic_post_content' ); // show field's section open in next form
					$post_args['post_content'] = $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] )  . $check_on_database->post->post_content;
				}
				$outcome['post_id'] = wp_update_post( $post_args ); 
			} else {
				$post_args['post_content'] = $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] );
				$outcome['post_id'] = $next_form_output['wic_post_id'];			
			}
		} else {
			$outcome['post_id'] = wp_insert_post( $post_args );		
		}				
		// save or update error return with error
		if ( 0 == $outcome['post_id'] ) {
			$outcome['notices'] = __( 'Unknown error. Could not save/update constituent record.  Do new constituent search on same constituent to check for partial results.', 'wp-issues-crm' );
			return ($outcome);					
		}
		// otherwise proceed to update metafields
		 foreach ( $fields_array as $field ) {
		 	// note: in the not read only branch, explicitly set meta_return in every case 
		 	if ( 'readonly' != $field['type'] ) {
				// note: add/update post meta automatically serializes arrays!				
				$post_field_key =  $wic_base_definitions->wic_metakey . $field['slug'];
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
				// new constituent record
				} else { 
					if ( $next_form_output[$field['slug']] > '' ) { 
						$meta_return = add_post_meta ( $outcome['post_id'], $post_field_key, $next_form_output[$field['slug']] );
					} else { // for blank field set return to be OK (no action was taken)
						$meta_return = 1;					
					}
				}
				
				if ( ! $meta_return ) {
					$outcome['notices'] = sprintf( __( 'Unknown error. Could not save constituent detail -- %1$s.   Do new constituent search on same constituent to check for partial results.', 'wp-issues-crm' ), $field['label'] );
				}
			}	
		} 
		
		return ( $outcome );
	}	  



}

$wic_database_utilities = new WP_Issues_CRM_Database_Utilities; 
