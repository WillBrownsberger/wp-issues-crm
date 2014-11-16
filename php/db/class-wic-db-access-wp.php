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

	protected function db_save(  $data_array ) {
		
	}
	
	protected function db_search( $data_array ) {

	}
	
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


