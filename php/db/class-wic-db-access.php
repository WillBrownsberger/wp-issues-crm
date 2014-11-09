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

	public function __construct ( $entity ) { 
		$this->entity = $entity;
	}		

	/*
	*	publicly callable methods that will return results -- 
	*		these methods do not alter the object array as it exists in the object calling them
	*
	*/



	public function search ( $dao ) { // receives $data_object_array
		$this->sanitize_values( $dao );
		$meta_query_array = $this->assemble_meta_query_array ( $dao );  
		$this->db_search( $meta_query_array, false );
	}

	public function update ( $dao) {
		$this->sanitize_values( $dao );
		$this->dup_check ( $dao );
		$this->validate_values ( $dao );
		$this->required_check ( $dao );
		if ( false === $this->outcome ) {
			return;					
		} else {
			$this->db_update ( $dao );		
		}
	}

	public function save ( $dao) {
		$this->sanitize_values( $dao );
		$this->dup_check ( $dao );
		$this->validate_values ( $dao );
		$this->required_check ( $dao );
		if ( false === $this->outcome ) {
			return;					
		} else {
			$this->db_save ( $dao );		
		}
	}
	/*
	* protected helper functions, take $dao by reference
	*  and may alter the working copy of the $dao within this object
	*
	*/

	protected function sanitize_values( &$dao ) {
		foreach ( $dao as $field => $control ) {
			$control->sanitize();
		}
	}

	protected function dup_check ( &$dao ) {
		$dup_check_array = array();
		foreach ( $dao as $field_slug => $control ) {
			if	( $control->dup_check() ) {
				$dup_check_array[$field_slug] = $control;
			}	
		}	
		if ( count ($dup_check_array ) > 0 ) {
			$meta_query_array = $this->assemble_meta_query_array ( $dup_check_array );
			$this->db_search ( $meta_query_array, true );
			if ( $this->found_count > 1 || ( ( 1 == $this->found_count ) && ( $this->result[0]->ID != $dao[ID]->get_value() ) ) ) {
				$this->outcome = false;
				$dup_check_string = WIC_DB_Dictionary::get_dup_check_string ( $this->entity );
				$this->explanation .= sprintf ( __( 'Other records found with same combination of %s' , 'wp-issues-crm' ), $dup_check_string );		
			}
		}		 
	}

	protected function validate_values( &$dao ) {
		$validation_errors = '';		
		foreach ( $dao as $field => $control ) {
			$validation_errors .= $control->validate();
		}
		if ( $validation_errors > '' ) {
				$this->outcome = false;		
				$this->explanation .= $validation_errors;
		}
	}

	protected function required_check () { 
		$required_errors = '';
		$there_is_a_required_group = false;
		$a_required_group_member_is_present = false;		
		foreach ( $dao as $field_slug => $control ) {
			$required_errors .= $control->required_check();	
			if ( $control->is_group_required ) {
				$there_is_a_required_group = true;			
				$a_required_group_member_is_present = $control->is_present ? true : $a_required_group_member_is_present ;
			}
		}
		if ( $there_is_a_required_group && ! $a_required_group_member_is_present ) {		
			$required_errors .= sprintf ( __( ' At least one among %s is required. ', 'wp-issues-crm' ), WIC_DB_Dictionary::get_group_required_string( $this->entity ) );
		}
		if ( $required_errors > '' ) {
			$this->outcome = false;
			$this->explanation .= $required_errors;		
		}
   }


	protected function assemble_meta_query_array ( &$dao ) {
		$meta_query_array = array (
			'where_array' => array(),
			'join_array'	=> array(),
		);

		foreach ( $dao as $field => $control ) {
			$query_clauses = $control->create_search_clauses();
			if ( is_array ( $query_clauses ) ) {
				$meta_query_array['where_array'][] = $query_clauses['where_clause'];
				$meta_query_array['join_array'][] = $query_clauses['join_clause'];
			}
		}	
		return $meta_query_array;
	}

	abstract protected function db_search ( $meta_query_array, $dup_check );
	abstract protected function db_save ( $adfasdfasdf );
	abstract protected function db_update ( $adfasdfasdf );
	
}


