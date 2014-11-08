<?php
/*
*
* class-wp-issues-crm-base-definitions.php
*
* definitions shared across others classes
*
*
* 
*/

class WP_Issues_CRM_Base_Definitions {
	
	public function __construct() {

	}
	
	public $wic_metakey = 'wic_data_';	
	

	
	public $wic_post_types = array(
		'constituent' 	=> array ( 
		'dedicated_table'	=> 'wic_constituents',
			'post_type'	=> 'wic_constituent',
			),
		'activity' 		=> array (
			'post_type'	=> 'wic_activity',
			), 
		'issue'			=> array (
			'post_type'	=> 'post',
			),
	);
	
	public $wp_query_parameters = array(
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

$wic_base_definitions = new WP_Issues_CRM_Base_Definitions; 
