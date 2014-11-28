<?php
/*
*
* class-wic-db-access.php
*		intended as wraparound for direct db access objects (implemented as extensions to this.) 
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
	public $search_id;  //

	public function __construct ( $entity ) { 
		$this->entity = $entity;
	}		

	/*
	*	publicly callable methods that will return results -- 
	*		these methods do not alter the object array as it exists in the object calling them
	*
	*/

	private function search_log ( $meta_query_array ) {
		$entity = $this->entity;
		if ( "constituent" == $entity || 'issue' == $entity ) {	

			global $wpdb;
			$user_id = get_current_user_id();

			$search = serialize( $meta_query_array );
			
			$sql = $wpdb->prepare(
				"
				INSERT INTO wp_wic_search_log
				( user_id, time, entity, serialized_search_array )
				VALUES ( $user_id, %s, %s, %s )
				", 
				array ( current_time( 'Y-m-d-H-i-s' ),  $entity, $search ) ); 
			
			$save_result = $wpdb->query( $sql );
			
			if ( 1 == $save_result ) {
				$this->search_id = $wpdb->insert_id;	
			} else {		
				die ( __( 'Unknown database error in query_log. WIC_DB_Access::search_log.' , 'wp-issues-crm' ) );
			}
		}
	}
	 
	public static function get_search_from_search_log ( $id ) {
		global $wpdb;
		$search_object = $wpdb->get_row ( "SELECT * from wp_wic_search_log where id = $id ");
		
		$return = array (
			'user_id' => $search_object->user_id,
			'entity' =>  $search_object->entity, 
			'meta_query_array' =>  unserialize ( $search_object->serialized_search_array )
		);

		return ( $return );		
	}


	public function search ( $meta_query_array, $search_parameters ) { // receives pre-assembled meta_query_array
		$this->search_log( $meta_query_array );
		$this->db_search( $meta_query_array, $search_parameters );
		return;
	}

	public function delete_by_id ( $id ) {
		$this->db_delete_by_id ( $id );	
	}

	// note that the assembly of the save update array occurs in this database access class because
	// updates are handled for particular entities (and this object serves a particular entity)
	// by contrast, the search array assembly is handled at the entity level because it needs to be able to report up to
	// a multivalue control and contribute to a join across multiple entities in addition the primary object entity  
	public function save_update ( &$doa ) { // 
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


	public function list_by_id ( $id_string ) {
		$this->db_list_by_id ( $id_string ); 
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

	abstract protected function db_search ( $meta_query_array, $search_parameters );
	abstract protected function db_save ( &$meta_query_array );
	abstract protected function db_update ( &$meta_query_array );
	abstract protected function db_delete_by_id ( $id );
	
}


