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
	public $sanitized_array; // copy of $doa after sanitization (will include id after save)

	public function __construct ( $entity ) { 
		$this->entity = $entity;
	}		

	/*
	*	publicly callable methods that will return results -- 
	*		these methods do not alter the object array as it exists in the object calling them
	*
	*/

	public function search ( $doa ) { // receives $data_object_array
		$this->sanitize_values( $doa );
		$meta_query_array = $this->assemble_meta_query_array ( $doa );  
		$this->db_search( $meta_query_array, false );
	}

	public function update ( $doa ) {
		$this->sanitize_values( $doa );
		$this->dup_check ( $doa, false ); // not doing a save (different fail threshold)
		$this->validate_values ( $doa );
		$this->required_check ( $doa );
		if ( false === $this->outcome ) {
			return;					
		} else {
			$save_update_array = $this->assemble_save_update_array( $doa );
			$this->db_update ( $save_update_array );
			return;	
		}
	}

	public function save ( $doa ) {
		$this->sanitize_values( $doa );
		$this->dup_check ( $doa, true ); // doing a save -- lower fail threshold
		$this->validate_values ( $doa ); 
		$this->required_check ( $doa );
		if ( false === $this->outcome ) {
			return;					
		} else {
			$save_update_array = $this->assemble_save_update_array( $doa );
			$this->db_save ( $save_update_array );	
			$this->sanitized_array['ID']->set_value( $this->insert_id );
			return;	
		}
	}
	/*
	* protected helper functions, take $doa by reference
	*  and may alter the working copy of the $doa within this object
	*
	*/

	protected function sanitize_values( &$doa ) {
		foreach ( $doa as $field => $control ) {
			$control->sanitize();
		}
		$this->sanitized_array = $doa;
	}

	protected function dup_check ( &$doa, $save ) {
		$dup_check_array = array();
		foreach ( $doa as $field_slug => $control ) {
			if	( $control->dup_check() ) {
				$dup_check_array[$field_slug] = $control;
			}	
		}	
		if ( count ($dup_check_array ) > 0 ) {
			$meta_query_array = $this->assemble_meta_query_array ( $dup_check_array );
			$this->db_search ( $meta_query_array, true );
			if ( $this->found_count > 1 || ( ( 1 == $this->found_count ) && ( $this->result[0]->ID != $doa['ID']->get_value() ) )
					|| ( $save && $this->found_count > 0 ) ) {
				$this->outcome = false;
				$dup_check_string = WIC_DB_Dictionary::get_dup_check_string ( $this->entity );
				$this->explanation .= sprintf ( __( 'Other records found with same combination of %s' , 'wp-issues-crm' ), $dup_check_string );		
			}
		}		 
	}

	protected function validate_values( &$doa ) {
		$validation_errors = '';		
		foreach ( $doa as $field => $control ) {
			$validation_errors .= $control->validate();
		}
		if ( $validation_errors > '' ) {
				$this->outcome = false;		
				$this->explanation .= $validation_errors;
		}
	}

	protected function required_check ( &$doa ) { 
		$required_errors = '';
		$there_is_a_required_group = false;
		$a_required_group_member_is_present = false;		
		foreach ( $doa as $field_slug => $control ) {
			$required_errors .= $control->required_check();	
			if ( $control->is_group_required() ) {
				$there_is_a_required_group = true;			
				$a_required_group_member_is_present = $control->is_present() ? true : $a_required_group_member_is_present ;
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
	
	/*
	*
	* Assemble query strings from controls ( use WP format, even for direct table updates )
	*
	*/

	protected function assemble_meta_query_array ( &$doa ) {
		$meta_query_array = array (
			'where_array' => array(),
			'join_array'	=> array(),
		);
		foreach ( $doa as $field => $control ) {
			$query_clauses = $control->create_search_clauses();
			if ( is_array ( $query_clauses ) ) { // skipping empty fields
				$meta_query_array['where_array'][] = $query_clauses['where_clause'];
				$meta_query_array['join_array'][] = $query_clauses['join_clause'];
			}
		}	
		return $meta_query_array;
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

	abstract protected function db_search ( $meta_query_array, $dup_check );
	abstract protected function db_save ( $meta_query_array );
	abstract protected function db_update ( $meta_query_array );
	
}


