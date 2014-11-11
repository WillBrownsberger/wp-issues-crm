<?php
/*
*
* class-wic-db-access-factory.php
*		intended as wraparound for wpdb 
*
* supports multiple formats for data access to be further implemented in subclasses
*		WIC_Dedicated_Table_Access
*		WIC_WP_Post_Access
*
* note, that as for wpdb and other wordpress object, this object includes all necessary pre-database sanitization and validation
*
* 
*/

class WIC_DB_Access_Factory {

	static private $entity_model_array = array (
		'constituent' => 'WIC_DB_Access_WIC',	
		'activity' => 'WIC_DB_Access_WIC',
		'issue' => 'WIC_DB_Access_WP',
	);

	public static function make_a_db_access_object ( $entity ) {
		$right_db_class = self::$entity_model_array[$entity];
		$new_db_access_object = new $right_db_class ( $entity );
		return ( $new_db_access_object );	
	}
	
	
}
