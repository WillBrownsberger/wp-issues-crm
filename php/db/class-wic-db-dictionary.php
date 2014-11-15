<?php
/*
* class-wic-form.php
*
*
*
*  This script provides access to the WIC data dictionary.
* 
*  The first two methods are used in assembling the data_object_array for entities.  They load all field
*		rules into the controls in the array.
*
*	Outside those controls, no routines know individual field rules.  There are, however, select occasions where
*		it is convenient to query across all fields for certain properties.  These are limited to:
*			+ like_search_enabled, dup_check and required solely for the purpose of formatting of form legend creation or of error messages
*			+ sort_clause_order soley for the purpose of creating a sort string
*			+ sort_list_fields soley for the purpose supporting a shortened data object array for displaying a list
*			+ form fields only for the purposes of grouping
*
*		NB: all field properties are private to the control objects, but certain properties are disclosed in processing -- see wic-control classes. 
*
*/

class WIC_DB_Dictionary {
	
	/*************************************************************************
	*	
	* Basic methods supporting setup of data object array for entities
	*
	**************************************************************************/	
	
	// assemble fields for an entity -- n.b. limits the assembly to fields assigned to groups
	public static function get_form_fields ( $entity ) {
		// returns array of row objects, one for each field 
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

	// expose the rules for all fields for an entity -- only called in control initialization;
	// rules are passed to each control that is in the data object array directly -- no processing;
	// the set retrieved by this method is not limited to group members and might support a data dump function, 
	// 	but in the online system, only the fields selected by get_form_fields are actually set up as controls  
	public static function get_field_rules ( $entity, $field_slug ) {
		// this is only called in the control object -- only the control object knows field details
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

	/*************************************************************************
	*	
	* Methods supporting list display -- sort order and shortened field list 
	*
	**************************************************************************/	

	// return string of fields for inclusion in sort clause for lists
	public static function get_sort_order_for_entity ( $entity ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$sort_clause = $wpdb->get_results( 
			$wpdb->prepare (
					"
					SELECT group_concat( field_slug ORDER BY sort_clause_order ASC SEPARATOR ', ' ) AS sort_clause_string
					FROM $table 
					WHERE entity_slug = %s and sort_clause_order > 0
					"				
					, array ( $entity )
					)
				, OBJECT );
		return ( $sort_clause );
	}

	// return short list of fields for inclusion in display in lists (always include id) 
	// also used in assembly of shortened data object array for lists
	public static function get_list_fields_for_entity ( $entity ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$list_fields = $wpdb->get_results( 
			$wpdb->prepare (
				"
				SELECT field_slug, field_label, field_type
				FROM $table
				WHERE entity_slug = %s
				AND ( listing_order > 0 or field_slug = 'ID' )
				ORDER BY listing_order
				"				
				, array ( $entity )
				)
			, OBJECT );
		return ( $list_fields );
	}

	/*************************************************************************
	*	
	* Basic methods supporting forms  
	*
	**************************************************************************/	
	
	// retrieve the groups for a form with their properties	
	public static function get_form_field_groups ( $entity ) {
		// this lists the form groups
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

	// this just retrieves the list of fields in a form group 
	public static function get_fields_for_group ( $entity, $group ) {
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
	
	/*************************************************************************
	*	
	* Special methods for assembling generic message strings across groups
	*   	-- these functions play no role in validation or any processing, 
	*		-- they only format info
	*
	**************************************************************************/
		
	// report presence of fields requiring legend display 
	public static function get_field_suffix_elements ( $entity ) {
		// this tabulates required and like properties across fields to 
		//	support determination of whether to display legends
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

	// return string of dup check fields for inclusion in error message
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

	// return string of required fields for required error message
	public static function get_group_required_string ( $entity ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wic_data_dictionary';
		$group_required_string = $wpdb->get_row( 
			$wpdb->prepare (
					"
					SELECT group_concat( field_label SEPARATOR ', ' ) AS group_require_string
					FROM $table 
					WHERE entity_slug = %s and required = 'group' 
					"				
					, array ( $entity )
					)
				, OBJECT );
	
		return ( trim( $group_required_string->group_required_string, "," ) ); 
	}

}