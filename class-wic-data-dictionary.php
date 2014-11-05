<?php
/*
* class-wic-form.php
*
*/
class WIC_Data_Dictionary {
	
	public static function get_fields ( $entity ) {
		// returns array of the form key=>value where key is field_slug and value is type
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$fields = $wpdb->get_results( 
			$wpdb->prepare (
				"
				SELECT field_slug, field_type 
				FROM $table
				WHERE entity_slug = %s
				"				
				, array ( $entity )
				)
			, OBJECT_K );
			
		return ( $fields );
	}

	public static function get_rules_for_entity ( $entity ) {
		// returns array of the form key=>value where key is field_slug and value is type
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$fields = $wpdb->get_results( 
			$wpdb->prepare (
				"
				SELECT field_slug, field_type, required, dedup, field_default, like_search_enabled, sanitize_call_back, validation_call_back, enum_values 
				FROM $table
				WHERE entity_slug = %s
				"				
				, array ( $entity )
				)
			, OBJECT_K );
			
		return ( $fields );
	}


	public static function get_form_field_groups ( $entity ) {
		// returns array of the form key=>value where key is field_slug and value is type
		global $wpdb;
		$table = $wpdb->prefix . 'wic_form_field_groups';
		$groups = $wpdb->get_results( 
			$wpdb->prepare (
				"
				SELECT group_slug, group_label, group_legend, initial_open
				FROM $table
				WHERE entity_slug = %s
				ORDER BY group_order
				"				
				, array ( $entity )
				)
			, OBJECT_K );
			
		return ( $groups );
	}

	public static function get_fields_for_group ( $entity, $group ) {
		// returns array of the form key=>value where key is field_slug and value is type
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$groups = $wpdb->get_results( 
			$wpdb->prepare (
				"
				SELECT field_slug, field_type, field_label, like_search_enabled, required
				FROM $table 
				WHERE entity_slug = %s and group_slug = %s
				ORDER BY field_order
				"				
				, array ( $entity, $group )
				)
			, OBJECT_K );
			
		return ( $groups );
	}
	
	public static function get_field_suffix_elements ( $entity ) {
		global $wpdb;
		$table1 = $wpdb->prefix . 'wic_data_dictionary';
		$table2 = $wpdb->prefix . 'wic_form_field_groups';
		$elements = $wpdb->get_results( 
			$wpdb->prepare (
				"
				SELECT max ( like_search_enabled ) as like, 
					max ( if ( required = 'group', 1, 0 ) as required_group , 
					max( if ( required = 'individual', 1, 0 ) as required_individual
				FROM $table1 t1 inner join $table2 t2 on t1.entity_slug = t2.entity_slug and t1.group_slug = t2.group_slug
				WHERE entity_slug = %s and field_group = %s
				ORDER BY field_order
				"				
				, array ( $entity )
				)
			, 'OBJECT_A' );
			
		return ( $elements );
	}



}