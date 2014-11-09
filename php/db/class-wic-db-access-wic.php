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

	protected function db_save ( $data_array ) {

	}
protected function db_update ( $data_array ) {

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

}

