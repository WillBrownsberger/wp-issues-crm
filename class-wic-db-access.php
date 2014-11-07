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

class WIC_DB_Access_Factory {

	static private $entity_model_array = array (
		'constituent' => 'WIC_WIC_DB_Access',	
		'activity' => 'WIC_WIC_DB_Access',
		'issue' => 'WIC_WP_DB_Access',
	);

	public static function make_a_db_access_object ( $entity ) {
		$right_db_class = self::$entity_model_array[$entity];
		$new_db_access_object = new $right_db_class ( $entity );
		return ( $new_db_access_object );	
	}
	
	
}



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
		$this->entity_rules = WIC_Data_Dictionary::get_rules_for_entity( $entity );
	}		

	public function search ( $data_array) {
		$this->sanitize_values( $data_array );
		$meta_query_array = $this->assemble_meta_query_array ( $data_array );  
		$this->db_search( $meta_query_array );
	}

	public function id_search ( $id ) {
		$this->sanitize_values( $data_array );
		$result = $this->db_search( $data_array );
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
		foreach ( $data_array as $field => $value ) {
		   $sanitizor = $this->entity_rules[$field]->sanitize_call_back;
		   $sanitizor = $sanitizor > '' ? $sanitizor : 'wic_generic_sanitizor';
   		$class_name = 'WIC_' . initial_cap ( $this->entity_rules[$field]->field_type ) . '_Control';
			$value = $class_name::sanitize_value( $field, $value, $sanitizor );
		}
	}

	protected function assemble_meta_query_array ( $data_array ) {
		$meta_query_array = array (
			'where_array' => array(),
			'join_array'	=> array(),
		);

		foreach ( $data_array as $field => $value ) {
		   $like = $this->entity_rules[$field]->like_search_enabled;
		   $like = ( $this->get_strict_match_setting() ) ? false : $like;
   		$class_name = 'WIC_' . initial_cap ( $this->entity_rules[$field]->field_type ) . '_Control';
			$query_clauses = $class_name::create_search_clauses( $field, $value, $like );
			if ( is_array ( $query_clauses ) ) {
				$meta_query_array['where_array'][] = $query_clauses['where_clause'];
				$meta_query_array['join_array'][] = $query_clauses['join_clause'];
			}
		}	
		return $meta_query_array;
	}

	protected function validate_values() {
	
	
	}
	
	protected function get_strict_match_setting() {
		$strict_match = isset ( $_POST['strict_match'] )  ? true : false;
		return ( $strict_match );
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


class WIC_WIC_DB_Access Extends WIC_DB_Access {

	protected $entity_table_translation_array = array (
		'constituent' 	=> 'wic_constituents',	
		'activity'		=>	'wic_activities',	
	);

	protected function db_save ( $data_array ) {

	}

	protected function db_search( $meta_query_array ) {

		global $wpdb;

		$join = '';
		$where = '';
		$values = array();
		$table  = $wpdb->prefix . $this->entity_table_translation_array[$this->entity]; 
		$sort_clause_array = WIC_Data_Dictionary::get_sort_order_for_entity( $this->entity );
		$sort_clause = $sort_clause_array[0]->sort_clause_string;
		
		foreach ( $meta_query_array['where_array'] as $where_item ) {
			$field_name		= $where_item['key'];
			$compare 		= $where_item['compare'];
			$where 			.= " AND $field_name $compare %s ";
			$values[] 		= ( '=' == $where_item['compare'] ) ? $where_item['value'] : $wpdb->esc_like ( $where_item['value'] ) . '%' ;
		}

		/* deal with joins! */		
		
		$sql = $wpdb->prepare( "
					SELECT 	* 
					FROM 		$table
					$join
					WHERE 1=1 $where
					ORDER BY $sort_clause ASC
					LIMIT 0, 100
					",
				$values );	
		
		$this->sql = $sql; 
		$this->result = $wpdb->get_results ( $sql );	
		$this->outcome = count( $this->result );
		$this->explanation = ''; 

		// wpdb get_results does not return errors for searches, so assume zero return is just a none found condition (not an error)
		// codex.wordpress.org/Class_Reference/wpdb#SELECT_Generic_Results
	}	

}

class WIC_WP_DB_Access Extends WIC_DB_Access {

	protected function db_save(  $data_array ) {
		
	}
	
	protected function db_search( $data_array ) {

	}
	
}


/*










		/* defaults if not supplied 
		$wic_post_type = 'constituent';
		$posts_per_page = 100;		
		
		extract ( $args, EXTR_OVERWRITE );
		
		global $wic_base_definitions;
		$this->wic_metakey = $wic_base_definitions->wic_metakey;
		
		global ${ 'wic_' . $wic_post_type . '_definitions' };
		
		global $wpdb;

		$table = $wpdb->prefix . $wic_base_definitions->wic_post_types[$wic_post_type]['dedicated_table'];		
		$orderby		= ${ 'wic_' . $wic_post_type . '_definitions' }->wic_post_type_sort_order['orderby'];
		$order		= ${ 'wic_' . $wic_post_type . '_definitions' }->wic_post_type_sort_order['order'];
		
		$orderby = ( 'title' == $orderby ) ? 'post_title' : $orderby; 
		
		if ( ! isset ( $args ['p'] ) ) {	
		
			$where = $this->parse_meta_args( $args );		
			$where_clause = $where['where_clause_with_placeholders'];

			$sql = $wpdb->prepare( "
					SELECT 	* 
					FROM 		$table
					WHERE 1=1 $where_clause
					ORDER BY $orderby $order
					LIMIT 0, $posts_per_page
					",
				$where['where_values'] );

		} else {
			$id = $args['p'];
			$sql = $wpdb->prepare ( "
					SELECT 	*
					FROM		$table
					WHERE		ID = %d
					",
				$id ); 	
		}
		echo $sql;
		$this->posts = $wpdb->get_results( $sql, OBJECT );		
		$this->found_posts = $wpdb->num_rows;

	}

	private function parse_meta_args ( $args ) {
	
		global $wpdb;
		global $wic_base_definitions;
		
		$where = array (
			'where_clause_with_placeholders' => '',
			'where_values' => array(),		
		);	
		
		foreach ( $args['meta_query'] as $where_item ) { // is not set on a direct search, but is set on a blank search, so needn't be tested for isset 
			if ( is_array ( $where_item ) ) { 
			
				$field_name = str_replace( $this->wic_metakey, '', $where_item['key'] ); // cleaning out key from generic metaquery passed from databased_utilities
				$compare = $where_item['compare']; 

				if  ( 'phones' == $field_name || 'emails' == $field_name ) {
					$where['where_clause_with_placeholders'] .= " AND ( 1=0 ";
					for ( $i = 0; $i < 5; $i++ ) { 
						switch ( $field_name ) {					
							case 'emails':
								$where['where_clause_with_placeholders'] .= " OR email_address_$i $compare %s ";
								$where['where_values'][] = ( '=' == $where_item['compare'] ) ? $where_item['value'] : $wpdb->esc_like ( $where_item['value'] ) . '%' ;
								break;
							case 'phones':
								$where['where_clause_with_placeholders'] .= " OR phone_$i $compare %s ";
								$where['where_values'][] = ( '=' == $where_item['compare'] ) ? $where_item['value'] : $wpdb->esc_like ( $where_item['value'] ) . '%' ;
								break;					
						}
					}
					$where['where_clause_with_placeholders'] .= ') ';
				} else {					
					$where['where_clause_with_placeholders'] .= " AND $field_name $compare %s ";
					$where['where_values'][] = ( '=' == $where_item['compare'] ) ? $where_item['value'] : $wpdb->esc_like ( $where_item['value'] ) . '%' ;
				}	
			}
		}

		$content_search =  ( isset( $args['s'] ) ) ? '%' . $args['s'] . '%' : '';
		if ( '' != $content_search && '%%' != $content_search ) {
			$where['where_clause_with_placeholders']  .= " AND post_content LIKE %s"; 
			$where['where_values'][] = $content_search; 
		}
	
		return ( $where );	
	} 
}*/