<?php
/*
*
* class-wic-db-access-wic.php
*		intended as wraparound for wpdb to access wic tables
*
*/

class WIC_DB_Access_Dictionary Extends WIC_DB_Access_WIC {
	
	public $field_slug;

	// function adds a field to constituent table and then feeds field_name to data dictionary save array
	protected function external_update ( $set ) {

		global $wpdb;	
		
		// check used custom field names on constituent table
		$table1 = $wpdb->prefix . 'wic_constituent';
		$table_rows = $wpdb->get_results (
			"
			SELECT * FROM $table1 WHERE 1
			LIMIT 0, 1		
			", ARRAY_A
		);	
		$column_names = array_keys ( $table_rows[0] );
		$filtered_column_names = array_filter ( $column_names, array ( $this, 'filter_custom_columns' ) );
		
		// determine new field name 
		if ( count ( $filtered_column_names ) > 0 ) {
			rsort ( $filtered_column_names );
			$last_custom = (int) substr( $filtered_column_names[0], 13, 3);
			$new_field_name = 'custom_field_' . sprintf("%03d", $last_custom + 1 );
		 } else {
			$new_field_name = 'custom_field_001';		 
		 }

		// set property to be picked up in wic-entity-data-dictionary->special_entity_value_hook
		$this->field_slug = $new_field_name;
		// update database
		$wpdb->query ( 
			"
			ALTER TABLE $table1 ADD $new_field_name VARCHAR( 255 ) NOT NULL ,
			ADD INDEX ( $new_field_name ) ;
			"		
			);
		// die on error		
		if ( $wpdb->last_error > '' ) {
			die ( '<h3>' .  sprintf( __( 'MySQL could not add custom field. Error reported was: %s', 'wp-issues-crm' ), $wpdb->last_error ) . '</h3>' );
		// other wise proceed to do set up save to data dictionary		
		} else {		
			// add to set clause with placeholders
			$set['set_clause_with_placeholders'] .= ', field_slug = %s, entity_slug = %s, customizable = %d';
			// add to set value array		
			array_push ( $set['set_value_array'], $new_field_name );	
			array_push ( $set['set_value_array'], 'constituent' );
			array_push ( $set['set_value_array'], 1 );			
			return ( $set );
		}	
	}	

	private function filter_custom_columns ($value) {
		return ( 'custom_field_' == substr ( $value, 0, 13 ) ); 
	}	
	
}

