<?php
/*
*
* class-wic-db-access-wic.php
*		intended as wraparound for wpdb to access wic tables
*
*/

class WIC_DB_Access_WIC Extends WIC_DB_Access {

	protected $entity_table_translation_array = array (
		'constituent' 	=> 'wic_constituents',	
		'activity'		=>	'wic_activities',	
	);

	protected function db_save ( $save_update_array ) {
		global $wpdb;
		$table  = $wpdb->prefix . $this->entity_table_translation_array[$this->entity]; 
		
		$set = $this->parse_save_update_array( $save_update_array );
  		$set_clause_with_placeholders = $set['set_clause_with_placeholders'];
		$sql = $wpdb->prepare( "
				INSERT INTO $table 	
				$set_clause_with_placeholders
				",
			$set['set_value_array'] );
		$save_result = $wpdb->query( $sql );
				
		if ( 1 == $save_result ) {
			$this->outcome = true;		
			$this->insert_id = $wpdb->insert_id;	
		} else {		
			$this->outcome = false;
			$this->explanation = __( 'Unknown database error. Update may not have been successful' );
		}
		$this->sql = $sql;
		return;
	}
	
	protected function db_update ( $save_update_array ) {
		
		global $wpdb;
		$table  = $wpdb->prefix . $this->entity_table_translation_array[$this->entity]; 
		
		$set = $this->parse_save_update_array( $save_update_array );
  		$set_clause_with_placeholders = $set['set_clause_with_placeholders'];
		$sql = $wpdb->prepare( "
				UPDATE $table
				$set_clause_with_placeholders
				WHERE ID = %s
				",
			$set['set_value_array'] );
		$update_result = $wpdb->query( $sql );
		$this->outcome = ( 1 == $update_result );
		$this->explanation = ( $this->outcome ) ? '' : __( 'Unknown database error. Update may not have been successful' );
		$this->sql = $sql;
		return;
	}

	protected function db_search( $meta_query_array, $dup_check ) {

		global $wpdb;

		$join = '';
		$where = '';
		$values = array();
		$table  = $wpdb->prefix . $this->entity_table_translation_array[$this->entity]; 
		$sort_clause_array = WIC_DB_Dictionary::get_sort_order_for_entity( $this->entity );
		$sort_clause = $sort_clause_array[0]->sort_clause_string;
		
		foreach ( $meta_query_array['where_array'] as $where_item ) {
			$field_name		= $where_item['key'];
			$compare 		= $dup_check ? '=' : $where_item['compare'];
			$where 			.= " AND $field_name $compare %s ";
			$values[] 		= ( '=' == $where_item['compare']  || $dup_check ) ? $where_item['value'] : $wpdb->esc_like ( $where_item['value'] ) . '%' ;
		}

		/* deal with joins! */		
		
		$sql = $wpdb->prepare( "
					SELECT 	* 
					FROM 		$table
					$join
					WHERE 1=1 $where
					ORDER BY $sort_clause ASC
					LIMIT 0, 10
					",
				$values );	
		
		$this->sql = $sql; 
		$this->result = $wpdb->get_results ( $sql );	
		$this->outcome = true;  // wpdb get_results does not return errors for searches, so assume zero return is just a none found condition (not an error)
										// codex.wordpress.org/Class_Reference/wpdb#SELECT_Generic_Results 
		$this->found_count = count( $this->result ); 
		$this->explanation = ''; 


	}	

	protected function parse_save_update_array( $save_update_array ) {

		$set_clause_with_placeholders = 'SET last_updated_time = now()';
		$set_array = array();
		$current_user = wp_get_current_user();
		
		
		foreach ( $save_update_array['set_array'] as $save_update_clause ) {
			if ( $save_update_clause['key'] != 'ID' ) {
				$set_clause_with_placeholders .= ', ' . $save_update_clause['key'] . ' = %s'; 		
				$set_value_array[] = $save_update_clause['value'];
			} else { 
				$entity_id = $save_update_clause['value'];
			}
		}

		$set_clause_with_placeholders .= ', last_updated_by = %d ';
		$set_value_array[]  = $current_user->ID;		

		if ( $entity_id > '' ) {
			$set_value_array[] = $entity_id; // tag entity ID on to end of array (will not be present in save cases, since is a readonly field)
		}	
	
		return ( array (
			'set_clause_with_placeholders' => $set_clause_with_placeholders,
			'set_value_array'	=> $set_value_array,
				)		
			);
	}

}

