<?php
/*
*
* class-db-access.php
*		intended as wraparound for wpdb 
*
* supports multiple formats for data access to be further implemented in subclasses
*		WIC_Dedicated_Table_Access
*		WIC_WP_Post_Acccess
*
* 
*/

interface WIC_DB_Template {

	public function save ( $entity, $data_array );
	public function update ( $entity, $data_array );
	public function search ( $entity, $data_array );
	public function id_search ( $entity, $data_array );
	
	/* all functions should return as follows:
		$outcome  save/update/search # records found or acted on  or false if error
		$explanation  reason for outcome 
		$entity_object_array -- as saved, update or found( possibly multiple )
	*/

}

abstract class WIC_DB_Access impements WIC_DB_Template {

	protected $entity_rules
		
	protected function __construct {
		$this->entity_rules = $this->get_rules_for_entity();
	}		

	public function save ( $entity, $data_array) 
		$this->sanitize_values( $data_array );
		$errors = $this->validate_values( $data_array )
		$errors .= $this->do_required_checks( $data_array );
		if ( '' == $errors ) {
			$result = $this->db_save($data_array);
		}
		return $result
	}

	public function search ( $entity, $data_array) 
		$this->sanitize_values( $data_array );
		$result = $this->db_search( $data_array );
	}




	protected function sanitize_values {
		foreach ( $data_array as $field => $value ) {
			if ( is_array ( $value ) ) {
				foreach ( $value ) {
					$value = $this->$this->entity_rules->field_slug->sanitize_callback() ( $value ) )
				}
			} else {
				$value = $this->$this->entity_rules->field_slug->sanitize_callback() ( $value );
			}
		}
	}

	protected function validate_values{
	
	
	}
	
	protected function check required values () { // REWRITE!!!
		/* for each defined field, instantiate a field object (sanitize and validate post input) */		
		$group_required_test = '';
		$group_required_label = '';		
		foreach ( $this->field_definitions as $args ) {
			$class_name = 'WIC_' .  $args['type'] . '_Field';
			${$args['name']} = new $class_name ( $args );
			$this->fields[] = ${$args['name']};  
			$this->error_messages .= ${$args['name']}->validation_errors;	
			if ( '' = ${$args['name']}->present && "individual" == ${$args['name']}->required )
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

	abstract function db_save {
	
	}
	
	abstract function db_search
	
}


class WIC_WIC_DB_Access Extends WIC_DB_Access {
	private function db_save() {
	}
	private function db_search() {
	}

}

$wic_wic_db_access = new WIC_WIC_DB_Access;

class WIC_WP_DB_Access Extends WIC_DB_Access {
	private function db_save() {
	}
	private function db_search() {
	}

}

$wic_wp_db_access = new WIC_WIC_DB_Access;














		/* defaults if not supplied */
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
}