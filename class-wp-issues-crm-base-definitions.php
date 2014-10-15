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
	
	/* this array determines:
		- whether field will be handled as array for display purposes -- multi lines of same field
		- whether field will always be searched on a like compare (instead of = ), regardless of field or screen settings
		- whether will look second field at first member of array when doing dedup and required field checking (i.e., first phone, email or street address)
	*/
	
	public $wic_post_types = array(
		'constituent' 	=> 'wic_constituent',
		'activity'		=> 'wic_activity',
		'issue'			=> 'post',
	);
	
	public $serialized_field_types = array ( 
		'phones',
		'emails',
		'addresses',
	);
	
}

$wic_base_definitions = new WP_Issues_CRM_Base_Definitions; 
