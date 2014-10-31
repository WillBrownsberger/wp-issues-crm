<?php
/*
*
* class-wic-table-query.php
*
* mimics WP-Query to support access to special purpose tables in a compatible way
*
*
* 
*/

class WIC_Table_Query {
	
	
	
	public $found_posts = 0;
	public $post_count = 0;
	public $posts = array();
	
	public function __construct( $args, $wic_post_type ) {


		
		
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