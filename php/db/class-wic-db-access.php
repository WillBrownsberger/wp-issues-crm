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

	public function delete_by_id ( $id ) {
		$this->db_delete_by_id ( $id );	
	}


	public function save_update ( $doa ) {
		$save_update_array = $this->assemble_save_update_array( $doa );
		if ( count ( $save_update_array ) > 0 ) {
			if ( $doa['ID']->get_value() > 0 ) {
				$this->db_update ( $save_update_array );		
			} else {
				$this->db_save ( $save_update_array );
			}	
		} 
		$no_multivalue_updates = true;
		if ( $this->outcome ) { // if main update OK, do multivalue ( child ) updates
			$id =  ( $doa['ID']->get_value() > 0 ) ? $doa['ID']->get_value() : $this->insert_id;
			$errors = '';			
			foreach ( $doa as $field => $control ) {
				if ( $control->is_multivalue() ) {
					$errors .= $control->do_save_updates( $id );
					$no_multivalue_updates = false;
				}			
			}
			if ( $errors > '' ) {
				$this->outcome = false;
				$this->explanation .= $errors;
			}		
		}
		if ( ( 0 == count ( $save_update_array ) ) && $no_multivalue_updates ) {		
			$this->results = '';
			$this->sql = '';
			$this->outcome = false;
			$this->explanation = __( 'No data received for update.  May have been deleted in sanitization.', 'wp-issues-crm' );
			$this->insert_id = 0;
		}
		return;	
	}


	/*
	*
	*	Assemble save_update array from controls.
	*
	*/
	protected function assemble_save_update_array ( &$doa ) {
		$save_update_array = array();
		foreach ( $doa as $field => $control ) {
			if ( ! $control->is_multivalue() ) {
				$update_clause = $control->create_update_clause();
				if ( '' < $update_clause ) {
					$save_update_array[] = $update_clause;
				}
			}
		}	
		return ( $save_update_array );
	}

	abstract protected function db_search ( $meta_query_array );
	abstract protected function db_save ( $meta_query_array );
	abstract protected function db_update ( $meta_query_array );
	abstract protected function db_delete_by_id ( $id );
	
}


