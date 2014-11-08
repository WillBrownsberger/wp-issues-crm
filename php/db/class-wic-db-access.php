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
	public $entity;		// top level entity searched for ( e.g., constituents or issues )
	public $sql; 			// for search, the query executed;
	public $result; 		// entity_object_array -- as saved, update or found( possibly multiple ) (each row as object with field_names properties)
	public $outcome; 		// integer save/update/search # records found or acted on  or false if error
	public $explanation; // reason for outcome

	protected $entity_rules;
		
	public function __construct ( $entity ) { 
		$this->entity = $entity;
	}		

	public function search ( $data_array) {
		$this->sanitize_values( $data_array );
		$meta_query_array = $this->assemble_meta_query_array ( $data_array );  
		$this->db_search( $meta_query_array );
	}

	public function update ( $data_array) {
		$this->sanitize_values( $data_array );
		$result = $this->db_search( $data_array );
	}

	public function save ( $data_array) {
		$this->sanitize_values( $data_array );
		$errors = $this->validate_values( $data_array );
		$errors .= $this->do_required_checks( $data_array );
		if ( '' == $errors ) {
			$result = $this->db_save($data_array);
		}
		return $result;
	}

	protected function sanitize_values( $data_array ) {
		foreach ( $data_array as $field => $control ) {
			$control->sanitize();
		}
	}

	protected function assemble_meta_query_array ( $data_array ) {
		$meta_query_array = array (
			'where_array' => array(),
			'join_array'	=> array(),
		);

		foreach ( $data_array as $field => $control ) {
			$query_clauses = $control->create_search_clauses();
			if ( is_array ( $query_clauses ) ) {
				$meta_query_array['where_array'][] = $query_clauses['where_clause'];
				$meta_query_array['join_array'][] = $query_clauses['join_clause'];
			}
		}	
		return $meta_query_array;
	}

	protected function validate_values() {
	
	
	}

	protected function check_required_values () { // REWRITE!!!
		/* for each defined field, instantiate a field object (sanitize and validate post input) */		
		$group_required_test = '';
		$group_required_label = '';		
		foreach ( $this->field_definitions as $args ) {
			$class_name = 'WIC_' .  $args['type'] . '_Field';
			${$args['name']} = new $class_name ( $args );
			$this->fields[] = ${$args['name']};  
			$this->error_messages .= ${$args['name']}->validation_errors;	
			if ( '' == ${$args['name']}->present && "individual" == ${$args['name']}->required )
				$this->missing_fields .= ' ' . sprintf( __( ' %s is a required field. ' , 'wp-issues-crm' ), ${$args['name']}->label );
			}
			if  ( "group" == ${$args['name']}->required ) {
 				$group_required .= ${$args['name']}->present;
 				$group_required_label .= ( '' == $group_required_label ) ? '' : ', ';	
 				$group_required_label .= ${$args['name']}->label;
			}
		if ( '' == $group_required_test && $group_required_label > '' ) {
			$this->missing_fields .= sprintf ( __( ' At least one among %s is required. ', 'wp-issues-crm' ), $group_required_label );
   	}
	}

	abstract protected function db_save ( $data_array );
	
	abstract protected function db_search ( $data_array );
	
}


