<?php
/*
*
* class-wic-db-setup.php
*		accesses sql to  
*/



class WIC_DB_Setup {

	/*********************************************************************************************
	*	
	* 	this function takes sql exported from development environment  
	*	prepares it for production use with appropriate site variables
	*	runs dbdelta to install it
	*
	*	manual steps in package preparation of export from development
	*		set without autoincrement settings, not exists and back ticks
	* 		put two spaces between the words PRIMARY KEY and the definition of your primary key (matters on update compares, not on table creation).
	*		check field names all safe as is
	*
	*  current database version must be coded both here and in database_update_check
	*
	*********************************************************************************************/
	public static function database_setup() { 
		
		global $wpdb;		
		
		// load the table set up sql 
		$sql = file_get_contents( plugin_dir_path ( __FILE__ ) . 'wic_structures.sql' );
		
		$wp_issues_crm_db_version = '1.6';

		// set up name conversion table		
		$table_name_array = array (
			array ( 'wp_wic_activity'		, 	$wpdb->prefix . 'wic_activity' 	),
			array ( 'wp_wic_address'		, 	$wpdb->prefix . 'wic_address'		),
			array ( 'wp_wic_constituent'	, 	$wpdb->prefix . 'wic_constituent'		),
			array ( 'wp_wic_constituent'	,	$wpdb->prefix . 'wic_constituent'		),
			array ( 'wp_wic_email'			, 	$wpdb->prefix . 'wic_email'		),
			array ( 'wp_wic_form_field_groups',$wpdb->prefix . 'wic_form_field_groups'		),
			array ( 'wp_wic_option_group'	,	$wpdb->prefix . 'wic_option_group'		),
			array ( 'wp_wic_option_value'	,	$wpdb->prefix . 'wic_option_value'		),
			array ( 'wp_wic_phone'			,	$wpdb->prefix . 'wic_phone'		),
			array ( 'wp_wic_search_log'	,	$wpdb->prefix . 'wic_search_log'		),
		);
		
		// convert table names from development to production
		foreach ( $table_name_array as $table_name ) {
			$sql = str_replace ( $table_name[0], $table_name[1], $sql );		
		}
	
		// get character set and replace in SQL (if necessary)
		$charset_collate = $wpdb->get_charset_collate();
		$sql = str_replace ( 'DEFAULT CHARSET=latin1', $charset_collate, $sql );

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		echo 'before>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>';
		// dbdelta performs unpredictably with multiple sql statements regardless of ; as termination
/*		$sql_tables_array = explode ( 'CREATE TABLE', $sql );
		foreach ( $sql_tables_array as $table_create )  {
			dbDelta( 'CREATE TABLE' . $table_create );
		} */
		$result = dbDelta( $sql, true );
		var_dump ($result );
		// add_option( 'wp_issues_crm_db_version', $wp_issues_crm_db_version );
		echo '<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<after'; // die;
	}
	
	public static function update_db_check () {
		/*
		* hard code current version # here and in install
		*/
		$installed_version = get_option( 'wp_issues_crm_db_version');
//		if ( '1.6' != $installed_version ) {
			self::database_setup();		
//		}
	}	
	

	public static function data_dictionary_load() {
	// figure out how to load sql directly from a file
	
	// load that into a variable
	
	// send it to dbdelta	
	
	}

	
}


