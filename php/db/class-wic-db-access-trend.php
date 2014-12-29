<?php
/*
*
* class-wic-db-access-wic.php
*		intended as wraparound for wpdb to access wic tables
*
*/

class WIC_DB_Access_Trend Extends WIC_DB_Access {
	
	// implements trend search variables as activity search variables and then aggregates

	protected function db_search( $meta_query_array, $search_parameters ) { 

		// search parameters ignored

		$top_entity = 'activity'; // trend passes through variables to an activity query
		$deleted_clause = 'AND c.mark_deleted != \'deleted\'';
				 
		// set global access object 
		global $wpdb;

		// prepare $where clause
		$where = '';
		$values = array();
		// explode the meta_query_array into where string and array ready for wpdb->prepare
		foreach ( $meta_query_array as $where_item ) {

			$field_name		= $where_item['key'];
			$table 			= 'activity';
			$compare 		= $where_item['compare'];
			
			// set up $where clause with placeholders and array to fill them
			if ( '=' == $compare || '>' == $compare || '<' == $compare || '!=' == $compare ) {  // straight strict match			
				$where .= " AND $table.$field_name $compare %s ";
				$values[] = $where_item['value'];
			} elseif ( 'BETWEEN' == $compare ) { // date range
				$where .= " AND $table.$field_name >= %s AND $table.$field_name <= %s" ;  			
				$values[] = $where_item['value'][0];
				$values[] = $where_item['value'][1];
			} else {
				 die ( sprintf( __( 'Incorrect compare settings for field %1$s reported by WIC_DB_Access_Trend::db_search.', 'WP_Issues_CRM' ),
					 $this->field->field_slug ) ); 
			}
		}

		// straight activity query to start
		$join = $wpdb->prefix . 'wic_activity activity inner join ' . $wpdb->prefix . 'wic_constituent c on c.id = activity.constituent_id';

		// prepare SQL
		$activity_sql = $wpdb->prepare( "
					SELECT constituent_id, issue, max(pro_con) as pro_con
					FROM 	$join
					WHERE 1=1 $deleted_clause $where 
					GROUP BY $top_entity.constituent_ID, $top_entity.issue
					LIMIT 0, 9999999
					",
				$values );	
		// $sql group by always returns single row, even if multivalues for some records 
		$sql =  	"
					SELECT p.id, count(constituent_id) as total, sum( if (pro_con = 0, 1, 0) ) as pro,  sum( if (pro_con = 1, 1, 0) ) as con  
					FROM ( $activity_sql ) as a 
					INNER JOIN $wpdb->posts p on a.issue = p.ID
					ORDER BY count(constituent_id) DESC
					";
		$sql_found = "SELECT FOUND_ROWS() as found_count";
		$this->sql = $sql; 
		// do search
		$this->result = $wpdb->get_results ( $sql );
	 	$this->showing_count = count ( $this->result );
	 	$this->found_count = count ( $this->result );
		// set value to say whether found_count is known
		$this->outcome = true;  // wpdb get_results does not return errors for searches, so assume zero return is just a none found condition (not an error)
										// codex.wordpress.org/Class_Reference/wpdb#SELECT_Generic_Results 
		$this->explanation = ''; 
		$this->sql = $activity_sql;
	}	

	/* required functions not implemented */
	protected function db_save ( &$meta_query_array ) {}
	protected function db_update( &$meta_query_array ) {  }
	protected function db_delete_by_id ( $args ){}

}

