<?php
/*
* class-wic-form.php
*
*/
class WIC_DB_Dictionary {
	
	public static function get_form_fields ( $entity ) {
		// returns array of row objects -- note: this join limits the form system to fields assigned to groups
		global $wpdb;
		$table1 = $wpdb->prefix . 'wic_data_dictionary';
		$table2 = $wpdb->prefix . 'wic_form_field_groups';
		$fields = $wpdb->get_results( 
			$wpdb->prepare (
				"
				SELECT field_slug, field_type 
				FROM $table1 t1 inner join $table2 t2 on t1.entity_slug = t2.entity_slug and t1.group_slug = t2.group_slug
				WHERE t1.entity_slug = %s
				"				
				, array ( $entity )
				)
			, OBJECT );

		return ( $fields );
	}


	public static function get_field_rules ( $entity, $field_slug ) {
		// 
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$field_rules = $wpdb->get_row( 
			$wpdb->prepare (
				"
				SELECT * 
				FROM $table
				WHERE entity_slug = %s and field_slug = %s
				"				
				, array ( $entity, $field_slug )
				)
			, OBJECT );

		return ( $field_rules );
	}


	public static function get_form_field_groups ( $entity ) {
		// 
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
		// 
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$fields = $wpdb->get_col ( 
			$wpdb->prepare (
				"
				SELECT field_slug
				FROM $table 
				WHERE entity_slug = %s and group_slug = %s
				ORDER BY field_order
				"				
				, array ( $entity, $group )
				)
			);

		return ( $fields );
	}
	
	public static function get_field_suffix_elements ( $entity ) {
		global $wpdb;
		$table1 = $wpdb->prefix . 'wic_data_dictionary';
		$table2 = $wpdb->prefix . 'wic_form_field_groups';
		$elements = $wpdb->get_results( 
		$wpdb->prepare (
				"
				SELECT max( like_search_enabled ) as like_search_enabled,
					max( if ( required = 'group', 1, 0 ) ) as required_group , 
					max( if ( required = 'individual', 1, 0 ) ) as required_individual
				FROM $table1 t1 inner join $table2 t2 on t1.entity_slug = t2.entity_slug and t1.group_slug = t2.group_slug
				WHERE t1.entity_slug = %s 
				ORDER BY field_order
				"				
				, array ( $entity )
				)
			, OBJECT_K );
		return ( $elements );
	}

	public static function get_sort_order_for_entity ( $entity ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$sort_clause = $wpdb->get_results( 
			$wpdb->prepare (
					"
					SELECT group_concat( field_slug ORDER BY sort_clause_order ASC SEPARATOR ', ' ) AS sort_clause_string
					FROM $table 
					WHERE entity_slug = %s 
					"				
					, array ( $entity )
					)
				, OBJECT );
		return ( $sort_clause );
	}

	public static function get_list_fields_for_entity ( $entity ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$sort_clause = $wpdb->get_results( 
			$wpdb->prepare (
				"
				SELECT field_slug, field_type, field_label 
				FROM $table
				WHERE entity_slug = %s
				AND listing_order > 0 
				ORDER BY listing_order
				"				
				, array ( $entity )
				)
			, OBJECT );
		return ( $sort_clause );
	}

	public static function get_dup_check_string ( $entity ) {
	
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$dup_check_string = $wpdb->get_row( 
			$wpdb->prepare (
					"
					SELECT group_concat( field_label SEPARATOR ', ' ) AS dup_check_string
					FROM $table 
					WHERE entity_slug = %s and dedup = 1 
					"				
					, array ( $entity )
					)
				, OBJECT );
	
		return ( trim( $dup_check_string->dup_check_string, "," ) ); 

	}

	public static function get_group_required_string ( $entity ) {
	
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$group_required_string = $wpdb->get_row( 
			$wpdb->prepare (
					"
					SELECT group_concat( field_label SEPARATOR ', ' ) AS dup_check_string
					FROM $table 
					WHERE entity_slug = %s and required = 'group' 
					"				
					, array ( $entity )
					)
				, OBJECT );
	
		return ( trim( $group_required_string->group_required_string, "," ) ); 

	}




}