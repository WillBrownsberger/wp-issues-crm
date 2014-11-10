<?php
/*
*
* class-wic-update.php
*
* replaces logic to update meta fields when using special purpose tables
* called by wp-issues-crm-database-utilities->save_update_wic_post and produces outcome in same format as that
*
* 
*/

class WIC_Update {
	
	public $found_posts = 0;
	public $post_count = 0;
	public $posts = array();
	public $outcome = array (
			'post_id'	=> 0,
		   'notices'	=> '', 
	);			
	
	public function __construct( &$next_form_output, &$fields_array, $wic_post_type ) {

		global $wpdb;
		global $wic_base_definitions;

		$table = $wpdb->prefix . $wic_base_definitions->wic_post_types[$wic_post_type]['dedicated_table'];		

		$set = $this->create_set_clause ( $next_form_output, $fields_array, $wic_post_type );
		$set_clause = $set['set_clause_with_placeholders'];
		
		if ( $next_form_output['wic_post_id'] > 0 ) {	
			$set['set_values'][] = $next_form_output['wic_post_id'];
			$sql = $wpdb->prepare( "
					UPDATE $table
					$set_clause
					WHERE ID = %d
					",
				$set['set_values'] );
			$success = $wpdb->query( $sql );
			$this->outcome['post_id'] = $next_form_output['wic_post_id'] > 0 ;
		} else {
			$sql = $wpdb->prepare ( "
					INSERT INTO $table 	
					$set_clause
					",
				$set['set_values'] ); 	
			$success = $wpdb->query( $sql );	
			$this->outcome['post_id'] = $wpdb->insert_id;		
		}

		$this->outcome['notices'] = $success ? '' : __( 'Unknown database error in save/update.', 'wp-issues-crm' );
	}

	private function create_set_clause ( &$next_form_output, &$fields_array, $wic_post_type ) {


		global ${ 'wic_' . $wic_post_type . '_definitions' };	
		global $wic_form_utilities;
		
		$set = array (
			'set_clause_with_placeholders' => '',
			'set_values' => array(),		
		);	
		

		$set['set_values'][] = (  $next_form_output['wic_post_content'] > '' ) ? $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] ) : '';   
		$set['set_clause_with_placeholders'] =  ( $next_form_output['wic_post_id'] > 0 ) ? // if have a post already concatenating content as running log 
				"SET post_content = concat( %s, post_content )" : " SET post_content = %s ";

		$title =  ${ 'wic_' . $wic_post_type . '_definitions' }->title_callback( $next_form_output );
		$set['set_clause_with_placeholders'] .= ", post_title = %s ";
		$set['set_values'][] = $title; 
		
		foreach ( $fields_array as $field ) {  
			if ( 'multivalue' == $field['type'] ) { 
				if  ( 'phones' == $field['slug'] || 'emails' == $field['slug'] ) {
					for ( $i = 0; $i < 5; $i++ ) { 
						switch ( $field['slug'] ) {					
							case 'emails':
								$set['set_clause_with_placeholders'] .= ", email_type_$i = %s ";
								$set['set_values'][] = isset ( $next_form_output['emails'][$i][0] ) ? $next_form_output['emails'][$i][0] : '';
								$set['set_clause_with_placeholders'] .= ", email_address_$i = %s ";
								$set['set_values'][] = isset ( $next_form_output['emails'][$i][1] ) ? $next_form_output['emails'][$i][1] : '';
								break;
							case 'phones':
								$set['set_clause_with_placeholders'] .= ", phone_type_$i = %s ";
								$set['set_values'][] = isset ( $next_form_output['phones'][$i][0] ) ? $next_form_output['phones'][$i][0] : '';
								$set['set_clause_with_placeholders'] .= ", phone_$i = %s ";
								$set['set_values'][] = isset ( $next_form_output['phones'][$i][1] ) ? $next_form_output['phones'][$i][1] : '';
								$set['set_clause_with_placeholders'] .= ", phone_ext_$i = %s ";
								$set['set_values'][] = isset ( $next_form_output['phones'][$i][2] ) ? $next_form_output['phones'][$i][2] : '';
								break;					
						}
					}
				} 
			} else {			
			   $field_name = $field['slug'];	
			   if ( 'audit' != $field['group'] ) {	
					$set['set_clause_with_placeholders'] .= " , $field_name =  %s ";
					$value = isset ( $next_form_output[$field['slug']] ) ? $next_form_output[$field['slug']] : '';
					$set['set_values'][] = $value;
					if ( $value > '' ) {
						array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form					
					}
				}
			}
		}
	
		$set['set_clause_with_placeholders'] .= ", last_updated_time = now(), last_updated_by = %d ";
		$current_user = wp_get_current_user();
		$set['set_values'][] = $current_user->ID; 
	
		return ( $set );	
	} 
}