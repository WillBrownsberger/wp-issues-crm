<?php
/*
*
* class-wic-db-access.php
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


abstract class WIC_DB_Access {
	
	// these properties contain  the results of the db access
	public $entity;		// top level entity searched for or acted on ( e.g., constituents or issues )
	public $sql; 			// for search, the query executed;
	public $result; 		// entity_object_array -- as saved, update or found( possibly multiple ) (each row as object with field_names properties)
	public $outcome; 		// true or false if error
	public $explanation; // reason for outcome
	public $found_count; // integer save/update/search # records found or acted on
	public $insert_id;	// ID of newly saved entity


	public function __construct ( $entity ) { 
		$this->entity = $entity;
	}		

	/*
	*	publicly callable methods that will return results -- 
	*		these methods do not alter the object array as it exists in the object calling them
	*
	*/

	public function search ( $meta_query_array  ) { // receives pre-assembled meta_query_array
		$this->db_search( $meta_query_array );
		return;
	}


	public function save_update ( $doa ) {
		$save_update_array = $this->assemble_save_update_array( $doa );
		if ( $doa['ID']->get_value() > 0 ) {
			$this->db_update ( $save_update_array );		
		} else {
			$this->db_save ( $save_update_array );
		}	
		return;	
	}


	/*
	*
	*	Assemble save_update array from controls.
	*
	*/
	protected function assemble_save_update_array ( &$doa ) {
		$save_update_array = array(
			'set_array'	=> array(),
			'direct_sql_statement' => array(),
		);
		foreach ( $doa as $field => $control ) {
			$update_clauses = $control->create_update_clauses();
			$save_update_array['set_array'][] = $update_clauses['set_array'];
			$save_update_array['direct_sql_statement'][] = $update_clauses['direct_sql_statement'];
		}	
		return ( $save_update_array );
	}

	abstract protected function db_search ( $meta_query_array );
	abstract protected function db_save ( $meta_query_array );
	abstract protected function db_update ( $meta_query_array );
	
}


