<?php
/*
*
* class-wic-db-access-wp.php
*		intended as wraparound for wpdb 
*
*
* 
*/

class WIC_DB_Access_WP Extends WIC_DB_Access {

	private static $wic_metakey =  '_wic_issue_';

	protected function db_save(  $data_array ) {
		
	}
	
	protected function db_search( $meta_query_array, $search_parameters ) {
		var_dump ($meta_query_array);
		// default search parameters supplied -- these need to be added to form elements or other call if to be varied
		$select_mode 		= 'id';
		$sort_order 		= false;
		$compute_total 	= true;
		$retrieve_limit 	= '10';
		$show_deleted		= true;
		
		// extract ( $search_parameters, EXTR_OVERWRITE ); set as blank and don't want to overwrite with this, so // for now.
		
		$allowed_statuses = array(
			'publish',
			'private',
		);		
		
		$query_args = array (
 			'posts_per_page' => $retrieve_limit,
 			'post_status'	=> $allowed_statuses,
 			'ignore_sticky_posts' => true,
	 	);

	   $meta_query_args = array( // will be inserted into $query_args below
	     		'relation'=> 'AND',
	   );
	   
	   foreach ( $meta_query_array as $search_clause ) {
			switch ( $search_clause['wp_query_parameter'] ) {
				case 'p':
					$query_args = array (
						'p' => $search_clause['value'],					
					);
					break( 2 ); // exit switch and foreach with just the ID search array
									// supports call from ID search
				case '':
					$meta_query_array[] = $search_clause;
					break;
				case 'author':
				case 'tag' :
				case 'post_status':
					$query_args[$search_clause['wp_query_parameter']] = $search_clause['value'];
					break;
				case 'post_title': 
							// note -- hiding post_content as a search field by css 
						 	// on not found going to save form, the search term will come up in title
						 	// the alternative approach would be to add a column to dictionary to suppress a field on search
						 	// treating this as a css issue -- if it were shown, it would do nothing anyway, since
						 	// post_content is not in this switch list 
					$query_args['s'] = $search_clause['value'];
					break;
				case 'cat':
					$cats = array();
					foreach ( $search_clause['value'] as $cat => $value ) {
						$cats[] = $cat;				
					}
					$query_args['category__in'] = $cats;
					break;
				case 'date':
					$date_array = array();
					if ( 'BETWEEN' == $search_clause['compare'] ) {
						$date_array = array (
							array(
								'after' => $search_clause['value'][0],									
								'before' => $search_clause['value'][1],
								),
							'inclusive' => true,	
						);
					} elseif ( '<' == $search_clause['compare'] ) {
						$date_array = array (
							array(
								'before' => $search_clause['value'],
								),
							'inclusive' => true,	
						);
					} elseif ( '>' == $search_clause['compare'] ) {
						$date_array = array (
							array(
								'after' => $search_clause['value'],
								),
							'inclusive' => true,	
						);
												
					}
					$query_args['date_query'] = $date_array;
					break;
			}	// end switch   
	   } // end foreach  	
		
		if ( count ( $meta_query_args ) > 1 && ! isset ( $query_args['p'] ) ) { // if have ID, go only for that
			$query_args['meta_query'] 	= $meta_query_args;
		}
	
		$wp_query = new WP_Query($query_args);

		$this->sql = ' ' . __(' ( serialized WP_Query->query output ) ', 'wp-issues-crm' ) . serialize ( $wp_query->query );
		$this->result = $wp_query->posts;
	 	$this->showing_count = $wp_query->post_count;
		// only do sql_calc_found_rows on id searches; in other searches, found count will always equal showing count
		$this->found_count = $wp_query->found_posts;
		// set value to say whether found_count is known
		$this->found_count_real = $compute_total;
		$this->retrieve_limit = $retrieve_limit;
		$this->outcome = true;  // wpdb get_results does not return errors for searches, so assume zero return is just a none found condition (not an error)
										// codex.wordpress.org/Class_Reference/wpdb#SELECT_Generic_Results 
		$this->explanation = ''; 

 	}

	protected function db_update ($doa){
	}

	protected function db_delete_by_id ($id){
	}
	
/*<?php
	 	} elseif ( 'db_check' == $search_mode ) { 
			$query_args = array (
				'p' => $next_form_output['constituent_id'],
				'post_type' => 'wic_constituent',			
			);	 	
	 	} 

 		$wic_query = new WP_Query($query_args);
 
 		return $wic_query;
	}

*/	
	
			     	
	protected  $wp_query_parameters = array(
		'author' 	=> array ( 
			'update_post_parameter'	=> 'post_author',
			),
		'cat' 	=> array ( 
			'update_post_parameter'	=> 'post_category',
			),
		'date' 	=> array ( 
			'update_post_parameter'	=> 'post_date',
			),
		's' 	=> array ( 
			'update_post_parameter'	=> '',
			),
		'tag' 	=> array ( 
			'update_post_parameter'	=> 'post_tags',
			),
		'post_status' 	=> array ( 
			'update_post_parameter'	=> 'post_status',
			),
		'post_title' 	=> array ( 
			'update_post_parameter'	=> 'post_title',
			),			
	);
	
}


