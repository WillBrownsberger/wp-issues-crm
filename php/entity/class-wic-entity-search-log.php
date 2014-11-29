<?php
/*
*
*	wic-entity-search-log.php
*
*
*/

class WIC_Entity_Search_Log extends WIC_Entity_Parent {

	protected function set_entity_parms( $args ) { // 
		// accepts args to comply with abstract function definition, but as a parent does not process them -- no instance
		$this->entity = 'search_log';
	} 

	public function id_search( $id ) {
		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $this->entity );
		$wic_query->list_by_id ( '(' . $id['id_requested'] . ')' );
		$class_name = 'WIC_Entity_' . $wic_query->result[0]->entity; // entity_requested
		$action_requested = 'redo_search_from_query'; 
		$args = unserialize( $wic_query->result[0]->serialized_search_array ); // not an id, but will pass through
			// normally args is array with keys 'id' and 'instance', but no error since passing to a function expecting
		${ 'wic_entity_'. $wic_query->result[0]->entity } = new $class_name ( $action_requested, $args ) ;		
	}
	
	public static function serialized_search_array_formatter ( $serialized ) {
		
		$search_array = unserialize ( $serialized );
		$search_phrase = '';

		if ( count ( $search_array ) > 0 ) { 
			foreach ( $search_array as $search_clause ) {
	
				$value =  $search_clause['value']; // default
				$show_item = true; 
				
				// look up categories if any for post_category			
				if ( 'post_category' == $search_clause['key'] ) { 
					if ( 0 < count ( $value ) ) {
						$value_string = '';
						foreach ( $value as $key => $selected ) {
							$value_string .= ( $value_string > '' ) ? ', ': '';
							$value_string .= get_the_category_by_ID ( $key );				
						}
						$value = $value_string;
					} else {
						$value = '';
						$show_item = false;				
					}
				} elseif ( is_array( $value ) ) {
					$value = implode ( ',', $value );		
				} else {
					$class_name = 'WIC_Entity_' . $search_clause['table'];
					$method_name = 'get_' . $search_clause['key'] . '_label'; 
					if ( method_exists ( $class_name, $method_name ) ) {
						$value = $class_name::$method_name ( $value );
					}
				}
				
				if ( $show_item )	{			
					$search_phrase .= $search_clause['table'] . ': ' . 
						$search_clause['key'] . ' ' . 
						$search_clause['compare'] . ' ' . 
						esc_html( $value ) . '. <br />';
				}		
			}
		}
		return ( $search_phrase );	
	}	
	
  	public static function time_formatter( $value ) {
		$date_part = substr ( $value, 0, 10 );
		$time_part = substr ( $value, 11, 10 ); 		
		// return ( $date_part . '<br/>' . $time_part ); 
		return ( $value );
	} 

	public static function download_time_formatter ( $value ) {
		return ( self::time_formatter ( $value ) );	
	}

	public static function user_id_formatter ( $user_id ) {

		$display_name = '';		
		if ( isset ( $user_id ) ) { 
			if ( $user_id > 0 ) {
				$user =  get_users( array( 'fields' => array( 'display_name' ), 'include' => array ( $user_id ) ) );
				$display_name = esc_html ( $user[0]->display_name ); // best to generate an error here if this is not set on non-zero user_id
			}
		}
		return ( $display_name );
	}

}